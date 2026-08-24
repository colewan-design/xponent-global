<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->admin()->create());

        $this->product = Product::create([
            'sku' => 'GALV-2.50',
            'name' => 'Galvanised Wire 2.50mm',
            'slug' => 'galv-250',
            'unit' => 'kg',
            'unit_price' => 1.35,
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create(['name' => 'Brisbane', 'code' => 'BNE']);

        app(InventoryService::class)->record($this->product, $this->warehouse->id, 'in', 10000, [
            'reason' => 'initial',
        ]);
    }

    public function test_order_number_is_generated_and_totals_are_computed_server_side(): void
    {
        $response = $this->createOrder(['tax_rate' => 10, 'shipping_total' => 100]);

        $response->assertCreated()
            ->assertJsonPath('data.order_number', 'XG-'.now()->format('Y').'-0001')
            ->assertJsonPath('data.subtotal', 1350)   // 1000 × 1.35
            ->assertJsonPath('data.tax_total', 135)
            ->assertJsonPath('data.total', 1585);

        // A second order continues the sequence.
        $this->createOrder()->assertCreated()
            ->assertJsonPath('data.order_number', 'XG-'.now()->format('Y').'-0002');
    }

    public function test_line_snapshots_come_from_the_product_not_the_client(): void
    {
        $this->createOrder([
            'items' => [[
                'product_id' => $this->product->id,
                'sku' => 'CLIENT-MADE-UP',
                'name' => 'Whatever the client typed',
                'unit_price' => 1.35,
                'quantity' => 10,
            ]],
        ])->assertCreated();

        $this->assertDatabaseHas('order_items', [
            'sku' => 'GALV-2.50',
            'name' => 'Galvanised Wire 2.50mm',
        ]);
    }

    public function test_a_pending_order_does_not_touch_stock(): void
    {
        $this->createOrder(['status' => 'pending'])->assertCreated();

        $item = InventoryItem::first();
        $this->assertSame(10000.0, (float) $item->quantity);
        $this->assertSame(0.0, (float) $item->reserved_quantity);
    }

    public function test_confirming_reserves_shipping_deducts_and_cancelling_gives_the_stock_back(): void
    {
        $id = $this->createOrder(['status' => 'pending'])->json('data.id');

        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'confirmed'])->assertOk();
        $item = InventoryItem::first();
        $this->assertSame(10000.0, (float) $item->quantity, 'reserving must not move stock off the shelf');
        $this->assertSame(1000.0, (float) $item->reserved_quantity);
        $this->assertSame(9000.0, $item->available);

        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'shipped'])->assertOk();
        $item = $item->fresh();
        $this->assertSame(9000.0, (float) $item->quantity);
        $this->assertSame(0.0, (float) $item->reserved_quantity, 'shipping releases the reservation it consumed');

        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'cancelled'])->assertOk();
        $item = $item->fresh();
        $this->assertSame(10000.0, (float) $item->quantity);
        $this->assertSame(0.0, (float) $item->reserved_quantity);

        // Opening balance, the shipment, and the reversal — the ledger explains
        // the round trip rather than quietly rewriting it.
        $this->assertSame(3, StockMovement::count());
    }

    public function test_saving_the_same_status_twice_does_not_deduct_twice(): void
    {
        $id = $this->createOrder(['status' => 'shipped'])->json('data.id');

        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'shipped'])->assertOk();
        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['payment_status' => 'paid'])->assertOk();

        $this->assertSame(9000.0, (float) InventoryItem::first()->quantity);
        $this->assertSame(2, StockMovement::count());
    }

    public function test_editing_the_lines_of_a_reserved_order_re_reserves_the_new_quantity(): void
    {
        $id = $this->createOrder(['status' => 'confirmed'])->json('data.id');
        $this->assertSame(1000.0, (float) InventoryItem::first()->reserved_quantity);

        $this->putJson("/api/v1/admin/orders/{$id}", $this->payload([
            'status' => 'confirmed',
            'items' => [[
                'product_id' => $this->product->id,
                'unit_price' => 1.35,
                'quantity' => 2500,
            ]],
        ]))->assertOk()->assertJsonPath('data.subtotal', 3375);

        $this->assertSame(2500.0, (float) InventoryItem::first()->reserved_quantity);
    }

    public function test_deleting_an_order_returns_the_stock_it_holds(): void
    {
        $id = $this->createOrder(['status' => 'shipped'])->json('data.id');
        $this->assertSame(9000.0, (float) InventoryItem::first()->quantity);

        $this->deleteJson("/api/v1/admin/orders/{$id}")->assertOk();

        $this->assertSame(10000.0, (float) InventoryItem::first()->quantity);
        $this->assertDatabaseMissing('orders', ['id' => $id]);
    }

    public function test_an_order_cannot_move_stock_without_a_warehouse(): void
    {
        $this->createOrder(['status' => 'confirmed', 'warehouse_id' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('warehouse_id');

        $id = $this->createOrder(['status' => 'pending', 'warehouse_id' => null])->json('data.id');

        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'shipped'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('warehouse_id');
    }

    public function test_an_order_cannot_ship_more_than_is_on_the_shelf(): void
    {
        $id = $this->createOrder([
            'status' => 'pending',
            'items' => [[
                'product_id' => $this->product->id,
                'unit_price' => 1.35,
                'quantity' => 25000,
            ]],
        ])->json('data.id');

        // Over-committing is allowed — that is a backorder, and available stock
        // goes negative to show it.
        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'confirmed'])->assertOk();
        $this->assertSame(-15000.0, InventoryItem::first()->available);

        // Despatching goods that do not exist is not.
        $this->patchJson("/api/v1/admin/orders/{$id}/status", ['status' => 'shipped'])->assertStatus(422);
        $this->assertSame(10000.0, (float) InventoryItem::first()->quantity);
        $this->assertNull(Order::find($id)->stock_deducted_at);
    }

    public function test_lines_without_a_product_are_priced_but_hold_no_stock(): void
    {
        $this->createOrder([
            'status' => 'confirmed',
            'items' => [
                ['product_id' => $this->product->id, 'unit_price' => 1.35, 'quantity' => 100],
                ['name' => 'Freight to site', 'unit_price' => 450, 'quantity' => 1],
            ],
        ])->assertCreated()->assertJsonPath('data.subtotal', 585);

        $this->assertSame(100.0, (float) InventoryItem::first()->reserved_quantity);
        $this->assertDatabaseCount('inventory_items', 1);
    }

    public function test_a_line_with_neither_product_nor_name_is_rejected(): void
    {
        $this->createOrder([
            'items' => [['unit_price' => 10, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('items.0.name');
    }

    private function createOrder(array $overrides = [])
    {
        return $this->postJson('/api/v1/admin/orders', $this->payload($overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Sunstate Fencing Supplies',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'items' => [[
                'product_id' => $this->product->id,
                'unit_price' => 1.35,
                'quantity' => 1000,
            ]],
        ], $overrides);
    }
}
