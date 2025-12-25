<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductImageRepositoryInterface $productImageRepository,
        private ProductVariantRepositoryInterface $productVariantRepository
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
    public function getAll(int $perPage = 12, ?string $search = null, ?int $categoryId = null, bool $onlyWithStock = false): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage, $search, $categoryId, $onlyWithStock);
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
            'short_description' => $product->short_description,
            'description' => $product->description,
            'thumbnail' => $product->thumbnail,
            'specs' => $product->specs,
            'category' => $product->category,
            'images' => $product->images,
            'variants' => $product->variants,
            'rating' => [
                'avg' => $avgRating ? round($avgRating, 2) : 0,
                'count' => $countRating,
            ],
            'latest_reviews' => $product->reviews,
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
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . time();
        }

        if (!isset($data['price'])) {
            $data['price'] = 0;
        }

        $product = $this->productRepository->create($data);
        return $product->toArray();
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
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundException("Product with ID {$id} not found");
        }

        $product = $this->productRepository->update($product, $data);
        return $product->toArray();
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

        $this->productRepository->delete($product);
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
}

