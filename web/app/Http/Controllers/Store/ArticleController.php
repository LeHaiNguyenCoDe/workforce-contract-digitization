<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ArticleStoreRequest;
use App\Http\Requests\Store\ArticleUpdateRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService
    ) {
    }

    /**
     * Get all articles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 6);
            $articles = $this->articleService->getAll($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $articles,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get article details
     */
    public function show(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found',
                ], 404);
            }

            $articleData = $this->articleService->getById($article->id);

            return response()->json([
                'status' => 'success',
                'data' => $articleData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Publish article
     */
    public function publish(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found',
                ], 404);
            }

            $article->update(['published_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Article published',
                'data' => $article->fresh(),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Unpublish article
     */
    public function unpublish(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found',
                ], 404);
            }

            $article->update(['published_at' => null]);

            return response()->json([
                'status' => 'success',
                'message' => 'Article unpublished',
                'data' => $article->fresh(),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Create article
     */
    public function store(ArticleStoreRequest $request): JsonResponse
    {
        try {
            $article = $this->articleService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Article created',
                'data' => $article,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Update article
     */
    public function update(Article $article, ArticleUpdateRequest $request): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found',
                ], 404);
            }

            $articleData = $this->articleService->update($article->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Article updated',
                'data' => $articleData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Delete article
     */
    public function destroy(Article $article): JsonResponse
    {
        try {
            if (!$article || !$article->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found',
                ], 404);
            }

            $this->articleService->delete($article->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Article deleted',
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}


