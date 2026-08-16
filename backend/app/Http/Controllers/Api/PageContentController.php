<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageContentResource;
use App\Models\PageContent;

class PageContentController extends Controller
{
    public function show(string $page): PageContentResource
    {
        $content = PageContent::where('page', $page)->firstOrNew(['page' => $page], ['sections' => []]);

        return new PageContentResource($content);
    }
}
