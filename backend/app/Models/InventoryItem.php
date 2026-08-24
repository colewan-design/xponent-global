<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'reorder_level',
        'bin_location',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'reserved_quantity' => 'decimal:3',
            'reorder_level' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Free to promise: on hand less what confirmed orders have committed. */
    public function getAvailableAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved_quantity;
    }

    /**
     * Drives the badge in the admin. "low" fires on the *available* figure, not
     * on hand — stock already committed to an order cannot be sold twice, so a
     * warehouse full of reserved coils still needs reordering.
     */
    public function getStockStatusAttribute(): string
    {
        $available = $this->available;

        if ($available <= 0) {
            return 'out_of_stock';
        }

        if ($this->reorder_level > 0 && $available <= $this->reorder_level) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('reorder_level', '>', 0)
            ->whereRaw('(quantity - reserved_quantity) <= reorder_level');
    }
}
