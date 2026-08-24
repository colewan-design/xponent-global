<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * On-hand balance for one product in one warehouse.
 *
 * This is a cached running total, not the source of truth — `stock_movements`
 * is the ledger, and every change to `quantity` here is written in the same
 * transaction as the movement that caused it (see InventoryService).
 *
 * Quantities are decimal, not integer: wire ships by weight, so a line can
 * legitimately read 1,250.500 kg.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 14, 3)->default(0);
            // Committed to confirmed orders but not yet shipped. Available stock
            // is quantity - reserved_quantity.
            $table->decimal('reserved_quantity', 14, 3)->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->string('bin_location')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
