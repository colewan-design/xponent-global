<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobOpeningResource;
use App\Models\JobOpening;
use App\Support\FiltersBySearch;
use App\Support\GeneratesUniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    use FiltersBySearch, GeneratesUniqueSlug;

    public function index(Request $request)
    {
        $query = $this->applySearch(JobOpening::query(), $request, ['title', 'department', 'location']);

        return JobOpeningResource::collection(
            $query->withCount('applications')->orderByDesc('created_at')->paginate(20)
        );
    }

    public function show(JobOpening $jobOpening): JobOpeningResource
    {
        return new JobOpeningResource($jobOpening->loadCount('applications'));
    }

    public function store(Request $request): JobOpeningResource
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(JobOpening::class, $data['title']);
        $data['posted_at'] = $data['posted_at'] ?? now();

        $jobOpening = JobOpening::create($data);

        return new JobOpeningResource($jobOpening);
    }

    public function update(Request $request, JobOpening $jobOpening): JobOpeningResource
    {
        $data = $this->validated($request);
        $jobOpening->update($data);

        return new JobOpeningResource($jobOpening);
    }

    public function destroy(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening->delete();

        return response()->json(['message' => 'Job opening deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:full_time,part_time,contract'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
            'posted_at' => ['nullable', 'date'],
        ]);
    }
}
