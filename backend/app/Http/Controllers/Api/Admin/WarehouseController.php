<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WarehouseController extends Controller
{
    public function index()
    {
        return WarehouseResource::collection(
            Warehouse::query()->withCount('inventoryItems')->orderBy('name')->get()
        );
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse->loadCount('inventoryItems'));
    }

    public function store(Request $request): WarehouseResource
    {
        return new WarehouseResource(Warehouse::create($this->validated($request)));
    }

    public function update(Request $request, Warehouse $warehouse): WarehouseResource
    {
        $warehouse->update($this->validated($request, $warehouse));

        return new WarehouseResource($warehouse->loadCount('inventoryItems'));
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        // Deleting cascades to inventory_items and stock_movements, so a
        // warehouse that has ever held stock is refused rather than silently
        // taking its history with it. Deactivating is the intended retirement
        // path — it hides the warehouse from pickers but keeps the ledger.
        if ($warehouse->stockMovements()->exists()) {
            throw ValidationException::withMessages([
                'warehouse' => 'This warehouse has stock history and cannot be deleted. Mark it inactive instead.',
            ]);
        }

        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted.']);
    }

    private function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:16', Rule::unique('warehouses', 'code')->ignore($warehouse?->id)],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
