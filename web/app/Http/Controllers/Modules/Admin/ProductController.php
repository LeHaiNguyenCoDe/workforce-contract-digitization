<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\ProductImageRequest;
use App\Http\Requests\Modules\Admin\ProductVariantRequest;
use App\Http\Requests\Modules\Admin\ProductStoreRequest;
use App\Http\Requests\Modules\Admin\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\Admin\ProductService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private ProductService $productService
    ) {
    }

    /**
     * Get all products with filters
     * For admin: Only show products that have stock in warehouse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 12);
            $search = $request->query('search');
            $categoryId = $request->query('category_id');
            // For admin products page: only show products with stock
            $onlyWithStock = (bool) $request->query('only_with_stock', false);

            $products = $this->productService->getAll($perPage, $search, $categoryId, $onlyWithStock);

            return $this->paginatedResponse($products);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Get products by category
     */
    public function byCategory(Category $category, Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 12);
            $products = $this->productService->getByCategory($category->id, $perPage);

            return $this->paginatedResponse($products);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Get product details
     */
    public function show(Product $product): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $productData = $this->productService->getDetails($product->id);

            return $this->successResponse($productData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Add image to product
     */
    public function addImage(Product $product, ProductImageRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $image = $this->productService->addImage($product->id, $request->validated());

            return $this->createdResponse($image, 'image_added');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Remove image from product
     */
    public function removeImage(Product $product, int $image): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $this->productService->removeImage($product->id, $image);

            return $this->deletedResponse('image_removed');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Add variant to product
     */
    public function addVariant(Product $product, ProductVariantRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $variant = $this->productService->addVariant($product->id, $request->validated());

            return $this->createdResponse($variant, 'variant_added');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Update product variant
     */
    public function updateVariant(Product $product, int $variant, ProductVariantRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $variantData = $this->productService->updateVariant($product->id, $variant, $request->validated());

            return $this->updatedResponse($variantData, 'variant_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Remove variant from product
     */
    public function removeVariant(Product $product, int $variant): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $this->productService->removeVariant($product->id, $variant);

            return $this->deletedResponse('variant_removed');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Create product
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            return $this->createdResponse($product, 'product_created');
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Update product
     */
    public function update(Product $product, ProductUpdateRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return $this->notFoundResponse('product_not_found');
            }

            $productData = $this->productService->update($product->id, $request->validated());

            return $this->updatedResponse($productData, 'product_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('product_not_found');
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Delete product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            \Log::info('Delete product request', ['product_id' => $product->id ?? null]);

            if (!$product || !$product->id) {
                \Log::warning('Delete product failed: Product not found');
                return $this->notFoundResponse('product_not_found');
            }

            // Check if product has related stocks
            $stockCount = \DB::table('stocks')->where('product_id', $product->id)->count();
            \Log::info('Product stock check', ['product_id' => $product->id, 'stock_count' => $stockCount]);

            $this->productService->delete($product->id);

            \Log::info('Product deleted successfully', ['product_id' => $product->id]);

            return $this->deletedResponse('product_deleted');
        } catch (NotFoundException $ex) {
            \Log::warning('Delete product failed: Not found', ['message' => $ex->getMessage()]);
            return $this->notFoundResponse('product_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


