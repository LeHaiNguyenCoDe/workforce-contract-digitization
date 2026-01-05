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
            
            $filters = [
                'search' => $request->query('search'),
                'category_id' => $request->query('category_id'),
                'category_ids' => $request->query('category_ids'),
                'brands' => $request->query('brands'),
                'dimensions' => $request->query('dimensions'),
                'color' => $request->query('color'),
                'sort_by' => $request->query('sort_by', 'latest'),
                'only_with_stock' => (bool) $request->query('only_with_stock', false),
            ];

            $products = $this->productService->getAll($perPage, $filters);

            return $this->paginatedResponse($products);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
    /**
     * Get home page data - categories with featured products
     * Optimized single query - no N+1 problem
     */
    public function getHomeData(Request $request): JsonResponse
    {
        try {
            $categoriesLimit = (int) $request->query('categories_limit', 6);
            $productsPerCategory = (int) $request->query('products_per_category', 4);

            $categories = \App\Models\Category::with(['products' => function ($query) use ($productsPerCategory) {
                $query->with(['images' => function ($q) {
                    $q->select('id', 'product_id', 'image_url', 'is_main')
                        ->orderByDesc('is_main');
                }])
                ->select('id', 'name', 'slug', 'price', 'thumbnail', 'category_id')
                ->limit($productsPerCategory);
            }])
            ->select('id', 'name', 'slug')
            ->limit($categoriesLimit)
            ->get();

            $result = $categories->filter(function ($category) {
                return $category->products->count() > 0;
            })->map(function ($category) {
                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'products' => $category->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'price' => $product->price,
                            'thumbnail' => $product->thumbnail,
                            'images' => $product->images,
                        ];
                    })->values(),
                ];
            })->values();

            return $this->successResponse($result);
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


