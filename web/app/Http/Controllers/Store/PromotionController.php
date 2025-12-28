<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\PromotionStoreRequest;
use App\Http\Requests\Store\PromotionUpdateRequest;
use App\Http\Requests\Store\PromotionItemRequest;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(
        private PromotionService $promotionService
    ) {
    }

    /**
     * Get all promotions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $promotions = $this->promotionService->getAll($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $promotions,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Create promotion
     */
    public function store(PromotionStoreRequest $request): JsonResponse
    {
        try {
            $promotion = $this->promotionService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Promotion created',
                'data' => $promotion,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get promotion details
     */
    public function show(Promotion $promotion): JsonResponse
    {
        try {
            $promotionData = $this->promotionService->getById($promotion->id);

            return response()->json([
                'status' => 'success',
                'data' => $promotionData,
            ]);
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
     * Update promotion
     */
    public function update(Promotion $promotion, PromotionUpdateRequest $request): JsonResponse
    {
        try {
            $promotionData = $this->promotionService->update($promotion->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Promotion updated',
                'data' => $promotionData,
            ]);
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
     * Delete promotion
     */
    public function destroy(Promotion $promotion): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promotion not found',
                ], 404);
            }

            $this->promotionService->delete($promotion->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Promotion deleted',
            ]);
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
     * Add item to promotion
     */
    public function addItem(Promotion $promotion, PromotionItemRequest $request): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promotion not found',
                ], 404);
            }

            $validated = $request->validated();

            if (!isset($validated['product_id']) && !isset($validated['category_id'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Either product_id or category_id is required',
                ], 422);
            }

            $item = $this->promotionService->addItem($promotion->id, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Item added to promotion',
                'data' => $item,
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
     * Remove item from promotion
     */
    public function removeItem(Promotion $promotion, int $item): JsonResponse
    {
        try {
            if (!$promotion || !$promotion->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promotion not found',
                ], 404);
            }

            $this->promotionService->removeItem($promotion->id, $item);

            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from promotion',
            ]);
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
}


