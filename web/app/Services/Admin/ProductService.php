<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductImageRepositoryInterface $productImageRepository,
        private ProductVariantRepositoryInterface $productVariantRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * Get all products with filters
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @param  int|null  $categoryId
     * @param  bool  $onlyWithStock  Only show products with stock > 0 (for admin)
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage, $filters);
    }

    /**
     * Get home page data - categories with featured products
     * Optimized to avoid N+1 and limit per relation issues
     *
     * @param int $categoriesLimit
     * @param int $productsPerCategory
     * @return array
     */
    public function getHomeData(int $categoriesLimit = 6, int $productsPerCategory = 4): array
    {
        $cacheKey = "home_data_{$categoriesLimit}_{$productsPerCategory}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($categoriesLimit, $productsPerCategory) {
            $categories = $this->categoryRepository->getAll([
                'is_active' => true,
                'limit' => $categoriesLimit
            ]);

            $result = [];
            foreach ($categories as $category) {
                $products = $this->productRepository->getTopProductsByCategory($category->id, $productsPerCategory);
                
                if ($products->count() > 0) {
                    $result[] = [
                        'category' => [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        ],
                        'products' => $products->map(function ($product) {
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
                }
            }

            return $result;
        });
    }

    /**
     * Get products by category
     *
     * @param  int  $categoryId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->productRepository->getByCategory($categoryId, $perPage);
    }

    /**
     * Get product details with relations
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getDetails(int $id): array
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundException("Product with ID {$id} not found");
        }

        $product->load([
            'category',
            'images',
            'variants',
            'reviews' => function ($q) {
                $q->whereNull('parent_id')->latest()->limit(10);
            }
        ]);

        $avgRating = $product->reviews()->avg('rating');
        $countRating = $product->reviews()->count();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'thumbnail' => $product->thumbnail,
            'specs' => $product->specs,
            'manufacturer_name' => $product->manufacturer_name,
            'manufacturer_brand' => $product->manufacturer_brand,
            'stock_quantity' => $product->stock_quantity,
            'discount_percentage' => $product->discount_percentage,
            'is_active' => $product->is_active,
            'published_at' => $product->published_at?->toIso8601String(),
            'category' => $product->category,
            'images' => $product->images,
            'variants' => $product->variants,
            'rating' => [
                'avg' => $avgRating ? round($avgRating, 2) : 0,
                'count' => $countRating,
            ],
            'latest_reviews' => $product->reviews,
            'faqs' => $product->faqs,
        ];
    }

    /**
     * Create product
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . time();
            }

            if (!isset($data['price'])) {
                $data['price'] = 0;
            }

            // Handle base64 thumbnail
            if (!empty($data['thumbnail']) && str_starts_with($data['thumbnail'], 'data:image')) {
                $data['thumbnail'] = $this->uploadBase64Image($data['thumbnail'], 'products');
            }

            $product = $this->productRepository->create($data);

            // Invalidate home data cache
            $this->clearHomeDataCache();

            // Handle gallery images
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if (str_starts_with($image, 'data:image')) {
                        $imageUrl = $this->uploadBase64Image($image, 'products');
                        if ($imageUrl) {
                            $this->productImageRepository->create([
                                'product_id' => $product->id,
                                'image_url' => $imageUrl,
                                'is_main' => false,
                            ]);
                        }
                    }
                }
            }

            // Handle variants
            if (!empty($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variant) {
                    $this->productVariantRepository->create([
                        'product_id' => $product->id,
                        'variant_type' => $variant['variant_type'] ?? 'storage',
                        'label' => $variant['label'] ?? '',
                        'color' => $variant['label'] ?? '', // Legacy field
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'stock' => $variant['stock'] ?? 0,
                        'is_default' => $variant['is_default'] ?? false,
                        'color_code' => $variant['color_code'] ?? null,
                    ]);
                }
            }

            return $product->toArray();
        });
    }

    /**
     * Update product
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->productRepository->findById($id);

            if (!$product) {
                throw new NotFoundException("Product with ID {$id} not found");
            }

            // Handle base64 thumbnail
            if (!empty($data['thumbnail']) && str_starts_with($data['thumbnail'], 'data:image')) {
                $data['thumbnail'] = $this->uploadBase64Image($data['thumbnail'], 'products');
            }

            $product = $this->productRepository->update($product, $data);

            // Invalidate home data cache
            $this->clearHomeDataCache();

            // Handle gallery images
            if (isset($data['images']) && is_array($data['images'])) {
                // 1. Get current image list from DB
                $currentImages = $product->images;
                $keptImageUrls = [];
                $newBase64Images = [];

                // 2. Separate existing URLs and new Base64 images
                foreach ($data['images'] as $image) {
                    if (str_starts_with($image, 'data:image')) {
                        $newBase64Images[] = $image;
                    } else {
                        $keptImageUrls[] = $image;
                    }
                }

                // 3. Delete images that are not in the kept list
                foreach ($currentImages as $currImg) {
                    if (!in_array($currImg->image_url, $keptImageUrls)) {
                        // Delete DB Record
                        $this->productImageRepository->delete($currImg);
                    }
                }

                // 4. Upload and create new images
                foreach ($newBase64Images as $base64) {
                    $imageUrl = $this->uploadBase64Image($base64, 'products');
                    if ($imageUrl) {
                        $this->productImageRepository->create([
                            'product_id' => $product->id,
                            'image_url' => $imageUrl,
                            'is_main' => false,
                        ]);
                    }
                }
            }

            // Handle variants (Sync logic)
            if (isset($data['variants']) && is_array($data['variants'])) {
                // For simplicity in this implementation, we delete old variants and create new ones
                // Alternatively, you could match by ID and update
                $this->productVariantRepository->deleteByProduct($product->id);
                
                foreach ($data['variants'] as $variant) {
                    $this->productVariantRepository->create([
                        'product_id' => $product->id,
                        'variant_type' => $variant['variant_type'] ?? 'storage',
                        'label' => $variant['label'] ?? '',
                        'color' => $variant['label'] ?? '', // Legacy field
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'stock' => $variant['stock'] ?? 0,
                        'is_default' => $variant['is_default'] ?? false,
                        'color_code' => $variant['color_code'] ?? null,
                    ]);
                }
            }

            return $product->toArray();
        });
    }

    /**
     * Delete product
     *
     * @param  int  $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundException("Product with ID {$id} not found");
        }

        // Delete related records first to avoid FK constraint errors
        // Order matters: delete child tables before parent references
        \DB::table('quality_checks')->where('product_id', $id)->delete();
        \DB::table('stocks')->where('product_id', $id)->delete();
        \DB::table('product_images')->where('product_id', $id)->delete();
        \DB::table('product_variants')->where('product_id', $id)->delete();
        \DB::table('reviews')->where('product_id', $id)->delete();
        \DB::table('wishlist_items')->where('product_id', $id)->delete();
        \DB::table('cart_items')->where('product_id', $id)->delete();
        \DB::table('order_items')->where('product_id', $id)->delete();
        \DB::table('inbound_batch_items')->where('product_id', $id)->delete();

        $this->productRepository->delete($product);
        
        // Invalidate home data cache
        $this->clearHomeDataCache();
    }

    /**
     * Clear home data cache
     */
    private function clearHomeDataCache(): void
    {
        // We use a wildcard approach or specific keys if known
        // Since limit could vary, it's safer to use tags if cache driver supports it
        // but for now we'll just clear common keys or provide a way to clear all home data
        Cache::forget('home_data_6_4');
        Cache::forget('home_data_10_4');
    }

    /**
     * Add image to product
     *
     * @param  int  $productId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function addImage(int $productId, array $data): array
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        // If this is main image, unset other main images
        if ($data['is_main'] ?? false) {
            $this->productImageRepository->updateByProduct($productId, ['is_main' => false]);
        }

        $image = $this->productImageRepository->create([
            'product_id' => $productId,
            'image_url' => $data['image_url'],
            'is_main' => $data['is_main'] ?? false,
        ]);

        return $image->toArray();
    }

    /**
     * Remove image from product
     *
     * @param  int  $productId
     * @param  int  $imageId
     * @return void
     * @throws NotFoundException
     */
    public function removeImage(int $productId, int $imageId): void
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $image = $this->productImageRepository->findById($imageId);

        if (!$image || $image->product_id !== $productId) {
            throw new NotFoundException("Image not found or does not belong to this product");
        }

        $this->productImageRepository->delete($image);
    }

    /**
     * Add variant to product
     *
     * @param  int  $productId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function addVariant(int $productId, array $data): array
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $variant = $this->productVariantRepository->create([
            'product_id' => $productId,
            'color' => $data['color'],
            'stock' => $data['stock'],
        ]);

        return $variant->toArray();
    }

    /**
     * Update product variant
     *
     * @param  int  $productId
     * @param  int  $variantId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function updateVariant(int $productId, int $variantId, array $data): array
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $variant = $this->productVariantRepository->findById($variantId);

        if (!$variant || $variant->product_id !== $productId) {
            throw new NotFoundException("Variant not found or does not belong to this product");
        }

        $variant = $this->productVariantRepository->update($variant, $data);
        return $variant->toArray();
    }

    /**
     * Remove variant from product
     *
     * @param  int  $productId
     * @param  int  $variantId
     * @return void
     * @throws NotFoundException
     */
    public function removeVariant(int $productId, int $variantId): void
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $variant = $this->productVariantRepository->findById($variantId);

        if (!$variant || $variant->product_id !== $productId) {
            throw new NotFoundException("Variant not found or does not belong to this product");
        }

        $this->productVariantRepository->delete($variant);
    }

    /**
     * Upload base64 image
     * 
     * @param string $base64String
     * @param string $folder
     * @return string|null
     */
    private function uploadBase64Image(string $base64String, string $folder): ?string
    {
        try {
            // Check if it's a valid base64 image
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                return null;
            }

            // Take the mime type
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if type is supported
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                return null;
            }

            // Remove the header (data:image/xxx;base64,)
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            
            // Decode
            $typeString = base64_decode($base64String);

            if ($typeString === false) {
                return null;
            }

            // Generate filename
            $filename = $folder . '/' . uniqid() . '.' . $type;

            // Store file
            Storage::disk('public')->put($filename, $typeString);

            // Return full URL
            return asset('storage/' . $filename);
        } catch (\Exception $e) {
            Log::error('Failed to upload base64 image: ' . $e->getMessage());
            return null;
        }
    }
}

