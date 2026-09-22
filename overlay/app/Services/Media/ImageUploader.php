<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores uploaded images under public/uploads/{folder}.
 *
 * Security rules:
 *  - The file type is read from the image's own bytes (getimagesize), never
 *    from the name the uploader gave it. Only JPG, PNG and WebP are accepted,
 *    and the saved file always gets the matching extension - so a file named
 *    "x.html" or "x.php" can never be stored under that name.
 *  - When GD is available the image is decoded and re-encoded, which drops
 *    anything hidden inside it (a "polyglot" file is only pixels afterwards).
 *  - The file name is random; nothing the uploader typed reaches the disk.
 *
 * Files go straight into public/ rather than storage/ so the site works on
 * Windows and shared hosting without a storage symlink.
 */
class ImageUploader
{
    private const TYPES = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    /** Refuse anything above ~40 megapixels: decoding it could exhaust memory. */
    private const MAX_PIXELS = 40_000_000;

    public function store(UploadedFile $file, string $folder, int $maxWidth = 1600, string $field = 'image'): string
    {
        $source = (string) $file->getRealPath();
        $info = @getimagesize($source);

        if ($info === false || ! isset(self::TYPES[$info[2]])) {
            throw ValidationException::withMessages([$field => 'That file is not a JPG, PNG or WebP image.']);
        }

        [$width, $height, $type] = $info;

        if ($width < 1 || $height < 1 || $width * $height > self::MAX_PIXELS) {
            throw ValidationException::withMessages([$field => 'That image is too large. Use one under 8000 pixels wide.']);
        }

        $ext = self::TYPES[$type];
        $folder = trim(preg_replace('/[^a-z0-9_-]/i', '', $folder) ?: 'misc', '/');
        $dir = public_path('uploads/'.$folder);
        File::ensureDirectoryExists($dir);

        $name = Str::lower(Str::random(24)).'.'.$ext;
        $target = $dir.DIRECTORY_SEPARATOR.$name;

        if (! $this->reencode($source, $target, $ext, $width, $maxWidth)) {
            // No GD (or it cannot read this format): keep the original bytes,
            // still under a safe name and extension.
            File::copy($source, $target);
        }

        return $folder.'/'.$name;
    }

    public function delete(?string $path): void
    {
        if (blank($path) || str_contains($path, '..') || ! preg_match('#^[a-z0-9_-]+/[a-z0-9]+\.(jpg|png|webp)$#', $path)) {
            return;
        }

        File::delete(public_path('uploads/'.$path));
    }

    private function reencode(string $source, string $target, string $ext, int $width, int $maxWidth): bool
    {
        if (! function_exists('imagecreatefromstring')) {
            return false;
        }

        $image = @imagecreatefromstring((string) file_get_contents($source));
        if ($image === false) {
            return false;
        }

        if ($width > $maxWidth) {
            $scaled = imagescale($image, $maxWidth, -1, IMG_BICUBIC);
            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        if ($ext !== 'jpg') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $ok = match ($ext) {
            'png' => imagepng($image, $target, 7),
            'webp' => function_exists('imagewebp') && imagewebp($image, $target, 82),
            default => imagejpeg($image, $target, 82),
        };

        imagedestroy($image);

        return (bool) $ok;
    }
}
