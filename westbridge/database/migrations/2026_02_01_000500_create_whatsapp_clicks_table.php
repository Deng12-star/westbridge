<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| One row each time a visitor taps "Order on WhatsApp". WhatsApp itself tells
| the site nothing, so this is the only record of which products people ask
| about - it feeds the dashboard's "most enquired" list.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_clicks');
    }
};
