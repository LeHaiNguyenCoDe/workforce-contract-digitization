<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $articles = Article::query()
            ->select('id', 'title', 'slug', 'thumbnail', 'published_at')
            ->latest('published_at')
            ->paginate($request->query('per_page', 6));

        return response()->json($articles);
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json([
            'data' => $article,
        ]);
    }
}


