<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolutionCategoryResource;
use App\Models\SolutionCategory;

class SolutionCategoryController extends Controller
{
    public function index()
    {
        $categories = SolutionCategory::with('items')->orderBy('sort_order')->get();

        return SolutionCategoryResource::collection($categories);
    }

    public function show(SolutionCategory $solutionCategory): SolutionCategoryResource
    {
        return new SolutionCategoryResource($solutionCategory->load('items'));
    }
}
