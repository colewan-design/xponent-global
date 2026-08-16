<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\FiltersBySearch;
use App\Support\UploadsFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    use FiltersBySearch, UploadsFiles;

    public function index(Request $request)
    {
        $query = $this->applySearch(Partner::query(), $request, ['name']);

        return PartnerResource::collection(
            $query
                ->when($request->query('type'), fn ($query, $type) => $query->where('type', $type))
                ->orderBy('sort_order')
                ->paginate(30)
        );
    }

    public function show(Partner $partner): PartnerResource
    {
        return new PartnerResource($partner);
    }

    public function store(Request $request): PartnerResource
    {
        $data = $this->validated($request, true);
        $data['logo'] = $this->storeUpload($request, 'logo', 'partners');

        return new PartnerResource(Partner::create($data));
    }

    public function update(Request $request, Partner $partner): PartnerResource
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete($partner->logo);
            $data['logo'] = $this->storeUpload($request, 'logo', 'partners');
        }

        $partner->update($data);

        return new PartnerResource($partner);
    }

    public function destroy(Partner $partner): JsonResponse
    {
        Storage::disk('public')->delete($partner->logo);
        $partner->delete();

        return response()->json(['message' => 'Partner deleted.']);
    }

    private function validated(Request $request, bool $logoRequired): array
    {
        return $request->validate([
            'type' => ['required', 'in:client,brand_partner,affiliation'],
            'name' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer'],
            'logo' => [$logoRequired ? 'required' : 'sometimes', 'image', 'max:2048'],
        ]);
    }
}
