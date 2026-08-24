<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only stock ledger. Never updated or deleted by the app — a mistaken
 * movement is corrected by posting a reversing one, so the history of how a
 * balance got where it is stays intact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            // Who posted it. Nullable so deleting an admin account does not take
            // the ledger with it.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // in, out, adjustment
            // Signed delta actually applied, so summing this column over a
            // product/warehouse reproduces the on-hand balance exactly.
            $table->decimal('quantity', 14, 3);
            $table->decimal('balance_after', 14, 3);
            $table->string('reason')->nullable(); // purchase, sale, order_shipment, return, damage, stock_take, correction, initial
            $table->string('reference')->nullable(); // order number, PO number, delivery note
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
