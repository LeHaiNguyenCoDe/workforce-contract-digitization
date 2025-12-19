<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $promotions = Promotion::query()
            ->with('items')
            ->orderByDesc('starts_at')
            ->paginate($request->query('per_page', 10));

        return response()->json($promotions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:promotions,code'],
            'type' => ['required', 'in:percent,fixed_amount'],
            'value' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $promotion = Promotion::create($validated);

        return response()->json([
            'message' => 'Promotion created',
            'data' => $promotion,
        ], 201);
    }

    public function show(Promotion $promotion): JsonResponse
    {
        $promotion->load('items');

        return response()->json(['data' => $promotion]);
    }

    public function update(Promotion $promotion, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:promotions,code,'.$promotion->id],
            'type' => ['sometimes', 'in:percent,fixed_amount'],
            'value' => ['sometimes', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $promotion->update($validated);

        return response()->json([
            'message' => 'Promotion updated',
            'data' => $promotion,
        ]);
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        $promotion->delete();

        return response()->json([
            'message' => 'Promotion deleted',
        ]);
    }
}


