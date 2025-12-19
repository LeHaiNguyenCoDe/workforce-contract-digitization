<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected function resolveCart(Request $request): Cart
    {
        $user = Auth::user();

        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        $sessionId = $request->session()->getId();

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function show(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request)->load('items.product');

        $items = $cart->items->map(function (CartItem $item) {
            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'thumbnail' => $item->product->thumbnail,
                ],
                'qty' => $item->qty,
                'price' => $item->price,
                'total' => $item->qty * $item->price,
            ];
        });

        $total = $items->sum('total');

        return response()->json([
            'data' => [
                'items' => $items,
                'total' => $total,
            ],
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->resolveCart($request);

        $product = Product::findOrFail($validated['product_id']);
        $variantId = $validated['variant_id'] ?? null;

        $item = $cart->items()->firstOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
        ], [
            'qty' => 0,
            'price' => $product->price,
        ]);

        $item->qty += $validated['qty'];
        $item->save();

        return response()->json([
            'message' => 'Item added to cart',
        ], 201);
    }

    public function updateItem(CartItem $item, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $item->update(['qty' => $validated['qty']]);

        return response()->json([
            'message' => 'Cart item updated',
        ]);
    }

    public function removeItem(CartItem $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'message' => 'Cart item removed',
        ]);
    }
}


