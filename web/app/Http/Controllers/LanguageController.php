<?php

namespace App\Http\Controllers;

use App\Http\Requests\LanguageSetRequest;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function __construct(
        private LanguageService $languageService
    ) {
    }

    /**
     * Get current language
     */
    public function current(): JsonResponse
    {
        $current = $this->languageService->getCurrentLocale();
        $info = $this->languageService->getLocaleInfo($current);

        return response()->json([
            'status' => 'success',
            'data' => [
                'locale' => $current,
                'info' => $info,
                'supported_count' => count($this->languageService->getSupportedLocales()),
            ],
        ]);
    }

    /**
     * Set language
     */
    public function set(LanguageSetRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $locale = $request->validated()['locale'];

        $success = $this->languageService->setLocale($locale, $userId);

        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported locale',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Language changed successfully',
            'data' => [
                'locale' => $locale,
            ],
        ]);
    }

    /**
     * Get supported languages
     */
    public function supported(): JsonResponse
    {
        $locales = $this->languageService->getSupportedLocales();
        $current = $this->languageService->getCurrentLocale();

        $formattedLocales = [];
        foreach ($locales as $code => $info) {
            $formattedLocales[] = [
                'code' => $code,
                'name' => $info['name'],
                'native' => $info['native'],
                'flag' => $info['flag'],
                'is_current' => $code === $current,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'locales' => $formattedLocales,
                'current' => $current,
                'current_info' => $this->languageService->getLocaleInfo($current),
            ],
        ]);
    }
}

