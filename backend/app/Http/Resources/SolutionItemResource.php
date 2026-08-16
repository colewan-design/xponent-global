<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolutionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'solution_category_id' => $this->solution_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => FileUrl::resolve($this->image),
            'sort_order' => $this->sort_order,
        ];
    }
}
