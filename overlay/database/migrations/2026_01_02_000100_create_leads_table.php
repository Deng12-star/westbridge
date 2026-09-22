<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();

            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();

            $table->string('service_interest', 40)->nullable();
            $table->text('message')->nullable();

            $table->string('status', 20)->default('new');
            $table->string('source', 30);

            // Points at the capture record: quote_requests, contact_messages,
            // or a product for an enquiry. One hub, many entry points.
            $table->nullableMorphs('sourceable');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('value_estimate', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();

            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('lost_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['source', 'created_at']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
