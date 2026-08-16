<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->where('published', true)
            ->when($request->query('type'), fn ($query, $type) => $query->where('type', $type))
            ->orderByDesc('published_at')
            ->paginate(9);

        return PostResource::collection($posts);
    }

    public function show(Post $post): PostResource
    {
        abort_unless($post->published, 404);

        return new PostResource($post);
    }
}
