<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Models\Resource as ResourceModel;
use App\Models\SolutionCategory;
use App\Models\SolutionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Site-wide search across everything the public API already exposes.
 *
 * A LIKE scan, not a search engine. The whole corpus is a few hundred rows of
 * catalogue copy across five tables, so an index would cost more to keep honest
 * than it saves — if the catalogue ever grows past a few thousand rows this is
 * the seam to put a real index behind, because callers only see the shape below.
 *
 * Results carry a `type` and the identifiers needed to address the thing, never
 * a URL: the site owns its own routing, and baking `/solutions#slug` into the
 * API would mean a frontend route change could only ship with a backend deploy.
 */
class SearchController extends Controller
{
    /** Per-type cap. Enough to be useful, small enough that no query is slow. */
    private const PER_TYPE_LIMIT = 8;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim($validated['q'] ?? '');

        // One character matches most of the catalogue and tells the visitor
        // nothing. Return the empty shape rather than an error so the live
        // results panel can call on every keystroke without handling a 422.
        if (Str::length($term) < 2) {
            return response()->json([
                'data' => ['query' => $term, 'total' => 0, 'groups' => []],
            ]);
        }

        $groups = array_values(array_filter([
            $this->group('solution', 'Solutions', $this->solutions($term)),
            $this->group('product', 'Products', $this->products($term)),
            $this->group('article', 'News & insights', $this->posts($term)),
            $this->group('resource', 'Resources', $this->resources($term)),
        ], fn (array $group) => $group['results'] !== []));

        return response()->json([
            'data' => [
                'query' => $term,
                'total' => array_sum(array_map(fn ($group) => count($group['results']), $groups)),
                'groups' => $groups,
            ],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, mixed>
     */
    private function group(string $type, string $label, array $results): array
    {
        return ['type' => $type, 'label' => $label, 'results' => $results];
    }

    /**
     * Solution categories and the items inside them, in one list.
     *
     * An item match reports its parent's slug as well, because the site renders
     * items inside the category band — `/solutions#<category slug>` is the only
     * address an item has.
     */
    private function solutions(string $term): array
    {
        $categories = SolutionCategory::query()
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', $this->like($term))
                ->orWhere('description', 'like', $this->like($term)))
            ->orderBy('sort_order')
            ->limit(self::PER_TYPE_LIMIT)
            ->get()
            ->map(fn (SolutionCategory $category) => [
                'title' => $category->title,
                'snippet' => $this->snippet($category->description, $term),
                'slug' => $category->slug,
                'parent_slug' => null,
                'context' => 'Solution range',
            ]);

        $remaining = self::PER_TYPE_LIMIT - $categories->count();

        if ($remaining < 1) {
            return $categories->all();
        }

        $items = SolutionItem::query()
            ->with('category:id,slug,title')
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', $this->like($term))
                ->orWhere('description', 'like', $this->like($term)))
            ->orderBy('sort_order')
            ->limit($remaining)
            ->get()
            ->map(fn (SolutionItem $item) => [
                'title' => $item->title,
                'snippet' => $this->snippet($item->description, $term),
                'slug' => $item->category?->slug,
                'parent_slug' => $item->category?->slug,
                'context' => $item->category?->title ?? 'Solutions',
            ])
            // A category deleted out from under its items leaves nothing to link
            // to; drop those rather than render a dead result.
            ->filter(fn (array $result) => $result['slug'] !== null);

        return $categories->concat($items)->values()->all();
    }

    private function products(string $term): array
    {
        return Product::query()
            ->active()
            ->with('category:id,slug,name')
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', $this->like($term))
                ->orWhere('sku', 'like', $this->like($term))
                ->orWhere('specification', 'like', $this->like($term))
                ->orWhere('description', 'like', $this->like($term)))
            ->orderBy('name')
            ->limit(self::PER_TYPE_LIMIT)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'snippet' => $this->snippet($product->specification ?: $product->description, $term),
                'slug' => $product->category?->slug,
                'parent_slug' => $product->category?->slug,
                'context' => $product->sku,
            ])
            ->values()
            ->all();
    }

    private function posts(string $term): array
    {
        return Post::query()
            ->where('published', true)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', $this->like($term))
                ->orWhere('excerpt', 'like', $this->like($term))
                ->orWhere('body', 'like', $this->like($term)))
            ->orderByDesc('published_at')
            ->limit(self::PER_TYPE_LIMIT)
            ->get()
            ->map(fn (Post $post) => [
                'title' => $post->title,
                'snippet' => $this->snippet($post->excerpt ?: $post->body, $term),
                'slug' => $post->slug,
                'parent_slug' => null,
                'context' => $post->published_at?->format('j M Y'),
            ])
            ->all();
    }

    private function resources(string $term): array
    {
        return ResourceModel::query()
            ->where('published', true)
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', $this->like($term))
                ->orWhere('description', 'like', $this->like($term)))
            ->orderBy('title')
            ->limit(self::PER_TYPE_LIMIT)
            ->get()
            ->map(fn (ResourceModel $resource) => [
                'title' => $resource->title,
                'snippet' => $this->snippet($resource->description, $term),
                'slug' => null,
                'parent_slug' => null,
                'context' => $resource->category,
            ])
            ->all();
    }

    /**
     * A LIKE pattern with the term's own wildcards defanged.
     *
     * Without this a visitor typing `%` matches every row in the table, and `_`
     * silently matches any character — both are literal text to someone typing a
     * part number, not operators.
     */
    private function like(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }

    /**
     * The window of text around the first hit, so a result explains why it
     * matched instead of showing the same opening sentence every time.
     */
    private function snippet(?string $body, string $term): ?string
    {
        if (! $body) {
            return null;
        }

        $flat = trim(preg_replace('/\s+/', ' ', $body));
        $at = mb_stripos($flat, $term);

        if ($at === false) {
            return Str::limit($flat, 140);
        }

        $start = max(0, $at - 60);
        $window = mb_substr($flat, $start, 180);

        return ($start > 0 ? '…' : '').trim($window).(mb_strlen($flat) > $start + 180 ? '…' : '');
    }
}
