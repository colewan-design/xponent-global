<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolutionCategoryResource;
use App\Models\SolutionCategory;
use App\Support\GeneratesUniqueSlug;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SolutionCategoryController extends Controller
{
    use GeneratesUniqueSlug, UploadsFiles;

    public function index()
    {
        return SolutionCategoryResource::collection(
            SolutionCategory::withCount('items')->orderBy('sort_order')->get()
        );
    }

    public function show(SolutionCategory $solutionCategory): SolutionCategoryResource
    {
        return new SolutionCategoryResource($solutionCategory->load('items'));
    }

    public function store(Request $request): SolutionCategoryResource
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(SolutionCategory::class, $data['title']);
        $data['image'] = $this->storeUpload($request, 'image', 'solutions');

        return new SolutionCategoryResource(SolutionCategory::create($data));
    }

    public function update(Request $request, SolutionCategory $solutionCategory): SolutionCategoryResource
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($solutionCategory->image);
            $data['image'] = $this->storeUpload($request, 'image', 'solutions');
        }

        $solutionCategory->update($data);

        return new SolutionCategoryResource($solutionCategory);
    }

    public function destroy(SolutionCategory $solutionCategory): JsonResponse
    {
        Storage::disk('public')->delete($solutionCategory->image);
        $solutionCategory->delete();

        return response()->json(['message' => 'Solution category deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer'],
        ]);
    }
}
