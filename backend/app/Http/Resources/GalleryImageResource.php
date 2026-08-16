<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => FileUrl::resolve($this->image),
            'caption' => $this->caption,
            'sort_order' => $this->sort_order,
        ];
    }
}
