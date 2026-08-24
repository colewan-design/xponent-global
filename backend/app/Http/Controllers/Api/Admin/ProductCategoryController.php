<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Support\GeneratesUniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use GeneratesUniqueSlug;

    public function index()
    {
        return ProductCategoryResource::collection(
            ProductCategory::query()
                ->withCount('products')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    public function show(ProductCategory $productCategory): ProductCategoryResource
    {
        return new ProductCategoryResource($productCategory->loadCount('products'));
    }

    public function store(Request $request): ProductCategoryResource
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(ProductCategory::class, $data['name']);

        return new ProductCategoryResource(ProductCategory::create($data));
    }

    public function update(Request $request, ProductCategory $productCategory): ProductCategoryResource
    {
        $data = $this->validated($request);

        // The slug is only re-derived on a genuine rename, so bookmarked or
        // referenced URLs survive an edit that only touches the description.
        if ($data['name'] !== $productCategory->name) {
            $data['slug'] = $this->uniqueSlug(ProductCategory::class, $data['name'], $productCategory->id);
        }

        $productCategory->update($data);

        return new ProductCategoryResource($productCategory->loadCount('products'));
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        // Products outlive their category — the FK is nullOnDelete, so they
        // simply become uncategorised rather than disappearing with it.
        $productCategory->delete();

        return response()->json(['message' => 'Product category deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
