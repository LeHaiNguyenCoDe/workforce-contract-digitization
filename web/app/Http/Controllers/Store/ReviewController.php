<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {
    }

    /**
     * Get reviews for a product
     */
    public function index(Product $product, Request $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $perPage = $request->query('per_page', 10);
            $reviews = $this->reviewService->getByProductId($product->id, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $reviews,
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
     * Create review
     */
    public function store(Product $product, ReviewRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $userId = Auth::id();
            $review = $this->reviewService->create($product->id, $userId, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Review created',
                'data' => $review,
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
     * Get all reviews (Admin)
     */
    public function getAllReviews(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $reviews = $this->reviewService->getAll($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $reviews,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get review by ID (Admin)
     */
    public function showReview(Review $review): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $review->load(['user:id,name,email', 'product:id,name']),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Approve review (Admin)
     * Note: Reviews are approved by default. This method can be used to mark as featured or verified.
     */
    public function approve(Review $review): JsonResponse
    {
        try {
            // For now, just return success as reviews don't have approval status
            // You can add a migration to add 'is_approved' or 'status' field later
            return response()->json([
                'status' => 'success',
                'message' => 'Review approved',
                'data' => $review,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Reject review (Admin)
     * Note: This will delete the review as there's no status field
     */
    public function reject(Review $review): JsonResponse
    {
        try {
            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review rejected and deleted',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Delete review (Admin)
     */
    public function destroy(Review $review): JsonResponse
    {
        try {
            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}


