<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Get all articles with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 6): LengthAwarePaginator
    {
        return Article::query()
            ->select('id', 'title', 'slug', 'thumbnail', 'published_at')
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Find article by ID
     *
     * @param  int  $id
     * @return Article|null
     */
    public function findById(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * Create a new article
     *
     * @param  array  $data
     * @return Article
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * Update article
     *
     * @param  Article  $article
     * @param  array  $data
     * @return Article
     */
    public function update(Article $article, array $data): Article
    {
        $article->update($data);
        return $article->fresh();
    }

    /**
     * Delete article
     *
     * @param  Article  $article
     * @return bool
     */
    public function delete(Article $article): bool
    {
        return $article->delete();
    }
}

