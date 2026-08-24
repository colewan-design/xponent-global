<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The single place stock levels are allowed to change.
 *
 * Two rules hold everywhere in here:
 *
 *  1. `stock_movements` is the ledger and `inventory_items.quantity` is a
 *     cache of it. Both are written inside one transaction, so a crash cannot
 *     leave a balance that the history does not explain.
 *
 *  2. On-hand may never go negative. A reservation *may* exceed what is on the
 *     floor — that is a backorder, and the admin shows it as out of stock —
 *     but goods that do not exist cannot be despatched, so the deduction is
 *     refused instead of quietly writing a negative balance.
 */
class InventoryService
{
    /**
     * Posts one movement and moves the balance with it.
     *
     * `in` and `out` take a positive magnitude and are signed here; only
     * `adjustment` accepts a signed figure, since a correction can go either
     * way.
     */
    public function record(
        Product $product,
        int $warehouseId,
        string $type,
        float $quantity,
        array $context = [],
    ): StockMovement {
        $delta = match ($type) {
            'in' => abs($quantity),
            'out' => -abs($quantity),
            default => $quantity,
        };

        return DB::transaction(function () use ($product, $warehouseId, $type, $delta, $context) {
            $item = $this->itemFor($product, $warehouseId, lock: true);

            $balance = round((float) $item->quantity + $delta, 3);

            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Not enough stock of {$product->sku} at this warehouse — "
                        .rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.').' available.',
                ]);
            }

            $item->update(['quantity' => $balance]);

            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'user_id' => $context['user_id'] ?? null,
                'type' => $type,
                'quantity' => round($delta, 3),
                'balance_after' => $balance,
                'reason' => $context['reason'] ?? null,
                'reference' => $context['reference'] ?? null,
                'note' => $context['note'] ?? null,
            ]);
        });
    }

    /**
     * The stock row for a product in a warehouse, created on first touch.
     *
     * New rows inherit the product's reorder level so a freshly stocked
     * warehouse warns at a sensible threshold instead of at zero.
     */
    public function itemFor(Product $product, int $warehouseId, bool $lock = false): InventoryItem
    {
        $query = InventoryItem::where('product_id', $product->id)->where('warehouse_id', $warehouseId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first() ?? InventoryItem::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => 0,
            'reserved_quantity' => 0,
            // Coalesced because a Product built in memory carries null here
            // until it is read back — passing that through would override the
            // column default with a NOT NULL violation.
            'reorder_level' => (int) ($product->reorder_level ?? 0),
        ]);
    }

    /**
     * Brings the ledger in line with the order's current status.
     *
     * Called after every save. What stock *should* be doing is read from the
     * status; what it *is* doing is read from the two markers on the order, so
     * saving the same order twice is a no-op rather than a double deduction.
     */
    public function syncOrderStock(Order $order, ?User $actor = null): void
    {
        $shouldReserve = in_array($order->status, Order::RESERVING_STATUSES, true);
        $shouldDeduct = in_array($order->status, Order::DEDUCTING_STATUSES, true);

        // Nothing can move without somewhere to move it from.
        if (! $order->warehouse_id) {
            return;
        }

        if ($order->stock_deducted_at && ! $shouldDeduct) {
            $this->reverseDeduction($order, $actor);
        }

        if ($order->stock_reserved_at && ! $shouldReserve) {
            $this->releaseReservation($order);
        }

        if ($shouldReserve && ! $order->stock_reserved_at) {
            $this->placeReservation($order);
        }

        if ($shouldDeduct && ! $order->stock_deducted_at) {
            $this->applyDeduction($order, $actor);
        }
    }

    /**
     * Rolls back every stock effect the order currently holds.
     *
     * Used before an order's lines are rewritten or the order is deleted:
     * the applied effects belong to the *old* lines, so they are unwound while
     * those lines are still there and re-applied afterwards from the new ones.
     */
    public function unwindOrderStock(Order $order, ?User $actor = null): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        if ($order->stock_deducted_at) {
            $this->reverseDeduction($order, $actor);
        }

        if ($order->stock_reserved_at) {
            $this->releaseReservation($order);
        }
    }

    private function placeReservation(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->stockableLines($order) as [$product, $quantity]) {
                $item = $this->itemFor($product, $order->warehouse_id, lock: true);
                $item->update([
                    'reserved_quantity' => round((float) $item->reserved_quantity + $quantity, 3),
                ]);
            }

            $order->forceFill(['stock_reserved_at' => now()])->save();
        });
    }

    private function releaseReservation(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->stockableLines($order) as [$product, $quantity]) {
                $item = $this->itemFor($product, $order->warehouse_id, lock: true);
                $item->update([
                    // Clamped at zero: a reservation released twice through some
                    // path we have not thought of should not invent free stock.
                    'reserved_quantity' => max(0, round((float) $item->reserved_quantity - $quantity, 3)),
                ]);
            }

            $order->forceFill(['stock_reserved_at' => null])->save();
        });
    }

    private function applyDeduction(Order $order, ?User $actor): void
    {
        DB::transaction(function () use ($order, $actor) {
            foreach ($this->stockableLines($order) as [$product, $quantity]) {
                $this->record($product, $order->warehouse_id, 'out', $quantity, [
                    'reason' => 'order_shipment',
                    'reference' => $order->order_number,
                    'user_id' => $actor?->id,
                ]);
            }

            $order->forceFill(['stock_deducted_at' => now()])->save();
        });
    }

    private function reverseDeduction(Order $order, ?User $actor): void
    {
        DB::transaction(function () use ($order, $actor) {
            foreach ($this->stockableLines($order) as [$product, $quantity]) {
                $this->record($product, $order->warehouse_id, 'in', $quantity, [
                    'reason' => 'order_reversal',
                    'reference' => $order->order_number,
                    'user_id' => $actor?->id,
                ]);
            }

            $order->forceFill(['stock_deducted_at' => null])->save();
        });
    }

    /**
     * Order lines that actually move stock, as [Product, quantity] pairs.
     *
     * Lines without a product — freight, handling, a one-off fabrication — are
     * priced on the order but hold no inventory, so they are skipped.
     *
     * @return list<array{0: Product, 1: float}>
     */
    private function stockableLines(Order $order): array
    {
        return $order->items()
            ->whereNotNull('product_id')
            ->with('product')
            ->get()
            ->filter(fn ($item) => $item->product !== null && (float) $item->quantity > 0)
            ->map(fn ($item) => [$item->product, (float) $item->quantity])
            ->values()
            ->all();
    }
}
