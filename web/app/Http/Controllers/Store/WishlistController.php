<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $items = WishlistItem::with('product:id,name,price,thumbnail')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = Auth::user();

        $product = Product::findOrFail($validated['product_id']);

        WishlistItem::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Added to wishlist',
        ], 201);
    }

    public function destroy(Product $product): JsonResponse
    {
        $user = Auth::user();

        WishlistItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json([
            'message' => 'Removed from wishlist',
        ]);
    }
}


