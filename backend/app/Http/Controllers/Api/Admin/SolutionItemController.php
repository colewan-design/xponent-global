<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolutionItemResource;
use App\Models\SolutionItem;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SolutionItemController extends Controller
{
    use UploadsFiles;

    public function index(Request $request)
    {
        return SolutionItemResource::collection(
            SolutionItem::query()
                ->when($request->query('solution_category_id'), fn ($query, $id) => $query->where('solution_category_id', $id))
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function show(SolutionItem $solutionItem): SolutionItemResource
    {
        return new SolutionItemResource($solutionItem);
    }

    public function store(Request $request): SolutionItemResource
    {
        $data = $this->validated($request);
        $data['image'] = $this->storeUpload($request, 'image', 'solutions');

        return new SolutionItemResource(SolutionItem::create($data));
    }

    public function update(Request $request, SolutionItem $solutionItem): SolutionItemResource
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($solutionItem->image);
            $data['image'] = $this->storeUpload($request, 'image', 'solutions');
        }

        $solutionItem->update($data);

        return new SolutionItemResource($solutionItem);
    }

    public function destroy(SolutionItem $solutionItem): JsonResponse
    {
        Storage::disk('public')->delete($solutionItem->image);
        $solutionItem->delete();

        return response()->json(['message' => 'Solution item deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'solution_category_id' => ['required', 'exists:solution_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer'],
        ]);
    }
}
