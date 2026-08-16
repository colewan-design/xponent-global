<?php

namespace App\Http\Resources;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_opening_id' => $this->job_opening_id,
            'job_title' => $this->whenLoaded('jobOpening', fn () => $this->jobOpening->title),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cover_letter' => $this->cover_letter,
            'resume' => FileUrl::resolve($this->resume),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
