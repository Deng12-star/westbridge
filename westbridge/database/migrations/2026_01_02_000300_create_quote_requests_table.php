<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();

            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone', 32);
            $table->string('email');

            $table->string('service', 40);
            $table->text('description');
            $table->string('budget_range', 20)->nullable();
            $table->string('timeline', 20)->nullable();

            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->unsignedInteger('attachment_size')->nullable();

            // Where the request came from — feeds the "which page converts" report.
            $table->string('source_page')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();

            $table->index(['service', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
