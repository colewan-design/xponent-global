<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource as ResourceModel;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $resources = ResourceModel::query()
            ->where('published', true)
            ->when($request->query('category'), fn ($query, $category) => $query->where('category', $category))
            ->orderBy('title')
            ->get();

        return ResourceResource::collection($resources);
    }
}
