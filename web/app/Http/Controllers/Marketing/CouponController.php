<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\CouponService;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService
    ) {
    }

    /**
     * Get all coupons
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $coupons = $this->couponService->getAll($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $coupons,
                'message' => 'Coupons retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single coupon
     */
    public function show(int $id): JsonResponse
    {
        try {
            $coupon = Coupon::with(['usages', 'creator', 'generationBatches'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $coupon,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new coupon
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:coupons',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed,bxgy,free_shipping',
                'value' => 'required|numeric|min:0',
                'min_purchase_amount' => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'usage_limit_total' => 'nullable|integer|min:0',
                'usage_limit_per_user' => 'nullable|integer|min:0',
                'usage_limit_per_day' => 'nullable|integer|min:0',
                'applicable_products' => 'nullable|array',
                'applicable_categories' => 'nullable|array',
                'applicable_segments' => 'nullable|array',
                'excluded_products' => 'nullable|array',
                'bxgy_buy_quantity' => 'nullable|integer|min:1',
                'bxgy_get_quantity' => 'nullable|integer|min:1',
                'bxgy_get_products' => 'nullable|array',
                'stackable' => 'nullable|boolean',
                'auto_apply' => 'nullable|boolean',
                'first_order_only' => 'nullable|boolean',
                'valid_from' => 'nullable|date',
                'valid_to' => 'nullable|date|after_or_equal:valid_from',
            ]);

            $coupon = $this->couponService->create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $coupon,
                'message' => 'Coupon created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update coupon
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'string|max:255',
                'description' => 'nullable|string',
                'value' => 'numeric|min:0',
                'min_purchase_amount' => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'usage_limit_total' => 'nullable|integer|min:0',
                'usage_limit_per_user' => 'nullable|integer|min:0',
                'usage_limit_per_day' => 'nullable|integer|min:0',
                'applicable_products' => 'nullable|array',
                'applicable_categories' => 'nullable|array',
                'applicable_segments' => 'nullable|array',
                'excluded_products' => 'nullable|array',
                'stackable' => 'nullable|boolean',
                'auto_apply' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'valid_from' => 'nullable|date',
                'valid_to' => 'nullable|date',
            ]);

            $coupon = $this->couponService->update($id, $validated);

            return response()->json([
                'status' => 'success',
                'data' => $coupon,
                'message' => 'Coupon updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete coupon
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->couponService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Coupon deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Validate coupon
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'order_amount' => 'nullable|numeric',
            ]);

            $result = $this->couponService->validateCoupon(
                $validated['code'],
                $validated['order_amount'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'Coupon is valid',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get coupon statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->couponService->getStats();

            return response()->json([
                'status' => 'success',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate coupon codes in batch
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1|max:10000',
                'prefix' => 'nullable|string|max:10',
                'coupon_id' => 'required|exists:coupons,id',
            ]);

            $batch = $this->couponService->generateCodes(
                $validated['coupon_id'],
                $validated['quantity'],
                $validated['prefix'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $batch,
                'message' => 'Coupon codes generated successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export coupons
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status', 'type', 'date_from', 'date_to']);
            return $this->couponService->export($filters);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
