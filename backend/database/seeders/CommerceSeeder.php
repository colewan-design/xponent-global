<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

    /** @return array<string, Product> keyed by SKU */
    private function seedProducts(): array
    {
        $catalogue = [
            'Steel Wire Products' => [
                ['BAW-2.00', 'Black Annealed Wire 2.00mm', '2.00mm dia, soft annealed, 1000kg coil', 'kg', 1.05, 0.002, 4000],
                ['BAW-3.15', 'Black Annealed Wire 3.15mm', '3.15mm dia, soft annealed, 1000kg coil', 'kg', 1.02, 0.006, 4000],
                ['GALV-2.50', 'Galvanised Wire 2.50mm Class 3', '2.50mm dia, Zn 240g/m², Class 3 heavy coating', 'kg', 1.35, 0.004, 6000],
                ['HGW-4.00', 'Heavy Galvanised Wire 4.00mm', '4.00mm dia, Zn 275g/m², marine grade', 'kg', 1.48, 0.010, 3000],
                ['PVC-2.80', 'PVC Coated Wire 2.80/3.60mm', '2.80mm core / 3.60mm coated, green PVC', 'kg', 1.72, 0.008, 2500],
                ['HTF-2.50', 'High Tensile Fence Wire 2.50mm', '2.50mm dia, 1200–1400 MPa, 25kg coil', 'kg', 1.28, 0.004, 5000],
                ['NW-3.15', 'Nail Wire 3.15mm', '3.15mm dia, bright drawn, nail-making grade', 'tonne', 940.00, 1000.000, 8],
                ['TIE-1.60', 'Binding / Tie Wire 1.60mm', '1.60mm dia, annealed, 25kg coil', 'kg', 1.18, 0.002, 3000],
            ],
            'Wire Mesh, Gabions and Fencing' => [
                ['GAB-211-PVC', 'Gabion Basket 2×1×1m PVC', '80×100mm mesh, 2.7mm PVC coated wire', 'piece', 46.50, 24.500, 150],
                ['MAT-620-030', 'Reno Mattress 6×2×0.3m', '60×80mm mesh, galvanised 2.2mm', 'piece', 118.00, 41.000, 60],
                ['GM-50X50', 'Grill Mesh Panel 50×50mm', '2400×1200mm panel, 4.0mm galvanised', 'piece', 32.00, 14.200, 200],
                ['PAM-13-900', 'Poultry Mesh 13mm × 900mm × 30m', '13mm hexagonal, 0.7mm galvanised, 30m roll', 'roll', 58.00, 12.800, 120],
            ],
            'Fencing Accessories' => [
                ['BW-IOWA-200', 'Barbed Wire IOWA 2.50mm × 200m', '2.50mm × 2.00mm, 4-point, 75mm spacing', 'roll', 39.50, 21.000, 180],
                ['CLIP-SPR-30', 'Gabion Spiral Clip 3.00mm', '3.00mm galvanised spiral binder, 750mm', 'piece', 2.35, 0.180, 800],
            ],
        ];

        $products = [];
        $categorySort = 1;

        foreach ($catalogue as $categoryName => $rows) {
            $category = ProductCategory::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'sort_order' => $categorySort++,
            ]);

            foreach ($rows as [$sku, $name, $specification, $unit, $price, $weight, $reorderLevel]) {
                $products[$sku] = Product::create([
                    'product_category_id' => $category->id,
                    'sku' => $sku,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'specification' => $specification,
                    'unit' => $unit,
                    'unit_price' => $price,
                    'currency' => 'USD',
                    'weight_kg' => $weight,
                    'reorder_level' => $reorderLevel,
                    'status' => 'active',
                ]);
            }
        }

        return $products;
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
