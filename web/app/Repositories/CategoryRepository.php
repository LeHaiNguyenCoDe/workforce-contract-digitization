<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Get all categories
     *
     * @return Collection
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Category::query()
            ->select('id', 'name', 'slug', 'parent_id', 'is_active', 'image', 'created_at', 'updated_at');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Find category by ID
     *
     * @param  int  $id
     * @return Category|null
     */
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Find category by slug
     *
     * @param  string  $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Create a new category
     *
     * @param  array  $data
     * @return Category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update category
     *
     * @param  Category  $category
     * @param  array  $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete category
     *
     * @param  Category  $category
     * @return bool
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}

