<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\WishlistRequest;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService
    ) {
    }

    /**
     * Get user wishlist
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $items = $this->wishlistService->getByUserId($userId);

            return response()->json([
                'status' => 'success',
                'data' => $items,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Add product to wishlist
     */
    public function store(WishlistRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->wishlistService->addItem($userId, $request->validated()['product_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Added to wishlist',
            ], 201);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->wishlistService->removeItem($userId, $product->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Removed from wishlist',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}


