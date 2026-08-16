<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolutionCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => FileUrl::resolve($this->image),
            'sort_order' => $this->sort_order,
            'items' => SolutionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
