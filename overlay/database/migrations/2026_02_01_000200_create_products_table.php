<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 64)->nullable();
            $table->string('brand')->nullable();
            $table->string('short_description', 300)->nullable();
            $table->text('description')->nullable();

            // Display only - nothing is sold online. Blank price = "Price on request".
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->string('stock_status', 20)->default('in_stock'); // in_stock|on_order|out_of_stock
            $table->json('specs')->nullable();                        // [{label, value}]
            $table->string('image_path')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
