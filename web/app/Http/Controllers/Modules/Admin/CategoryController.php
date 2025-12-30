<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\CategoryStoreRequest;
use App\Http\Requests\Modules\Admin\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAll();

            return $this->successResponse($categories);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function show(Category $category): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return $this->notFoundResponse('category_not_found');
            }

            $categoryData = $this->categoryService->getById($category->id);

            return $this->successResponse($categoryData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('category_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return $this->createdResponse($category, 'category_created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function update(Category $category, CategoryUpdateRequest $request): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return $this->notFoundResponse('category_not_found');
            }

            $categoryData = $this->categoryService->update($category->id, $request->validated());

            return $this->updatedResponse($categoryData, 'category_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('category_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        try {
            if (!$category || !$category->id) {
                return $this->notFoundResponse('category_not_found');
            }

            $this->categoryService->delete($category->id);

            return $this->deletedResponse('category_deleted');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('category_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


