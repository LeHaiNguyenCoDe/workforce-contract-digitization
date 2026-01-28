<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\SegmentationService;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SegmentationController extends Controller
{
    public function __construct(
        private SegmentationService $segmentationService
    ) {
    }

    /**
     * Get all segments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $segments = $this->segmentationService->getAll($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $segments,
                'message' => 'Segments retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single segment
     */
    public function show(int $id): JsonResponse
    {
        try {
            $segment = CustomerSegment::with(['creator', 'customers', 'history'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $segment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new segment
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:customer_segments',
                'description' => 'nullable|string',
                'type' => 'required|in:static,dynamic',
                'conditions' => 'nullable|array',
                'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
                'icon' => 'nullable|string|max:50',
            ]);

            // Set default color if not provided
            if (empty($validated['color'])) {
                $validated['color'] = '#3B82F6';
            }

            $segment = $this->segmentationService->create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $segment,
                'message' => 'Segment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update segment
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'string|max:255|unique:customer_segments,name,' . $id,
                'description' => 'nullable|string',
                'conditions' => 'nullable|array',
                'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
                'icon' => 'nullable|string|max:50',
                'is_active' => 'nullable|boolean',
            ]);

            // Set default color if not provided but needed
            if (empty($validated['color']) && isset($validated['color'])) {
                $validated['color'] = '#3B82F6';
            }

            $segment = $this->segmentationService->update($id, $validated);

            return response()->json([
                'status' => 'success',
                'data' => $segment,
                'message' => 'Segment updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete segment
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->segmentationService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Segment deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get segment customers with pagination
     */
    public function customers(Request $request, int $id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $customers = $this->segmentationService->getSegmentCustomers($id, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $customers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Add customers to segment
     */
    public function addCustomers(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_ids' => 'required|array|min:1',
                'customer_ids.*' => 'integer|exists:users,id',
                'source' => 'nullable|string|in:manual,auto',
            ]);

            $result = $this->segmentationService->addCustomers(
                $id,
                $validated['customer_ids'],
                $validated['source'] ?? 'manual'
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'Customers added successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove customers from segment
     */
    public function removeCustomers(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_ids' => 'required|array|min:1',
                'customer_ids.*' => 'integer|exists:users,id',
            ]);

            $result = $this->segmentationService->removeCustomers(
                $id,
                $validated['customer_ids']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Customers removed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get segments for a customer
     */
    public function customerSegments(int $userId): JsonResponse
    {
        try {
            $segments = $this->segmentationService->getCustomerSegments($userId);

            return response()->json([
                'status' => 'success',
                'data' => $segments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Preview segment size before creating
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'conditions' => 'required|array',
            ]);

            $size = $this->segmentationService->previewSegmentSize($validated['conditions']);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'estimated_size' => $size,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Calculate dynamic segment
     */
    public function calculate(int $id): JsonResponse
    {
        try {
            $this->segmentationService->calculateDynamicSegment(
                CustomerSegment::findOrFail($id)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Segment calculated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Recalculate all active dynamic segments
     */
    public function recalculateAll(): JsonResponse
    {
        try {
            $this->segmentationService->recalculateAllSegments();

            return response()->json([
                'status' => 'success',
                'message' => 'All segments recalculated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get segment statistics
     */
    public function stats(int $id): JsonResponse
    {
        try {
            $segment = CustomerSegment::findOrFail($id);
            $stats = $this->segmentationService->getSegmentStats($segment);

            return response()->json([
                'status' => 'success',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
