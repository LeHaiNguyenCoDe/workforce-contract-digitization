<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingPartner;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ShippingController extends Controller
{
    use StoreApiResponse;

    /**
     * List all shipping partners
     */
    public function index(): JsonResponse
    {
        try {
            $partners = ShippingPartner::all();
            return $this->successResponse($partners);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Show single partner
     */
    public function show(int $id): JsonResponse
    {
        try {
            $partner = ShippingPartner::findOrFail($id);
            return $this->successResponse($partner);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Create partner
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:shipping_partners',
                'api_key' => 'nullable|string',
                'api_secret' => 'nullable|string',
                'api_url' => 'nullable|url',
                'is_active' => 'boolean',
                'config' => 'nullable|array',
            ]);

            $partner = ShippingPartner::create($validated);
            return $this->successResponse($partner, 'Đã thêm đối tác vận chuyển');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Update partner
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $partner = ShippingPartner::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'api_key' => 'nullable|string',
                'api_secret' => 'nullable|string',
                'api_url' => 'nullable|url',
                'is_active' => 'boolean',
                'config' => 'nullable|array',
            ]);

            $partner->update($validated);
            return $this->successResponse($partner, 'Đã cập nhật đối tác');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Toggle active status
     */
    public function toggle(int $id): JsonResponse
    {
        try {
            $partner = ShippingPartner::findOrFail($id);
            $partner->update(['is_active' => !$partner->is_active]);
            return $this->successResponse($partner, $partner->is_active ? 'Đã bật' : 'Đã tắt');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Calculate shipping fee (mock - integrate with real API)
     */
    public function calculateFee(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'partner_code' => 'required|string',
                'from_district' => 'required|string',
                'to_district' => 'required|string',
                'weight' => 'required|numeric|min:0',
            ]);

            // Mock calculation - in real implementation, call partner API
            $baseFee = 30000;
            $weightFee = $validated['weight'] * 5000;
            $fee = $baseFee + $weightFee;

            return $this->successResponse([
                'partner' => $validated['partner_code'],
                'fee' => $fee,
                'estimated_days' => rand(2, 5),
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
