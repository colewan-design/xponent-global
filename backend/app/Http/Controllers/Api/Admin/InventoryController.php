<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryItemResource;
use App\Http\Resources\StockMovementResource;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public const MOVEMENT_TYPES = ['in', 'out', 'adjustment'];

    public const REASONS = ['purchase', 'sale', 'return', 'damage', 'stock_take', 'correction', 'initial', 'transfer'];

    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request)
    {
        $query = InventoryItem::query()
            ->with(['product', 'warehouse'])
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('low_stock'), fn ($q) => $q->lowStock());

        // Search reaches through to the product — an inventory row has no name
        // of its own, and "where is the 2.5mm galv" is how stock gets looked up.
        if ($term = $request->query('search')) {
            $query->whereHas('product', function (Builder $productQuery) use ($term) {
                $productQuery->where('sku', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%");
            });
        }

        $query->orderBy(
            Product::select('name')->whereColumn('products.id', 'inventory_items.product_id')
        );

        return InventoryItemResource::collection($query->paginate(25)->withQueryString());
    }

    /**
     * Edits the row's own settings only.
     *
     * Quantities are deliberately not writable here — every change to a balance
     * has to leave a ledger entry, which is what `adjust()` is for.
     */
    public function update(Request $request, InventoryItem $inventory): InventoryItemResource
    {
        $data = $request->validate([
            'reorder_level' => ['required', 'integer', 'min:0'],
            'bin_location' => ['nullable', 'string', 'max:64'],
        ]);

        $inventory->update($data);

        return new InventoryItemResource($inventory->load(['product', 'warehouse']));
    }

    /**
     * Posts a manual stock movement.
     *
     * Takes product + warehouse rather than an inventory row id so that the
     * first receipt of a product into a warehouse works before any row exists.
     */
    public function adjust(Request $request): InventoryItemResource
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', Rule::in(self::MOVEMENT_TYPES)],
            // A zero movement would write a ledger line that changes nothing;
            // `in`/`out` are signed by the service, so only `adjustment` may
            // legitimately arrive negative.
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['nullable', Rule::in(self::REASONS)],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $this->inventory->record($product, (int) $data['warehouse_id'], $data['type'], (float) $data['quantity'], [
            'reason' => $data['reason'] ?? null,
            'reference' => $data['reference'] ?? null,
            'note' => $data['note'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        return new InventoryItemResource(
            $this->inventory->itemFor($product, (int) $data['warehouse_id'])->load(['product', 'warehouse'])
        );
    }

    /** The ledger behind the balances, newest first. */
    public function movements(Request $request)
    {
        $query = StockMovement::query()
            ->with(['product', 'warehouse', 'user'])
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type));

        return StockMovementResource::collection(
            $query->orderByDesc('id')->paginate(25)->withQueryString()
        );
    }
}
