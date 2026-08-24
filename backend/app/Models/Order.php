<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Statuses that commit stock to the order without moving it out of the
     * warehouse yet — the goods are picked but not despatched.
     */
    public const RESERVING_STATUSES = ['confirmed', 'processing'];

    /** Statuses where the goods have left the building and stock is gone. */
    public const DEDUCTING_STATUSES = ['shipped', 'delivered'];

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid', 'refunded'];

    protected $fillable = [
        'order_number',
        'warehouse_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'shipping_address',
        'billing_address',
        'status',
        'payment_status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_rate',
        'tax_total',
        'shipping_total',
        'total',
        'notes',
        'placed_at',
        'stock_reserved_at',
        'stock_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'stock_reserved_at' => 'datetime',
            'stock_deducted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Recomputes the money columns from the saved line items.
     *
     * Totals are always derived here rather than trusted from the client — the
     * admin shows a running total while editing, but that figure is only ever a
     * preview of what this method will decide.
     */
    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('line_total');
        $discount = min((float) $this->discount_total, $subtotal);
        $taxable = $subtotal - $discount;
        $tax = round($taxable * ((float) $this->tax_rate / 100), 2);

        $this->forceFill([
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discount, 2),
            'tax_total' => $tax,
            'total' => round($taxable + $tax + (float) $this->shipping_total, 2),
        ])->save();
    }

    /**
     * Order numbers are human-facing and quoted on the phone, so they are
     * sequential per calendar year rather than a random string.
     */
    public static function nextOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "XG-{$year}-";

        $last = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
