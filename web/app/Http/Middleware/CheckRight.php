<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRight
{
    public function handle(Request $request, Closure $next, string $right): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRight($right)) {
            return response()->json([
                'message' => 'Forbidden. Missing required right: '.$right,
            ], 403);
        }

        return $next($request);
    }
}


