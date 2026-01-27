<?php

namespace App\Http\Controllers\Modules\Landing;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Services\Landing\CartService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private CartService $cartService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $items = $this->cartService->getCart($request);

            return $this->successResponse($items);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function addItem(Request $request): JsonResponse
    {
        try {
            $this->cartService->addItem($request, [
                'product_id' => $request->product_id,
                'qty' => $request->quantity ?? 1,
                'variant_id' => $request->variant_id
            ]);

            return $this->createdResponse(null, 'item_added');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        try {
            $item = \App\Models\CartItem::findOrFail($itemId);
            $this->cartService->updateItem($item, $request->quantity);

            return $this->updatedResponse(null, 'item_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function removeItem(int $itemId): JsonResponse
    {
        try {
            $item = \App\Models\CartItem::findOrFail($itemId);
            $this->cartService->removeItem($item);

            return $this->deletedResponse('item_removed');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            $this->cartService->clearCart($request);

            return $this->deletedResponse('cart_cleared');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}



