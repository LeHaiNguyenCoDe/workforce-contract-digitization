<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\PromotionStoreRequest;
use App\Http\Requests\Modules\Admin\PromotionUpdateRequest;
use App\Http\Requests\Modules\Admin\PromotionItemRequest;
use App\Models\Promotion;
use App\Services\Admin\PromotionService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private PromotionService $promotionService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $promotions = $this->promotionService->getAll($perPage);

            return $this->successResponse($promotions);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(PromotionStoreRequest $request): JsonResponse
    {
        try {
            $promotion = $this->promotionService->create($request->validated());

            return $this->createdResponse($promotion, 'promotion_created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function show(Promotion $promotion): JsonResponse
    {
        try {
            $promotionData = $this->promotionService->getById($promotion->id);

            return $this->successResponse($promotionData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('promotion_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function update(Promotion $promotion, PromotionUpdateRequest $request): JsonResponse
    {
        try {
            $promotionData = $this->promotionService->update($promotion->id, $request->validated());

            return $this->updatedResponse($promotionData, 'promotion_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('promotion_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return $this->notFoundResponse('promotion_not_found');
            }

            $this->promotionService->delete($promotion->id);

            return $this->deletedResponse('promotion_deleted');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('promotion_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function addItem(Promotion $promotion, PromotionItemRequest $request): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return $this->notFoundResponse('promotion_not_found');
            }

            $validated = $request->validated();

            if (!isset($validated['product_id']) && !isset($validated['category_id'])) {
                return $this->validationErrorResponse(['item' => 'promotion_item_required']);
            }

            $item = $this->promotionService->addItem($promotion->id, $validated);

            return $this->createdResponse($item, 'promotion_item_added');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('promotion_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function removeItem(Promotion $promotion, int $item): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return $this->notFoundResponse('promotion_not_found');
            }

            $this->promotionService->removeItem($promotion->id, $item);

            return $this->deletedResponse('promotion_item_removed');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('promotion_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


