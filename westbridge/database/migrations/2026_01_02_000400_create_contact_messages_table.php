<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');

            $table->boolean('is_read')->default(false);
            $table->timestamp('replied_at')->nullable();

            $table->string('source_page')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();

            $table->index(['is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
