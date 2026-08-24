<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse?->name),

            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_company' => $this->customer_company,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,

            'status' => $this->status,
            'payment_status' => $this->payment_status,

            'currency' => $this->currency,
            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'tax_rate' => (float) $this->tax_rate,
            'tax_total' => (float) $this->tax_total,
            'shipping_total' => (float) $this->shipping_total,
            'total' => (float) $this->total,

            'notes' => $this->notes,
            'placed_at' => $this->placed_at,
            // Surfaced so the admin can explain why an order's stock moved —
            // "shipped" alone does not say whether the deduction went through.
            'stock_reserved_at' => $this->stock_reserved_at,
            'stock_deducted_at' => $this->stock_deducted_at,
            'created_at' => $this->created_at,

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
        ];
    }
}
