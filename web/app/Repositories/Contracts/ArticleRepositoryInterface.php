<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    /**
     * Get all articles with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 6): LengthAwarePaginator;

    /**
     * Find article by ID
     *
     * @param  int  $id
     * @return Article|null
     */
    public function findById(int $id): ?Article;

    /**
     * Create a new article
     *
     * @param  array  $data
     * @return Article
     */
    public function create(array $data): Article;

    /**
     * Update article
     *
     * @param  Article  $article
     * @param  array  $data
     * @return Article
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete article
     *
     * @param  Article  $article
     * @return bool
     */
    public function delete(Article $article): bool;
}

