<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\ArticleStoreRequest;
use App\Http\Requests\Modules\Admin\ArticleUpdateRequest;
use App\Models\Article;
use App\Services\Admin\ArticleService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private ArticleService $articleService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 6);
            $articles = $this->articleService->getAll($perPage);

            return $this->successResponse($articles);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function show(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return $this->notFoundResponse('article_not_found');
            }

            $articleData = $this->articleService->getById($article->id);

            return $this->successResponse($articleData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('article_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(ArticleStoreRequest $request): JsonResponse
    {
        try {
            $article = $this->articleService->create($request->validated());

            return $this->createdResponse($article, 'article_created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function update(Article $article, ArticleUpdateRequest $request): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return $this->notFoundResponse('article_not_found');
            }

            $articleData = $this->articleService->update($article->id, $request->validated());

            return $this->updatedResponse($articleData, 'article_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('article_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return $this->notFoundResponse('article_not_found');
            }

            $this->articleService->delete($article->id);

            return $this->deletedResponse('article_deleted');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('article_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Publish article
     */
    public function publish(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return $this->notFoundResponse('article_not_found');
            }

            $articleData = $this->articleService->publish($article->id);

            return $this->updatedResponse($articleData, 'article_published');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('article_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Unpublish article
     */
    public function unpublish(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return $this->notFoundResponse('article_not_found');
            }

            $articleData = $this->articleService->unpublish($article->id);

            return $this->updatedResponse($articleData, 'article_unpublished');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('article_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


