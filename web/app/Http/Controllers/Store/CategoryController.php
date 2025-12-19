<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->select('id', 'name', 'slug', 'parent_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}


