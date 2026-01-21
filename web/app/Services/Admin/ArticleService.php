<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleService
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {
    }

    /**
     * Get all articles
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 6): LengthAwarePaginator
    {
        return $this->articleRepository->getAll($perPage);
    }

    /**
     * Get article by ID
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new NotFoundException("Article with ID {$id} not found");
        }

        return $article->toArray();
    }

    /**
     * Create article
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        $article = $this->articleRepository->create($data);
        return $article->toArray();
    }

    /**
     * Update article
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new NotFoundException("Article with ID {$id} not found");
        }

        $article = $this->articleRepository->update($article, $data);
        return $article->toArray();
    }

    /**
     * Delete article
     *
     * @param  int  $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new NotFoundException("Article with ID {$id} not found");
        }

        $this->articleRepository->delete($article);
    }

    /**
     * Publish article
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function publish(int $id): array
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new NotFoundException("Article with ID {$id} not found");
        }

        $article = $this->articleRepository->update($article, [
            'is_published' => true,
            'published_at' => now(),
        ]);

        return $article->toArray();
    }

    /**
     * Unpublish article
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function unpublish(int $id): array
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new NotFoundException("Article with ID {$id} not found");
        }

        $article = $this->articleRepository->update($article, [
            'is_published' => false,
            'published_at' => null,
        ]);

        return $article->toArray();
    }
}

