<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page' => $this->page,
            'sections' => collect($this->sections)->map(fn (array $section) => [
                'heading' => $section['heading'] ?? null,
                'body' => $section['body'] ?? null,
                'image' => FileUrl::resolve($section['image'] ?? null),
            ])->all(),
        ];
    }
}
