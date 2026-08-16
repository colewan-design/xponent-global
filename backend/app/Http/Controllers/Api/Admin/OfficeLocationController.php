<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeLocationResource;
use App\Models\OfficeLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index()
    {
        return OfficeLocationResource::collection(OfficeLocation::orderBy('sort_order')->get());
    }

    public function show(OfficeLocation $officeLocation): OfficeLocationResource
    {
        return new OfficeLocationResource($officeLocation);
    }

    public function store(Request $request): OfficeLocationResource
    {
        return new OfficeLocationResource(OfficeLocation::create($this->validated($request)));
    }

    public function update(Request $request, OfficeLocation $officeLocation): OfficeLocationResource
    {
        $officeLocation->update($this->validated($request));

        return new OfficeLocationResource($officeLocation);
    }

    public function destroy(OfficeLocation $officeLocation): JsonResponse
    {
        $officeLocation->delete();

        return response()->json(['message' => 'Office location deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
        ]);
    }
}
