<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A catalogue category with its products nested, shaped like
 * SolutionCategoryResource so /products and /solutions can be rendered by the
 * same kind of page: one band per category, a grid of items inside it.
 */
class PublicProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'products' => PublicProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
