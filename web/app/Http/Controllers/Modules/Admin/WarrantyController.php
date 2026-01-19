<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class WarrantyController extends Controller
{
    use StoreApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Warranty::with(['product:id,name,sku', 'customer:id,name,email']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('warranty_code', 'like', "%{$search}%")
                        ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }

            $warranties = $query->latest()->paginate($request->per_page ?? 15);
            return $this->successResponse($warranties);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function lookup(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            $warranty = Warranty::with(['product', 'customer', 'claims'])
                ->where('warranty_code', $code)
                ->first();

            if (!$warranty) {
                return $this->errorResponse('Không tìm thấy bảo hành', 404);
            }

            return $this->successResponse($warranty);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'order_id' => 'nullable|exists:orders,id',
                'customer_id' => 'nullable|exists:users,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'notes' => 'nullable|string',
            ]);

            // Generate warranty code
            $validated['warranty_code'] = 'WRT-' . strtoupper(uniqid());
            $warranty = Warranty::create($validated);

            return $this->successResponse($warranty, 'Đã tạo phiếu bảo hành');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function createClaim(Request $request, int $warrantyId): JsonResponse
    {
        try {
            $warranty = Warranty::findOrFail($warrantyId);

            if ($warranty->status !== 'active') {
                return $this->errorResponse('Bảo hành không còn hiệu lực', 400);
            }

            $validated = $request->validate([
                'issue_description' => 'required|string',
            ]);

            $claim = WarrantyClaim::create([
                'warranty_id' => $warrantyId,
                'issue_description' => $validated['issue_description'],
            ]);

            $warranty->update(['status' => 'claimed']);

            return $this->successResponse($claim, 'Đã tạo yêu cầu bảo hành');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function resolveClaim(Request $request, int $claimId): JsonResponse
    {
        try {
            $claim = WarrantyClaim::with('warranty')->findOrFail($claimId);
            $validated = $request->validate([
                'status' => 'required|in:approved,rejected,completed',
                'resolution' => 'nullable|string',
            ]);

            $claim->update([
                'status' => $validated['status'],
                'resolution' => $validated['resolution'] ?? null,
                'handled_by' => auth()->id(),
            ]);

            if ($validated['status'] === 'completed') {
                $claim->warranty->update(['status' => 'active']);
            }

            return $this->successResponse($claim, 'Đã xử lý yêu cầu');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
