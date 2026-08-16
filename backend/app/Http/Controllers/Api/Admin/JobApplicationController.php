<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobApplicationResource;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = JobApplication::query()
            ->with('jobOpening')
            ->when($request->query('job_opening_id'), fn ($query, $id) => $query->where('job_opening_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return JobApplicationResource::collection($applications);
    }

    public function show(JobApplication $jobApplication): JobApplicationResource
    {
        return new JobApplicationResource($jobApplication->load('jobOpening'));
    }

    public function update(Request $request, JobApplication $jobApplication): JobApplicationResource
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,rejected,hired'],
        ]);

        $jobApplication->update($data);

        return new JobApplicationResource($jobApplication);
    }

    public function destroy(JobApplication $jobApplication): JsonResponse
    {
        Storage::disk('public')->delete($jobApplication->resume);
        $jobApplication->delete();

        return response()->json(['message' => 'Application deleted.']);
    }
}
