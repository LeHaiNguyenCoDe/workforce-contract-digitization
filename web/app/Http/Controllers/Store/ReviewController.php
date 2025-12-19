<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Product $product, Request $request): JsonResponse
    {
        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate($request->query('per_page', 10));

        return response()->json($reviews);
    }

    public function store(Product $product, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review created',
            'data' => $review->load('user:id,name'),
        ], 201);
    }
}


