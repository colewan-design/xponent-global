<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'specification' => $this->specification,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'currency' => $this->currency,
            'weight_kg' => $this->weight_kg === null ? null : (float) $this->weight_kg,
            'reorder_level' => $this->reorder_level,
            'image' => FileUrl::resolve($this->image),
            'status' => $this->status,
            // Summed across warehouses by the controller (see
            // ProductController::baseQuery) so the products table can show a
            // stock column without a request per row. The sums come back null
            // for a product no warehouse stocks yet, which reads as zero.
            'stock_on_hand' => (float) ($this->stock_on_hand ?? 0),
            'stock_available' => (float) ($this->stock_on_hand ?? 0) - (float) ($this->stock_reserved ?? 0),
        ];
    }
}
