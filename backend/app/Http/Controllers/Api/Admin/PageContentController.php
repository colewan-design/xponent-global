<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    /**
     * Returns raw (unresolved) section image paths so edits round-trip
     * correctly — the public API resolves them to absolute URLs instead.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PageContent::orderBy('page')->get(['id', 'page', 'sections']),
        ]);
    }

    public function update(Request $request, string $page): JsonResponse
    {
        $data = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.heading' => ['nullable', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string'],
            'sections.*.image' => ['nullable', 'string'],
        ]);

        $content = PageContent::updateOrCreate(['page' => $page], $data);

        return response()->json(['data' => $content]);
    }
}
