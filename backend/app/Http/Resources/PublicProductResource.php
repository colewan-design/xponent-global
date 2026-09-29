<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A product as the public catalogue shows it.
 *
 * Deliberately narrower than ProductResource, which serves the admin. Unit
 * price, currency, reorder level and the stock balances are commercial
 * information: the site sells on quotation, so every card on /products ends at
 * "Request a quote" rather than at a number a competitor can read off. Weight is
 * omitted for a different reason — the seeded values are per-unit figures whose
 * basis varies by line (0.002 against a `kg` unit), so they would confuse rather
 * than inform until the catalogue data is tidied.
 *
 * Adding a field here publishes it. Add nothing the sales team would not print
 * in a public catalogue PDF.
 */
class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'specification' => $this->specification,
            'unit' => $this->unit,
            'image' => FileUrl::resolve($this->image),
            'category_slug' => $this->whenLoaded('category', fn () => $this->category?->slug),
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
        ];
    }
}
