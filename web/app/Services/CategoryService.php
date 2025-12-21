<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
    public function getAll(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    /**
     * Get category by ID
     *
     * @param  int  $id
     * @return array
     * @throws \App\Exceptions\NotFoundException
     */
    public function getById(int $id): array
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
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
}

