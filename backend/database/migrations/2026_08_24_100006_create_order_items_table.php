<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Nullable + nullOnDelete so retiring a product does not erase the
            // history of what was sold; the snapshot columns below carry it.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Snapshots taken at the time the line was added. A later rename or
            // repricing of the product must not rewrite past orders.
            $table->string('sku');
            $table->string('name');
            $table->string('unit')->default('kg');

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('quantity', 14, 3)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
