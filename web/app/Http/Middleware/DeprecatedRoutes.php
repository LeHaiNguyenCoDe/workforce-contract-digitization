<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deprecated Routes Middleware
 *
 * Adds deprecation headers to legacy route responses.
 */
class DeprecatedRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-API-Deprecated', 'true');
        $response->headers->set('X-API-Deprecation-Info', 'These routes are deprecated. Use /api/v1/admin/* or /api/v1/frontend/* instead.');
        $response->headers->set('X-API-Deprecation-Date', '2026-04-26');

        return $response;
    }
}
