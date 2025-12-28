<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CategoryStoreRequest;
use App\Http\Requests\Store\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAll();

            return response()->json([
                'status' => 'success',
                'data' => $categories,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get category details
     */
    public function show(Category $category): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $categoryData = $this->categoryService->getById($category->id);

            return response()->json([
                'status' => 'success',
                'data' => $categoryData,
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
     * Create category
     */
    public function store(CategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Category created',
                'data' => $category,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Update category
     */
    public function update(Category $category, CategoryUpdateRequest $request): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $categoryData = $this->categoryService->update($category->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated',
                'data' => $categoryData,
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
     * Delete category
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $this->categoryService->delete($category->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted',
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


