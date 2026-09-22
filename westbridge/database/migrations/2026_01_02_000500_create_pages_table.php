<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('heading')->nullable();
            $table->text('lede')->nullable();

            // Typed blocks, not a free-form page builder — the design system
            // stays intact no matter who edits the content.
            $table->json('blocks')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_published')->default(true);
            // System pages cannot be deleted from admin; their slug is a route.
            $table->boolean('is_system')->default(false);

            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
