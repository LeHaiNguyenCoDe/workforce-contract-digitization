<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::query()
            ->select('id', 'name', 'code', 'address', 'is_active')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $warehouses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'message' => 'Warehouse created',
            'data' => $warehouse,
        ], 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json(['data' => $warehouse]);
    }

    public function update(Warehouse $warehouse, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:warehouses,code,'.$warehouse->id],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $warehouse->update($validated);

        return response()->json([
            'message' => 'Warehouse updated',
            'data' => $warehouse,
        ]);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted',
        ]);
    }

    /**
     * Xem tồn kho theo kho.
     */
    public function stocks(Warehouse $warehouse): JsonResponse
    {
        $stocks = Stock::with('product:id,name,slug')
            ->where('warehouse_id', $warehouse->id)
            ->get();

        return response()->json(['data' => $stocks]);
    }
}


