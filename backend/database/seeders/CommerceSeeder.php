<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

/**
 * Products, warehouses, opening stock and a few orders.
 *
 * The catalogue mirrors the wire lines already in the solutions content, but as
 * priced, stocked SKUs — the point of the seed is that Products, Inventory and
 * Orders each open with something in them and with the stock ledger already
 * showing how the balances got there.
 */
class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $inventory = app(InventoryService::class);
        $actor = User::where('role', 'admin')->first();

        $warehouses = $this->seedWarehouses();
        $products = $this->seedProducts();

        $this->seedOpeningStock($inventory, $products, $warehouses, $actor);
        $this->seedOrders($inventory, $products, $warehouses, $actor);
    }

    /** @return array<string, Warehouse> keyed by code */
    private function seedWarehouses(): array
    {
        $rows = [
            ['name' => 'Brisbane Distribution Centre', 'code' => 'BNE', 'address' => '17 Freight Street', 'city' => 'Queensland 4160', 'country' => 'Australia'],
            ['name' => 'Manila Warehouse', 'code' => 'MNL', 'address' => 'Bonifacio Global City', 'city' => 'Taguig City, Metro Manila 1630', 'country' => 'Philippines'],
            ['name' => 'Hong Kong Transit Store', 'code' => 'HKG', 'address' => 'Kwun Tong', 'city' => 'Kowloon', 'country' => 'Hong Kong'],
        ];

        $warehouses = [];

        foreach ($rows as $row) {
            $warehouses[$row['code']] = Warehouse::create($row + ['is_active' => true]);
        }

        return $warehouses;
    }

    /**
     * The catalogue itself lives in ProductCatalogueSeeder, which is safe to run
     * on its own against a live site. This seeder is the development superset:
     * the same catalogue, plus the stock and orders that make the admin's
     * Inventory and Orders screens open with something in them.
     *
     * @return array<string, Product> keyed by SKU
     */
    private function seedProducts(): array
    {
        return (new ProductCatalogueSeeder)->seedCatalogue();
    }

    /**
     * Opening balances, posted as real `initial` movements rather than written
     * straight onto the balances — the ledger should explain every kilogram in
     * the warehouse, including the first one.
     *
     * @param  array<string, Product>  $products
     * @param  array<string, Warehouse>  $warehouses
     */
    private function seedOpeningStock(InventoryService $inventory, array $products, array $warehouses, ?User $actor): void
    {
        $opening = [
            'BNE' => ['BAW-2.00' => 12500, 'BAW-3.15' => 9800, 'GALV-2.50' => 21400, 'HGW-4.00' => 7600, 'PVC-2.80' => 3100, 'HTF-2.50' => 18200, 'NW-3.15' => 26, 'TIE-1.60' => 5400, 'GAB-211-PVC' => 420, 'MAT-620-030' => 95, 'BW-IOWA-200' => 340, 'CLIP-SPR-30' => 2600],
            'MNL' => ['GALV-2.50' => 8600, 'HGW-4.00' => 2400, 'HTF-2.50' => 4100, 'TIE-1.60' => 1900, 'GAB-211-PVC' => 180, 'GM-50X50' => 260, 'PAM-13-900' => 140, 'BW-IOWA-200' => 150, 'CLIP-SPR-30' => 1400],
            // Deliberately thin: a transit store is meant to look like one, and
            // it gives the low-stock filter something to find on first load.
            'HKG' => ['GALV-2.50' => 1200, 'PVC-2.80' => 600, 'GM-50X50' => 40, 'PAM-13-900' => 25],
        ];

        foreach ($opening as $code => $lines) {
            foreach ($lines as $sku => $quantity) {
                $inventory->record($products[$sku], $warehouses[$code]->id, 'in', $quantity, [
                    'reason' => 'initial',
                    'reference' => 'Opening balance',
                    'user_id' => $actor?->id,
                ]);
            }
        }
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, Warehouse>  $warehouses
     */
    private function seedOrders(InventoryService $inventory, array $products, array $warehouses, ?User $actor): void
    {
        $orders = [
            [
                'customer_name' => 'Ramon Espinosa',
                'customer_company' => 'Philsaga Mining Corporation',
                'customer_email' => 'procurement@philsaga.example',
                'customer_phone' => '+63 2 8123 4567',
                'shipping_address' => "Bunawan, Agusan del Sur\nPhilippines",
                'warehouse' => 'MNL',
                'status' => 'delivered',
                'payment_status' => 'paid',
                'tax_rate' => 12,
                'shipping_total' => 640,
                'placed_at' => now()->subDays(24),
                'lines' => [['GALV-2.50', 2400, 1.35], ['TIE-1.60', 600, 1.18]],
            ],
            [
                'customer_name' => 'Deborah Hale',
                'customer_company' => 'Quest Exploration Drilling (Philippines) Inc.',
                'customer_email' => 'orders@questdrilling.example',
                'customer_phone' => '+63 2 8555 0198',
                'shipping_address' => "Km 14 Diversion Road\nDavao City 8000\nPhilippines",
                'warehouse' => 'MNL',
                'status' => 'confirmed',
                'payment_status' => 'partial',
                'tax_rate' => 12,
                'shipping_total' => 380,
                'placed_at' => now()->subDays(6),
                'lines' => [['GAB-211-PVC', 120, 46.50], ['CLIP-SPR-30', 900, 2.35]],
            ],
            [
                'customer_name' => 'Andrew Whitlock',
                'customer_company' => 'Sunstate Fencing Supplies',
                'customer_email' => 'andrew@sunstatefencing.example',
                'customer_phone' => '+61 7 3040 1188',
                'shipping_address' => "42 Beaudesert Road\nQueensland 4109\nAustralia",
                'warehouse' => 'BNE',
                'status' => 'processing',
                'payment_status' => 'unpaid',
                'tax_rate' => 10,
                'shipping_total' => 220,
                'placed_at' => now()->subDays(2),
                'lines' => [['HTF-2.50', 6000, 1.28], ['BW-IOWA-200', 80, 39.50]],
            ],
            [
                'customer_name' => 'Grace Tanaka',
                'customer_company' => 'Pacific Rim Infrastructure',
                'customer_email' => 'g.tanaka@pacrim.example',
                'shipping_address' => "Kwai Chung\nHong Kong",
                'warehouse' => 'HKG',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'tax_rate' => 0,
                'shipping_total' => 0,
                'placed_at' => now()->subDay(),
                'lines' => [['GM-50X50', 30, 32.00], ['PAM-13-900', 20, 58.00]],
            ],
        ];

        foreach ($orders as $row) {
            $order = Order::create([
                'order_number' => Order::nextOrderNumber(),
                'warehouse_id' => $warehouses[$row['warehouse']]->id,
                'customer_name' => $row['customer_name'],
                'customer_email' => $row['customer_email'] ?? null,
                'customer_phone' => $row['customer_phone'] ?? null,
                'customer_company' => $row['customer_company'] ?? null,
                'shipping_address' => $row['shipping_address'],
                'status' => $row['status'],
                'payment_status' => $row['payment_status'],
                'currency' => 'USD',
                'tax_rate' => $row['tax_rate'],
                'shipping_total' => $row['shipping_total'],
                'placed_at' => $row['placed_at'],
            ]);

            foreach ($row['lines'] as [$sku, $quantity, $unitPrice]) {
                $product = $products[$sku];

                $order->items()->create([
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => round($unitPrice * $quantity, 2),
                ]);
            }

            $order->recalculateTotals();

            // Same path the controller takes, so the seeded orders leave the
            // reservations and shipment movements a real one would.
            $inventory->syncOrderStock($order->refresh(), $actor);
        }
    }
}
