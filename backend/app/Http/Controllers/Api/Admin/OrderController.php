<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request)
    {
        $query = Order::query()
            ->with('warehouse')
            ->withCount('items')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('payment_status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id));

        if ($term = $request->query('search')) {
            $query->where(function (Builder $q) use ($term) {
                foreach (['order_number', 'customer_name', 'customer_company', 'customer_email'] as $column) {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            });
        }

        return OrderResource::collection($query->orderByDesc('id')->paginate(20)->withQueryString());
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['items', 'warehouse']));
    }

    public function store(Request $request): OrderResource
    {
        $data = $this->validated($request, itemsRequired: true);
        $this->assertWarehouseForStatus($data);

        $order = DB::transaction(function () use ($data, $request) {
            $order = Order::create([
                ...$this->orderAttributes($data),
                // Generated server-side rather than accepted from the client so
                // the sequence stays gap-free and unguessable from the form.
                'order_number' => Order::nextOrderNumber(),
                // An order raised in the admin was almost always taken today;
                // the field stays editable for one keyed in after the fact.
                'placed_at' => $data['placed_at'] ?? now(),
            ]);

            $this->replaceItems($order, $data['items']);
            $order->recalculateTotals();
            $this->inventory->syncOrderStock($order->refresh(), $request->user());

            return $order;
        });

        return new OrderResource($order->load(['items', 'warehouse']));
    }

    public function update(Request $request, Order $order): OrderResource
    {
        $data = $this->validated($request, itemsRequired: false);
        $this->assertWarehouseForStatus($data);

        DB::transaction(function () use ($data, $order, $request) {
            // Unwind before touching anything: the reservation and deduction on
            // record were computed from the lines as they stand right now, so
            // they have to be reversed against those lines and not the new ones.
            $this->inventory->unwindOrderStock($order, $request->user());

            $order->update($this->orderAttributes($data));

            if (array_key_exists('items', $data)) {
                $this->replaceItems($order, $data['items']);
            }

            $order->recalculateTotals();
            $this->inventory->syncOrderStock($order->refresh(), $request->user());
        });

        return new OrderResource($order->load(['items', 'warehouse']));
    }

    /**
     * Status-only change, for the dropdown on the orders list.
     *
     * Separate from update() so moving an order to "shipped" does not require
     * the caller to round-trip the whole customer record and every line.
     */
    public function updateStatus(Request $request, Order $order): OrderResource
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(Order::STATUSES)],
            'payment_status' => ['sometimes', Rule::in(Order::PAYMENT_STATUSES)],
        ]);

        $this->assertWarehouseForStatus([
            'status' => $data['status'] ?? $order->status,
            'warehouse_id' => $order->warehouse_id,
        ]);

        DB::transaction(function () use ($data, $order, $request) {
            $order->update($data);
            $this->inventory->syncOrderStock($order->refresh(), $request->user());
        });

        return new OrderResource($order->load(['items', 'warehouse']));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        DB::transaction(function () use ($order, $request) {
            // Give the stock back before the lines that describe it disappear.
            $this->inventory->unwindOrderStock($order, $request->user());
            $order->delete();
        });

        return response()->json(['message' => 'Order deleted.']);
    }

    /**
     * Rewrites the order's lines from the request.
     *
     * Prices and names come from the product record where one is linked — the
     * client picks *what*, the server decides what that thing is called and
     * what it is stocked as. Free-text lines (freight, handling, a one-off
     * fabrication) carry no product and simply price whatever they say.
     */
    private function replaceItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $line) {
            $product = isset($line['product_id']) ? Product::find($line['product_id']) : null;
            $quantity = round((float) $line['quantity'], 3);
            $unitPrice = round((float) $line['unit_price'], 2);

            $order->items()->create([
                'product_id' => $product?->id,
                'sku' => $product?->sku ?? ($line['sku'] ?? '—'),
                'name' => $product?->name ?? $line['name'],
                'unit' => $product?->unit ?? ($line['unit'] ?? 'kg'),
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ]);
        }
    }

    /** Order columns only — items and totals are handled separately. */
    private function orderAttributes(array $data): array
    {
        return collect($data)->only([
            'warehouse_id',
            'customer_name',
            'customer_email',
            'customer_phone',
            'customer_company',
            'shipping_address',
            'billing_address',
            'status',
            'payment_status',
            'currency',
            'discount_total',
            'tax_rate',
            'shipping_total',
            'notes',
            'placed_at',
        ])->all();
    }

    /**
     * Stock cannot move without a warehouse to move it from, and silently
     * skipping the movement would leave an order marked shipped whose goods
     * were never taken off the shelf.
     */
    private function assertWarehouseForStatus(array $data): void
    {
        $status = $data['status'] ?? 'pending';
        $movesStock = in_array($status, [...Order::RESERVING_STATUSES, ...Order::DEDUCTING_STATUSES], true);

        if ($movesStock && empty($data['warehouse_id'])) {
            throw ValidationException::withMessages([
                'warehouse_id' => "Choose a warehouse before moving an order to \"{$status}\" — it decides where the stock comes from.",
            ]);
        }
    }

    private function validated(Request $request, bool $itemsRequired): array
    {
        return $request->validate([
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],

            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:64'],
            'customer_company' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'string'],
            'billing_address' => ['nullable', 'string'],

            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],

            'currency' => ['sometimes', 'string', 'size:3'],
            'discount_total' => ['sometimes', 'numeric', 'min:0'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'shipping_total' => ['sometimes', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string'],
            'placed_at' => ['nullable', 'date'],

            'items' => [$itemsRequired ? 'required' : 'sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            // Only needed for a line with no product behind it; otherwise the
            // product's own name wins.
            'items.*.name' => ['required_without:items.*.product_id', 'nullable', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:64'],
            'items.*.unit' => ['nullable', 'string', 'max:32'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);
    }
}
