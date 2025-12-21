<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LanguageDetectionService $detectionService
    ) {
    }


    /**
     * Set locale for current request
     *
     * @param  string  $locale
     * @param  int|null  $userId
     * @return bool
     */
    public function setLocale(string $locale, ?int $userId = null): bool
    {
        if (!$this->detectionService->isSupported($locale)) {
            return false;
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        // Save to user preference if authenticated
        if ($userId) {
            $this->saveUserLanguage($userId, $locale);
        }

        return true;
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get all supported locales
     *
     * @return array
     */
    public function getSupportedLocales(): array
    {
        return $this->detectionService->getSupportedLocales();
    }

    /**
     * Check if locale is supported
     *
     * @param  string  $locale
     * @return bool
     */
    public function isSupported(string $locale): bool
    {
        return $this->detectionService->isSupported($locale);
    }

    /**
     * Get locale info
     *
     * @param  string  $locale
     * @return array|null
     */
    public function getLocaleInfo(string $locale): ?array
    {
        return $this->detectionService->getLocaleInfo($locale);
    }

    /**
     * Save user language preference
     *
     * @param  int  $userId
     * @param  string  $locale
     * @return void
     */
    private function saveUserLanguage(int $userId, string $locale): void
    {
        $user = $this->userRepository->findById($userId);
        if ($user && isset($user->language)) {
            $this->userRepository->update($user, ['language' => $locale]);
        }
    }

    /**
     * Translate message with fallback
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $translation = trans($key, $replace, $locale);

        // If translation not found, return key
        if ($translation === $key) {
            return $key;
        }

        return $translation;
    }
}

