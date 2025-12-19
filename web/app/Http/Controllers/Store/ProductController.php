<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query
            ->select('id', 'category_id', 'name', 'slug', 'price', 'thumbnail', 'short_description')
            ->paginate($request->query('per_page', 12));

        return response()->json($products);
    }

    public function byCategory(Category $category, Request $request): JsonResponse
    {
        $products = $category->products()
            ->select('id', 'category_id', 'name', 'slug', 'price', 'thumbnail', 'short_description')
            ->paginate($request->query('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images', 'variants', 'reviews' => function ($q) {
            $q->latest()->limit(10);
        }]);

        $avgRating = $product->reviews()->avg('rating');
        $countRating = $product->reviews()->count();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'thumbnail' => $product->thumbnail,
                'specs' => $product->specs,
                'category' => $product->category,
                'images' => $product->images,
                'variants' => $product->variants,
                'rating' => [
                    'avg' => $avgRating,
                    'count' => $countRating,
                ],
                'latest_reviews' => $product->reviews,
            ],
        ]);
    }
}


