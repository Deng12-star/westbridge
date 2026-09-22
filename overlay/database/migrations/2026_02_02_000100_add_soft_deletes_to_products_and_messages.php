<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Deleting a product or a message now moves it to "Recently deleted" for 30
| days, where it can be restored. After that it is removed for good by the
| scheduled `model:prune` command.
*/
return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'contact_messages'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->softDeletes();
                    $t->index('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['products', 'contact_messages'] as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->dropIndex(['deleted_at']);
                $t->dropSoftDeletes();
            });
        }
    }
};
