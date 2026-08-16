<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryImageResource;
use App\Models\GalleryImage;

class GalleryImageController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->get();

        return GalleryImageResource::collection($images);
    }
}
