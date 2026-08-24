<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\FiltersBySearch;
use App\Support\GeneratesUniqueSlug;
use App\Support\UploadsFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use FiltersBySearch, GeneratesUniqueSlug, UploadsFiles;

    public const UNITS = ['kg', 'tonne', 'coil', 'roll', 'metre', 'piece'];

    public const STATUSES = ['active', 'inactive', 'discontinued'];

    public function index(Request $request)
    {
        $query = $this->applySearch($this->baseQuery(), $request, ['sku', 'name', 'specification']);

        $query
            ->when($request->query('product_category_id'), fn ($q, $id) => $q->where('product_category_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status));

        // The order form loads the whole active catalogue into a line picker in
        // one request, so the page size is caller-controlled — capped, because
        // "give me everything" should not become a way to pull the table.
        $perPage = min(max((int) $request->query('per_page', 20), 1), 200);

        return ProductResource::collection($query->orderBy('name')->paginate($perPage)->withQueryString());
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($this->withStock($product));
    }

    public function store(Request $request): ProductResource
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(Product::class, $data['name']);
        $data['image'] = $this->storeUpload($request, 'image', 'products');

        $product = Product::create($data);

        return new ProductResource($this->withStock($product));
    }

    public function update(Request $request, Product $product): ProductResource
    {
        $data = $this->validated($request, $product);

        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug(Product::class, $data['name'], $product->id);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $this->storeUpload($request, 'image', 'products');
        }

        $product->update($data);

        return new ProductResource($this->withStock($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        // Deleting takes the product's stock rows and ledger with it (both
        // cascade), which is why anything with history should be marked
        // discontinued instead. Sold lines survive — order_items keeps its own
        // SKU and name snapshot and only nulls the link.
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    /**
     * Products with their stock totals folded in.
     *
     * Two aggregate sub-queries rather than a join: a join across
     * inventory_items would multiply the product row per warehouse and break
     * the paginator's count.
     */
    private function baseQuery(): Builder
    {
        return Product::query()
            ->with('category')
            ->withSum('inventoryItems as stock_on_hand', 'quantity')
            ->withSum('inventoryItems as stock_reserved', 'reserved_quantity');
    }

    /**
     * The single-model counterpart of baseQuery().
     *
     * Loaded onto the model in hand rather than re-fetched by id: a re-fetched
     * model has lost `wasRecentlyCreated`, and a resource built from one
     * answers a POST with 200 instead of 201.
     */
    private function withStock(Product $product): Product
    {
        return $product
            ->load('category')
            ->loadSum('inventoryItems as stock_on_hand', 'quantity')
            ->loadSum('inventoryItems as stock_reserved', 'reserved_quantity');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'unit' => ['required', Rule::in(self::UNITS)],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['sometimes', 'integer', 'min:0'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);
    }
}
