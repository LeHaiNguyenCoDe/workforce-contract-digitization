<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\LeadService;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function __construct(
        private LeadService $leadService
    ) {
    }

    /**
     * Get all leads with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status',
                'temperature',
                'source',
                'assigned_to',
                'min_score',
                'search',
            ]);

            $perPage = $request->input('per_page', 20);
            $leads = $this->leadService->getAll($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $leads,
                'message' => 'Leads retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single lead
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lead = Lead::with(['assignedUser', 'activities', 'convertedUser', 'convertedOrder'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $lead,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new lead
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'source' => 'required|in:website,facebook,google,instagram,tiktok,referral,event,cold_call,landing_page,other',
                'source_detail' => 'nullable|string',
                'score' => 'nullable|integer|min:0|max:100',
                'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation,converted,lost,disqualified',
                'temperature' => 'nullable|in:cold,warm,hot',
                'estimated_value' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            $lead = $this->leadService->create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $lead,
                'message' => 'Lead created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update lead
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation,converted,lost,disqualified',
                'temperature' => 'nullable|in:cold,warm,hot',
                'estimated_value' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $lead = $this->leadService->update($id, $validated);

            return response()->json([
                'status' => 'success',
                'data' => $lead,
                'message' => 'Lead updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete lead
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->leadService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Lead deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $lead = $this->leadService->assignLead($id, $validated['user_id']);

            return response()->json([
                'status' => 'success',
                'data' => $lead,
                'message' => 'Lead assigned successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Convert lead to customer
     */
    public function convert(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'order_id' => 'nullable|exists:orders,id',
            ]);

            $lead = $this->leadService->convertLead(
                $id,
                $validated['user_id'],
                $validated['order_id'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $lead,
                'message' => 'Lead converted to customer successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add activity to lead
     */
    public function addActivity(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string',
                'description' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            $activity = $this->leadService->addActivity(
                $id,
                $validated['type'],
                $validated['description'] ?? null,
                $validated['metadata'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $activity,
                'message' => 'Activity added successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get lead statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $stats = $this->leadService->getStats($filters);

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
     * Get lead pipeline (funnel)
     */
    public function pipeline(): JsonResponse
    {
        try {
            $pipeline = $this->leadService->getPipeline();

            return response()->json([
                'status' => 'success',
                'data' => $pipeline,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import leads from CSV
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,txt',
            ]);

            $file = $validated['file'];
            $results = $this->leadService->bulkImport($file);

            return response()->json([
                'status' => 'success',
                'data' => $results,
                'message' => 'Leads imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
