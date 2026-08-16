<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\FiltersBySearch;
use App\Support\GeneratesUniqueSlug;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use FiltersBySearch, GeneratesUniqueSlug, UploadsFiles;

    public function index(Request $request)
    {
        $query = $this->applySearch(Post::query(), $request, ['title', 'excerpt']);

        return PostResource::collection($query->orderByDesc('created_at')->paginate(20));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    public function store(Request $request): PostResource
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(Post::class, $data['title']);
        $data['cover_image'] = $this->storeUpload($request, 'cover_image', 'posts');

        $post = Post::create($data);

        return new PostResource($post);
    }

    public function update(Request $request, Post $post): PostResource
    {
        $data = $this->validated($request);

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $this->storeUpload($request, 'cover_image', 'posts');
        }

        $post->update($data);

        return new PostResource($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:news,case_study'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }
}
