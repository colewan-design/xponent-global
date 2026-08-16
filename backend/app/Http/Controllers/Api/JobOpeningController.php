<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobOpeningResource;
use App\Models\JobOpening;

class JobOpeningController extends Controller
{
    public function index()
    {
        $jobs = JobOpening::query()
            ->where('status', 'open')
            ->orderByDesc('posted_at')
            ->get();

        return JobOpeningResource::collection($jobs);
    }

    public function show(JobOpening $jobOpening): JobOpeningResource
    {
        abort_unless($jobOpening->status === 'open', 404);

        return new JobOpeningResource($jobOpening);
    }
}
