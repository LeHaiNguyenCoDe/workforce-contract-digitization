<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CartRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    /**
     * Get cart with items
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $cartData = $this->cartService->getCart($request);

            return response()->json([
                'status' => 'success',
                'data' => $cartData,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(CartRequest $request): JsonResponse
    {
        try {
            $this->cartService->addItem($request, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Item added to cart',
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
     * Update cart item quantity
     */
    public function updateItem(CartItem $item, CartRequest $request): JsonResponse
    {
        try {
            $this->cartService->updateItem($item, $request->validated()['qty']);

            return response()->json([
                'status' => 'success',
                'message' => 'Cart item updated',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $item): JsonResponse
    {
        try {
            $this->cartService->removeItem($item);

            return response()->json([
                'status' => 'success',
                'message' => 'Cart item removed',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}


