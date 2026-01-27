<?php

namespace App\Http\Controllers\Modules\Landing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Landing\LanguageSetRequest;
use App\Services\Core\LanguageService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private LanguageService $languageService
    ) {
    }

    public function current(): JsonResponse
    {
        $current = $this->languageService->getCurrentLocale();
        $info = $this->languageService->getLocaleInfo($current);

        return $this->successResponse([
            'locale' => $current,
            'info' => $info,
            'supported_count' => count($this->languageService->getSupportedLocales()),
        ]);
    }

    public function set(LanguageSetRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $locale = $request->validated()['locale'];

        $success = $this->languageService->setLocale($locale, $userId);

        if (!$success) {
            return $this->errorResponse('unsupported_locale', null, 400);
        }

        return $this->successResponse(['locale' => $locale], 'language_changed');
    }

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

        return $this->successResponse([
            'locales' => $formattedLocales,
            'current' => $current,
            'current_info' => $this->languageService->getLocaleInfo($current),
        ]);
    }
}




