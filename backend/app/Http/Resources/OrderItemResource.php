<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'quantity' => (float) $this->quantity,
            'line_total' => (float) $this->line_total,
        ];
    }
}
