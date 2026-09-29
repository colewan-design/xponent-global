<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProductCategoryResource;
use App\Models\ProductCategory;

/**
 * The public product catalogue.
 *
 * Distinct from Admin\ProductController, which is the stock-and-price view of
 * the same table behind auth. This one answers a visitor's question — what do
 * you actually supply, to what specification — and hands off to a quote.
 */
class ProductController extends Controller
{
    /**
     * Categories with their active products nested.
     *
     * Empty categories are dropped rather than rendered as a heading over
     * nothing: a category whose whole line is discontinued should disappear from
     * the site, not advertise a gap.
     */
    public function index()
    {
        $categories = ProductCategory::query()
            ->with(['products' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (ProductCategory $category) => $category->products->isNotEmpty())
            ->values();

        return PublicProductCategoryResource::collection($categories);
    }
}
