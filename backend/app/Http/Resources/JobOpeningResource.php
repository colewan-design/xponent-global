<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOpeningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'department' => $this->department,
            'location' => $this->location,
            'employment_type' => $this->employment_type,
            'summary' => $this->summary,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'status' => $this->status,
            'posted_at' => $this->posted_at,
            'applications_count' => $this->whenCounted('applications'),
        ];
    }
}
