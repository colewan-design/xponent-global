<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Free text rather than structured attributes: wire specs read as
            // "2.5mm dia, Zn 60g/m², 1000kg coil" and every product line words
            // them differently, so a fixed attribute set would fit none of them.
            $table->text('specification')->nullable();
            $table->string('unit')->default('kg'); // kg, tonne, coil, roll, metre, piece
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->decimal('weight_kg', 10, 3)->nullable();
            // Fallback low-stock threshold. inventory_items carries a per-warehouse
            // override; this is what a new warehouse row starts from.
            $table->unsignedInteger('reorder_level')->default(0);
            $table->string('image')->nullable();
            $table->string('status')->default('active'); // active, inactive, discontinued
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
