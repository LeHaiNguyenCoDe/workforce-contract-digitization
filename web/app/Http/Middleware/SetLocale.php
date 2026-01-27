<?php

namespace App\Http\Middleware;

use App\Services\Core\LanguageDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        private LanguageDetectionService $detectionService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();
        $locale = $this->detectionService->detect($request, $userId);

        App::setLocale($locale);
        
        if ($request->hasSession()) {
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}


