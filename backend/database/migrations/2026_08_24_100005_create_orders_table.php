<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer details are denormalised onto the order rather than pointing at a
 * customers table. There is no customer account system on this site — orders
 * are raised internally from enquiries and phone calls — and an order should
 * keep the address it was shipped to even if that customer later moves.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            // Which warehouse fulfils the order — the one stock is reserved
            // against and later deducted from.
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();

            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, refunded

            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // percent, applied to subtotal - discount
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('shipping_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();

            // Stock side-effect markers. The status column alone cannot say
            // whether stock was already moved — an order can reach "shipped"
            // from several states, and a save must not deduct twice. See
            // InventoryService::syncOrderStock().
            $table->timestamp('stock_reserved_at')->nullable();
            $table->timestamp('stock_deducted_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
