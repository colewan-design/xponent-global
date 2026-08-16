<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'logo' => FileUrl::resolve($this->logo),
            'website_url' => $this->website_url,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ];
    }
}
