<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The product catalogue: categories and SKUs, and nothing else.
 *
 * Split out of CommerceSeeder so the catalogue can be installed on a live site
 * without the rest of it. CommerceSeeder also opens stock balances and writes
 * example orders — demonstration data that belongs in a development database and
 * emphatically not in a production one, where a fabricated order sitting in the
 * admin is indistinguishable from a real one until someone tries to ship it.
 *
 * Idempotent, keyed on slug and SKU, so it is safe to re-run and safe to run
 * against a database that already holds part of the catalogue. Re-running
 * restores the seeded values for these rows: fields edited in the admin are
 * overwritten, rows added there are left alone.
 *
 * CommerceSeeder calls this rather than keeping its own copy — one catalogue,
 * one definition.
 */
class ProductCatalogueSeeder extends Seeder
{
    /**
     * [sku, name, specification, unit, unit_price, weight_kg, reorder_level]
     *
     * Prices and reorder levels are internal: PublicProductResource does not
     * expose them, so they exist here for the admin's stock and quoting views.
     */
    public const CATALOGUE = [
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

    /**
     * @return array<string, Product> the seeded products, keyed by SKU, so
     *                                CommerceSeeder can open stock against them
     */
    public function seedCatalogue(): array
    {
        $products = [];
        $categorySort = 1;

        foreach (self::CATALOGUE as $categoryName => $rows) {
            $category = ProductCategory::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'sort_order' => $categorySort++],
            );

            foreach ($rows as [$sku, $name, $specification, $unit, $price, $weight, $reorderLevel]) {
                $products[$sku] = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_category_id' => $category->id,
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'specification' => $specification,
                        'unit' => $unit,
                        'unit_price' => $price,
                        'currency' => 'USD',
                        'weight_kg' => $weight,
                        'reorder_level' => $reorderLevel,
                        'status' => 'active',
                    ],
                );
            }
        }

        return $products;
    }

    public function run(): void
    {
        $this->seedCatalogue();
    }
}
