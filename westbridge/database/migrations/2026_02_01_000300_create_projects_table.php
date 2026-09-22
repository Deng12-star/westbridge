<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('client')->nullable();
            $table->string('location')->nullable();
            $table->string('industry')->nullable();
            $table->string('category')->nullable();
            $table->string('service')->nullable();
            $table->string('status', 40)->nullable();     // Live, In development...
            $table->string('year', 10)->nullable();
            $table->string('mock', 20)->default('dashboard'); // drawn panel shown without a screenshot
            $table->string('live_url')->nullable();
            $table->boolean('show_live_link')->default(false);
            $table->string('summary', 400)->nullable();
            $table->json('overview')->nullable();          // paragraphs
            $table->json('scope')->nullable();             // bullet points
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
