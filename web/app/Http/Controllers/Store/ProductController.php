<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductImageRequest;
use App\Http\Requests\Store\ProductVariantRequest;
use App\Http\Requests\Store\ProductStoreRequest;
use App\Http\Requests\Store\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
            $onlyWithStock = $request->query('only_with_stock', false);

            $products = $this->productService->getAll($perPage, $search, $categoryId, $onlyWithStock);

            return response()->json([
                'status' => 'success',
                'data' => $products,
            ]);
        } catch (\Exception $ex) {
            \Log::error('Get products error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
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

            return response()->json([
                'status' => 'success',
                'data' => $products,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get product details
     */
    public function show(Product $product): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $productData = $this->productService->getDetails($product->id);

            return response()->json([
                'status' => 'success',
                'data' => $productData,
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
     * Add image to product
     */
    public function addImage(Product $product, ProductImageRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $image = $this->productService->addImage($product->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Image added',
                'data' => $image,
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
     * Remove image from product
     */
    public function removeImage(Product $product, int $image): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $this->productService->removeImage($product->id, $image);

            return response()->json([
                'status' => 'success',
                'message' => 'Image removed',
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
     * Add variant to product
     */
    public function addVariant(Product $product, ProductVariantRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $variant = $this->productService->addVariant($product->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Variant added',
                'data' => $variant,
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
     * Update product variant
     */
    public function updateVariant(Product $product, int $variant, ProductVariantRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $variantData = $this->productService->updateVariant($product->id, $variant, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Variant updated',
                'data' => $variantData,
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
     * Remove variant from product
     */
    public function removeVariant(Product $product, int $variant): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $this->productService->removeVariant($product->id, $variant);

            return response()->json([
                'status' => 'success',
                'message' => 'Variant removed',
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
     * Create product
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Product created',
                'data' => $product,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Create product error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while creating the product',
            ], 500);
        }
    }

    /**
     * Update product
     */
    public function update(Product $product, ProductUpdateRequest $request): JsonResponse
    {
        try {
            if (!$product || !$product->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            $productData = $this->productService->update($product->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated',
                'data' => $productData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Update product error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'product_id' => $product->id ?? null,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while updating the product',
            ], 500);
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            // Check if product has related stocks
            $stockCount = \DB::table('stocks')->where('product_id', $product->id)->count();
            \Log::info('Product stock check', ['product_id' => $product->id, 'stock_count' => $stockCount]);

            $this->productService->delete($product->id);

            \Log::info('Product deleted successfully', ['product_id' => $product->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted',
            ]);
        } catch (NotFoundException $ex) {
            \Log::warning('Delete product failed: Not found', ['message' => $ex->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            \Log::error('Delete product error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'product_id' => $product->id ?? null,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while deleting the product',
            ], 500);
        }
    }
}


