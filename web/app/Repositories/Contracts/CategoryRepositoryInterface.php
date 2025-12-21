<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Find category by ID
     *
     * @param  int  $id
     * @return Category|null
     */
    public function findById(int $id): ?Category;

    /**
     * Find category by slug
     *
     * @param  string  $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a new category
     *
     * @param  array  $data
     * @return Category
     */
    public function create(array $data): Category;

    /**
     * Update category
     *
     * @param  Category  $category
     * @param  array  $data
     * @return Category
     */
    public function update(Category $category, array $data): Category;

    /**
     * Delete category
     *
     * @param  Category  $category
     * @return bool
     */
    public function delete(Category $category): bool;
}

