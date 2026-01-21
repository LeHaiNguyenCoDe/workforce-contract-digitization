<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * Get all categories
     *
     * @return Collection
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->categoryRepository->getAll($filters);
    }

    /**
     * Get category by ID
     *
     * @param  int  $id
     * @return array
     * @throws \App\Exceptions\NotFoundException
     */
    public function getById(int $id, bool $checkActiveOnly = false): array
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category || ($checkActiveOnly && !$category->is_active)) {
            throw new \App\Exceptions\NotFoundException("Category with ID {$id} not found");
        }

        return $category->toArray();
    }

    /**
     * Create category
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        // Handle base64 image upload
        if (!empty($data['image']) && str_starts_with($data['image'], 'data:image')) {
            $data['image'] = $this->uploadBase64Image($data['image'], 'categories');
        }

        $category = $this->categoryRepository->create($data);
        return $category->toArray();
    }

    /**
     * Update category
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws \App\Exceptions\NotFoundException
     */
    public function update(int $id, array $data): array
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \App\Exceptions\NotFoundException("Category with ID {$id} not found");
        }

        // Handle base64 image upload
        if (!empty($data['image']) && str_starts_with($data['image'], 'data:image')) {
            $data['image'] = $this->uploadBase64Image($data['image'], 'categories');
        }

        $category = $this->categoryRepository->update($category, $data);
        return $category->toArray();
    }

    /**
     * Delete category
     *
     * @param  int  $id
     * @return void
     * @throws \App\Exceptions\NotFoundException
     */
    public function delete(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \App\Exceptions\NotFoundException("Category with ID {$id} not found");
        }

        $this->categoryRepository->delete($category);
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
            Log::error('Failed to upload base64 image for category: ' . $e->getMessage());
            return null;
        }
    }
}

