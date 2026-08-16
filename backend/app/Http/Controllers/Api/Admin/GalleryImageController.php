<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryImageResource;
use App\Models\GalleryImage;
use App\Support\FiltersBySearch;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryImageController extends Controller
{
    use FiltersBySearch, UploadsFiles;

    public function index(Request $request)
    {
        $query = $this->applySearch(GalleryImage::query(), $request, ['caption']);

        return GalleryImageResource::collection($query->orderBy('sort_order')->paginate(24));
    }

    public function show(GalleryImage $galleryImage): GalleryImageResource
    {
        return new GalleryImageResource($galleryImage);
    }

    public function store(Request $request): GalleryImageResource
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $data['image'] = $this->storeUpload($request, 'image', 'gallery');

        return new GalleryImageResource(GalleryImage::create($data));
    }

    public function update(Request $request, GalleryImage $galleryImage): GalleryImageResource
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'image' => ['sometimes', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($galleryImage->image);
            $data['image'] = $this->storeUpload($request, 'image', 'gallery');
        }

        $galleryImage->update($data);

        return new GalleryImageResource($galleryImage);
    }

    public function destroy(GalleryImage $galleryImage): JsonResponse
    {
        Storage::disk('public')->delete($galleryImage->image);
        $galleryImage->delete();

        return response()->json(['message' => 'Image deleted.']);
    }
}
