<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class QuotationController extends Controller
{
    use StoreApiResponse;

    /**
     * List all quotations with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Quotation::with(['customer:id,name,email', 'creator:id,name'])
                ->latest();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }

            $quotations = $query->paginate($request->per_page ?? 15);

            return $this->successResponse($quotations);
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
            $quotation = Quotation::with([
                'customer:id,name,email,phone,address',
                'creator:id,name',
                'items.product:id,name,sku,price'
            ])->findOrFail($id);

            return $this->successResponse($quotation);
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

            DB::beginTransaction();

            // Generate code
            $prefix = 'QT-' . date('Ymd');
            $lastQuotation = Quotation::where('code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();
            $sequence = $lastQuotation
                ? (int) substr($lastQuotation->code, -4) + 1
                : 1;
            $code = $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Calculate total
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0);
                $totalAmount += $itemTotal;
            }

            $quotation = Quotation::create([
                'code' => $code,
                'name' => $validated['name'],
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'status' => 'draft',
                'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
                'total_amount' => $totalAmount,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0),
                ]);
            }

            DB::commit();

            return $this->successResponse(
                $quotation->load(['customer', 'creator', 'items.product']),
                'Tạo báo giá thành công'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Update quotation
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $quotation = Quotation::findOrFail($id);

            if ($quotation->status !== 'draft') {
                return $this->errorResponse('Chỉ có thể sửa báo giá ở trạng thái nháp', 400);
            }

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

            DB::beginTransaction();

            $quotation->update([
                'name' => $validated['name'] ?? $quotation->name,
                'customer_id' => $validated['customer_id'] ?? $quotation->customer_id,
                'valid_until' => $validated['valid_until'] ?? $quotation->valid_until,
                'note' => $validated['note'] ?? $quotation->note,
            ]);

            if (isset($validated['items'])) {
                // Recalculate total
                $totalAmount = 0;
                $quotation->items()->delete();

                foreach ($validated['items'] as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0);
                    $totalAmount += $itemTotal;

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'total' => $itemTotal,
                    ]);
                }

                $quotation->update(['total_amount' => $totalAmount]);
            }

            DB::commit();

            return $this->successResponse(
                $quotation->fresh(['customer', 'creator', 'items.product']),
                'Cập nhật báo giá thành công'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Send quotation to customer
     */
    public function send(int $id): JsonResponse
    {
        try {
            $quotation = Quotation::findOrFail($id);

            if ($quotation->status !== 'draft') {
                return $this->errorResponse('Báo giá đã được gửi', 400);
            }

            $quotation->update(['status' => 'sent']);

            // TODO: Send email to customer

            return $this->successResponse($quotation, 'Đã gửi báo giá');
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
            $quotation = Quotation::with('items')->findOrFail($id);

            if (!in_array($quotation->status, ['sent', 'accepted'])) {
                return $this->errorResponse('Báo giá chưa được gửi hoặc chấp nhận', 400);
            }

            DB::beginTransaction();

            // Create order from quotation
            $order = \App\Models\Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'user_id' => $quotation->customer_id,
                'total' => $quotation->total_amount,
                'status' => 'pending',
                'notes' => 'Chuyển từ báo giá: ' . $quotation->code,
            ]);

            foreach ($quotation->items as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                ]);
            }

            $quotation->update([
                'status' => 'converted',
                // 'converted_order_id' => $order->id, // Add this field if needed
            ]);

            DB::commit();

            return $this->successResponse([
                'quotation' => $quotation,
                'order' => $order,
            ], 'Đã chuyển thành đơn hàng');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Delete quotation
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $quotation = Quotation::findOrFail($id);

            if ($quotation->status !== 'draft') {
                return $this->errorResponse('Chỉ có thể xóa báo giá ở trạng thái nháp', 400);
            }

            $quotation->items()->delete();
            $quotation->delete();

            return $this->successResponse(null, 'Đã xóa báo giá');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
