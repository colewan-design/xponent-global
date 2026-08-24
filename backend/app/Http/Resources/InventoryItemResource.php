<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_sku' => $this->whenLoaded('product', fn () => $this->product?->sku),
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'unit' => $this->whenLoaded('product', fn () => $this->product?->unit),
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse?->name),
            'quantity' => (float) $this->quantity,
            'reserved_quantity' => (float) $this->reserved_quantity,
            'available' => $this->available,
            'reorder_level' => $this->reorder_level,
            'bin_location' => $this->bin_location,
            'stock_status' => $this->stock_status,
        ];
    }
}
