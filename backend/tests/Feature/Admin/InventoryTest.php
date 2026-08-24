<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryTest extends TestCase
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
            'reorder_level' => 500,
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create(['name' => 'Brisbane', 'code' => 'BNE']);
    }

    public function test_first_movement_creates_the_stock_line_and_inherits_the_reorder_level(): void
    {
        $this->assertDatabaseCount('inventory_items', 0);

        $this->adjust('in', 1200, ['reason' => 'purchase', 'reference' => 'PO-1001'])
            ->assertOk()
            ->assertJsonPath('data.quantity', 1200)
            ->assertJsonPath('data.available', 1200)
            ->assertJsonPath('data.reorder_level', 500)
            ->assertJsonPath('data.stock_status', 'in_stock');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 1200,
            'balance_after' => 1200,
            'reference' => 'PO-1001',
        ]);
    }

    public function test_every_balance_change_leaves_a_ledger_entry_that_sums_back_to_the_balance(): void
    {
        $this->adjust('in', 1000)->assertOk();
        $this->adjust('out', 250)->assertOk();
        $this->adjust('adjustment', -50, ['reason' => 'damage'])->assertOk();

        $item = InventoryItem::first();
        $this->assertSame(700.0, (float) $item->quantity);
        // The cached balance and the ledger must agree — that is the whole
        // contract of InventoryService.
        $this->assertSame(700.0, (float) StockMovement::sum('quantity'));
    }

    public function test_stock_cannot_be_taken_below_zero(): void
    {
        $this->adjust('in', 100)->assertOk();

        $this->adjust('out', 150)
            ->assertStatus(422)
            ->assertJsonValidationErrors('quantity');

        $this->assertSame(100.0, (float) InventoryItem::first()->quantity);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_a_zero_quantity_movement_is_rejected(): void
    {
        $this->adjust('in', 0)->assertStatus(422)->assertJsonValidationErrors('quantity');
    }

    public function test_low_stock_filter_reads_available_not_on_hand(): void
    {
        $this->adjust('in', 400)->assertOk();

        $this->getJson('/api/v1/admin/inventory?low_stock=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stock_status', 'low_stock');

        $this->adjust('in', 5000)->assertOk();

        $this->getJson('/api/v1/admin/inventory?low_stock=1')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_stock_line_settings_are_editable_but_quantities_are_not(): void
    {
        $this->adjust('in', 100)->assertOk();
        $item = InventoryItem::first();

        $this->putJson("/api/v1/admin/inventory/{$item->id}", [
            'reorder_level' => 20,
            'bin_location' => 'B12-03',
            'quantity' => 99999,
        ])->assertOk()->assertJsonPath('data.bin_location', 'B12-03');

        // The quantity in the payload is ignored — only a movement moves stock.
        $this->assertSame(100.0, (float) $item->fresh()->quantity);
    }

    public function test_movements_endpoint_lists_the_ledger_newest_first(): void
    {
        $this->adjust('in', 100)->assertOk();
        $this->adjust('out', 40)->assertOk();

        $this->getJson('/api/v1/admin/stock-movements')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.quantity', -40)
            ->assertJsonPath('data.1.quantity', 100);
    }

    private function adjust(string $type, float $quantity, array $extra = [])
    {
        return $this->postJson('/api/v1/admin/inventory/adjust', array_merge([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => $type,
            'quantity' => $quantity,
        ], $extra));
    }
}
