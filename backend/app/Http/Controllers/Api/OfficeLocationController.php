<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeLocationResource;
use App\Models\OfficeLocation;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $locations = OfficeLocation::orderBy('sort_order')->get();

        return OfficeLocationResource::collection($locations);
    }
}
