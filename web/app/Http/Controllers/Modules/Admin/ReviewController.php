<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Services\Admin\ReviewService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private ReviewService $reviewService
    ) {
    }

    public function index(Product $product, Request $request): JsonResponse
    {
        try {
            if (!$product->exists) {
                return $this->notFoundResponse('product_not_found');
            }

            $perPage = $request->query('per_page', 10);
            $reviews = $this->reviewService->getByProductId($product->id, $perPage);

            return $this->paginatedResponse($reviews);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(Product $product, ReviewRequest $request): JsonResponse
    {
        try {
            if (!$product->exists) {
                return $this->notFoundResponse('product_not_found');
            }

            $userId = Auth::id();
            $review = $this->reviewService->create($product->id, $userId, $request->validated());

            return $this->createdResponse($review, 'review_created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function getFeaturedReviews(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);
            $reviews = $this->reviewService->getFeaturedReviews($limit);

            return $this->successResponse($reviews);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function getAllReviews(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $reviews = $this->reviewService->getAll($perPage);

            return $this->paginatedResponse($reviews);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function showReview(Review $review): JsonResponse
    {
        try {
            return $this->successResponse($review->load(['user:id,name,email', 'product:id,name']));
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(Review $review): JsonResponse
    {
        try {
            $review->delete();
            return $this->deletedResponse('review_deleted');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}
