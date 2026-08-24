<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'product_category_id',
        'sku',
        'name',
        'slug',
        'description',
        'specification',
        'unit',
        'unit_price',
        'currency',
        'weight_kg',
        'reorder_level',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'reorder_level' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * On-hand across every warehouse. Reads the cached balances rather than
     * summing the ledger — the two are kept in step transactionally.
     */
    public function totalStock(): float
    {
        return (float) $this->inventoryItems()->sum('quantity');
    }

    public function totalAvailableStock(): float
    {
        return (float) $this->inventoryItems()->sum('quantity')
            - (float) $this->inventoryItems()->sum('reserved_quantity');
    }
}
