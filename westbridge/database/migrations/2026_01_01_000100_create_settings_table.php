<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 40);
            $table->string('key', 80);
            $table->json('value')->nullable();
            $table->string('type', 20)->default('string'); // string|text|boolean|number|url|email|phone|image
            $table->string('label')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
