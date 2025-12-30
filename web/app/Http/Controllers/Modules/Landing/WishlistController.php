<?php

namespace App\Http\Controllers\Modules\Landing;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\WishlistRequest;
use App\Models\Product;
use App\Services\Landing\WishlistService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private WishlistService $wishlistService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $items = $this->wishlistService->getByUserId($userId);

            return $this->successResponse($items);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(WishlistRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->wishlistService->addItem($userId, $request->validated()['product_id']);

            return $this->createdResponse(null, 'added_to_wishlist');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->wishlistService->removeItem($userId, $product->id);

            return $this->deletedResponse('removed_from_wishlist');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}



