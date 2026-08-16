<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::query()
            ->when($request->query('type'), fn ($query, $type) => $query->where('type', $type))
            ->orderBy('sort_order')
            ->get();

        return PartnerResource::collection($partners);
    }
}
