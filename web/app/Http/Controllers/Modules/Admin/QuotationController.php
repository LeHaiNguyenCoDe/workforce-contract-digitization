<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Http\Resources\Admin\QuotationResource;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class QuotationController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private \App\Services\Admin\QuotationService $quotationService
    ) {
    }

    /**
     * List all quotations with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'search']);
            $perPage = (int) $request->input('per_page', 15);

            $quotations = $this->quotationService->getAll($filters, $perPage);

            return $this->paginatedResponse(QuotationResource::collection($quotations));
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Show single quotation
     */
    public function show(int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->getById($id);
            return $this->successResponse(new QuotationResource($quotation));
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Create new quotation
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'valid_until' => 'nullable|date',
                'note' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            $quotation = $this->quotationService->create($validated);

            return $this->successResponse(new QuotationResource($quotation), 'Tạo báo giá thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Update quotation
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'customer_id' => 'sometimes|exists:users,id',
                'valid_until' => 'nullable|date',
                'note' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.product_id' => 'required_with:items|exists:products,id',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'items.*.unit_price' => 'required_with:items|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            $quotation = $this->quotationService->update($id, $validated);

            return $this->successResponse(new QuotationResource($quotation), 'Cập nhật báo giá thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Send quotation to customer
     */
    public function send(int $id): JsonResponse
    {
        try {
            $this->quotationService->send($id);
            return $this->successResponse(null, 'Đã gửi báo giá');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Convert quotation to order
     */
    public function convertToOrder(int $id): JsonResponse
    {
        try {
            $result = $this->quotationService->convertToOrder($id);
            return $this->successResponse($result, 'Đã chuyển thành đơn hàng');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Delete quotation
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->quotationService->delete($id);
            return $this->successResponse(null, 'Đã xóa báo giá');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
