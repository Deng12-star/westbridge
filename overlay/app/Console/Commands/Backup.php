<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Backs up the database (and optionally the uploaded photos) into
 * storage/app/backups, then deletes the oldest copies beyond --keep.
 *
 *   php artisan wb:backup                         database only
 *   php artisan wb:backup --with-uploads          database + public/uploads
 *   php artisan wb:backup --label=pre-deploy      run by scripts/deploy.sh
 *
 * Scheduled nightly (bootstrap/app.php). Copy storage/app/backups off the
 * server regularly - a backup on the same machine does not survive losing it.
 */
class Backup extends Command
{
    protected $signature = 'wb:backup {--with-uploads} {--keep=14} {--label=nightly}';

    protected $description = 'Back up the database (and optionally uploaded photos)';

    public function handle(): int
    {
        $label = preg_replace('/[^a-z0-9-]/', '', strtolower((string) $this->option('label'))) ?: 'manual';
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $stamp = now()->format('Ymd-His');

        try {
            $db = $this->backupDatabase($dir, "{$label}-{$stamp}");
            $this->info('Database: '.$db.' ('.$this->size($db).')');

            if ($this->option('with-uploads')) {
                $zip = $this->backupUploads($dir, "{$label}-{$stamp}-uploads.zip");
                $this->info('Photos:   '.($zip ? $zip.' ('.$this->size($zip).')' : 'nothing to back up'));
            }
        } catch (Throwable $e) {
            $this->error('Backup FAILED: '.$e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->prune($dir, $label, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    private function backupDatabase(string $dir, string $base): string
    {
        $connection = config('database.default');
        $cfg = config("database.connections.{$connection}");

        return match ($cfg['driver'] ?? null) {
            'sqlite' => $this->sqlite($cfg, $dir, $base),
            'mysql', 'mariadb' => $this->mysql($cfg, $dir, $base),
            default => throw new RuntimeException('Backups support SQLite and MySQL/MariaDB, not '.($cfg['driver'] ?? '?').'.'),
        };
    }

    /** @param array<string, mixed> $cfg */
    private function sqlite(array $cfg, string $dir, string $base): string
    {
        $source = (string) $cfg['database'];
        if (! is_file($source)) {
            throw new RuntimeException("SQLite file not found: {$source}");
        }

        $target = "{$dir}/{$base}.sqlite.gz";
        file_put_contents($target, gzencode((string) file_get_contents($source), 6));

        // Verify: the copy must decompress to a valid SQLite file.
        $head = substr((string) gzdecode((string) file_get_contents($target)), 0, 16);
        if ($head !== "SQLite format 3\0") {
            File::delete($target);
            throw new RuntimeException('The backup copy could not be verified.');
        }

        return $target;
    }

    /** @param array<string, mixed> $cfg */
    private function mysql(array $cfg, string $dir, string $base): string
    {
        $binary = (new ExecutableFinder)->find('mysqldump') ?? (new ExecutableFinder)->find('mariadb-dump');
        if (! $binary) {
            throw new RuntimeException('mysqldump is not installed on this server.');
        }

        $target = "{$dir}/{$base}.sql.gz";
        $out = gzopen($target, 'wb6');

        // The password goes in the environment, never on the command line
        // where other users of the server could read it.
        $process = new Process(
            [$binary, '--single-transaction', '--quick', '--routines', '--no-tablespaces',
                '--host='.$cfg['host'], '--port='.$cfg['port'], '--user='.$cfg['username'], $cfg['database']],
            null,
            ['MYSQL_PWD' => (string) $cfg['password']],
            null,
            900,
        );

        $tail = '';
        $process->run(function (string $type, string $buffer) use ($out, &$tail): void {
            if ($type === Process::OUT) {
                gzwrite($out, $buffer);
                $tail = substr($tail.$buffer, -200);
            }
        });
        gzclose($out);

        if (! $process->isSuccessful() || ! str_contains($tail, 'Dump completed')) {
            File::delete($target);
            throw new RuntimeException('mysqldump failed: '.trim($process->getErrorOutput()));
        }

        return $target;
    }

    private function backupUploads(string $dir, string $name): ?string
    {
        $source = public_path('uploads');
        if (! is_dir($source) || ! class_exists(ZipArchive::class)) {
            return null;
        }

        $target = "{$dir}/{$name}";
        $zip = new ZipArchive;
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the photo archive.');
        }

        foreach (File::allFiles($source) as $file) {
            $zip->addFile($file->getPathname(), 'uploads/'.str_replace('\\', '/', $file->getRelativePathname()));
        }
        $zip->close();

        return is_file($target) ? $target : null;
    }

    /** Keep the newest $keep backups of this label; delete the rest. */
    private function prune(string $dir, string $label, int $keep): void
    {
        $sets = collect(File::files($dir))
            ->map(fn ($f) => $f->getFilename())
            ->filter(fn ($n) => str_starts_with($n, $label.'-'))
            ->groupBy(fn ($n) => substr($n, 0, strlen($label) + 16)) // label-YYYYmmdd-HHMMSS
            ->sortKeysDesc();

        $sets->slice($keep)->flatten()->each(fn ($n) => File::delete("{$dir}/{$n}"));
    }

    private function size(string $path): string
    {
        $bytes = (int) @filesize($path);

        return $bytes > 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
