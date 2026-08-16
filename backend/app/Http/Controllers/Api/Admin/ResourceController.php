<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource as ResourceModel;
use App\Support\FiltersBySearch;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    use FiltersBySearch, UploadsFiles;

    public function index(Request $request)
    {
        $query = $this->applySearch(ResourceModel::query(), $request, ['title', 'description']);

        return ResourceResource::collection($query->orderByDesc('created_at')->paginate(20));
    }

    public function show(ResourceModel $resource): ResourceResource
    {
        return new ResourceResource($resource);
    }

    public function store(Request $request): ResourceResource
    {
        $data = $request->validate([
            'category' => ['required', 'in:technical_document,datasheet,safety_compliance'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'published' => ['sometimes', 'boolean'],
        ]);

        $data['file'] = $this->storeUpload($request, 'file', 'resources');

        $resource = ResourceModel::create($data);

        return new ResourceResource($resource);
    }

    public function update(Request $request, ResourceModel $resource): ResourceResource
    {
        $data = $request->validate([
            'category' => ['required', 'in:technical_document,datasheet,safety_compliance'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['sometimes', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'published' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($resource->file);
            $data['file'] = $this->storeUpload($request, 'file', 'resources');
        }

        $resource->update($data);

        return new ResourceResource($resource);
    }

    public function destroy(ResourceModel $resource): JsonResponse
    {
        Storage::disk('public')->delete($resource->file);
        $resource->delete();

        return response()->json(['message' => 'Resource deleted.']);
    }
}
