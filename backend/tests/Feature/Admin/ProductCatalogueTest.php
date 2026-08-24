<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_create_list_update_and_delete_a_product(): void
    {
        $category = ProductCategory::create(['name' => 'Steel Wire', 'slug' => 'steel-wire']);

        $create = $this->postJson('/api/v1/admin/products', [
            'product_category_id' => $category->id,
            'sku' => 'GALV-2.50',
            'name' => 'Galvanised Wire 2.50mm',
            'unit' => 'kg',
            'unit_price' => 1.35,
            'status' => 'active',
        ]);

        $create->assertCreated();
        $id = $create->json('data.id');
        $this->assertDatabaseHas('products', ['id' => $id, 'slug' => 'galvanised-wire-250mm']);

        $this->getJson('/api/v1/admin/products?search=GALV')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'GALV-2.50');

        $this->putJson("/api/v1/admin/products/{$id}", [
            'sku' => 'GALV-2.50',
            'name' => 'Galvanised Wire 2.50mm Class 3',
            'unit' => 'kg',
            'unit_price' => 1.42,
            'status' => 'discontinued',
        ])->assertOk()->assertJsonPath('data.status', 'discontinued');

        $this->deleteJson("/api/v1/admin/products/{$id}")->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    public function test_sku_must_be_unique_across_products(): void
    {
        $this->product('DUP-1');

        $this->postJson('/api/v1/admin/products', [
            'sku' => 'DUP-1',
            'name' => 'Something else',
            'unit' => 'kg',
            'unit_price' => 1,
            'status' => 'active',
        ])->assertStatus(422)->assertJsonValidationErrors('sku');
    }

    public function test_product_list_reports_stock_summed_across_warehouses(): void
    {
        $product = $this->product('SUM-1');
        $first = Warehouse::create(['name' => 'One', 'code' => 'ONE']);
        $second = Warehouse::create(['name' => 'Two', 'code' => 'TWO']);

        foreach ([$first, $second] as $warehouse) {
            $this->postJson('/api/v1/admin/inventory/adjust', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => 500,
            ])->assertOk();
        }

        $this->getJson('/api/v1/admin/products?search=SUM-1')
            ->assertOk()
            ->assertJsonPath('data.0.stock_on_hand', 1000)
            ->assertJsonPath('data.0.stock_available', 1000);
    }

    public function test_deleting_a_category_leaves_its_products_uncategorised(): void
    {
        $category = ProductCategory::create(['name' => 'Temporary', 'slug' => 'temporary']);
        $product = $this->product('ORPHAN-1', ['product_category_id' => $category->id]);

        $this->deleteJson("/api/v1/admin/product-categories/{$category->id}")->assertOk();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'product_category_id' => null]);
    }

    public function test_warehouse_with_stock_history_cannot_be_deleted(): void
    {
        $product = $this->product('KEEP-1');
        $warehouse = Warehouse::create(['name' => 'Brisbane', 'code' => 'BNE']);

        $this->postJson('/api/v1/admin/inventory/adjust', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 10,
        ])->assertOk();

        $this->deleteJson("/api/v1/admin/warehouses/{$warehouse->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors('warehouse');

        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id]);
    }

    private function product(string $sku, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'sku' => $sku,
            'name' => $sku,
            'slug' => strtolower($sku),
            'unit' => 'kg',
            'unit_price' => 1,
            'status' => 'active',
        ], $attributes));
    }
}
