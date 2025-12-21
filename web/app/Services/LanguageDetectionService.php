<?php

namespace App\Services;

use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageDetectionService
{
    /**
     * Default locale (fallback only if DB is empty)
     */
    private const DEFAULT_LOCALE = 'vi';

    public function __construct(
        private LanguageRepositoryInterface $languageRepository
    ) {
    }


    /**
     * Detect locale from request with multiple strategies
     *
     * @param  Request  $request
     * @param  int|null  $userId
     * @return string
     */
    public function detect(Request $request, ?int $userId = null): string
    {
        // Strategy 1: Query parameter (highest priority)
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if ($lang && is_string($lang)) {
                $locale = $this->normalizeLocale($lang);
            if ($this->isSupported($locale)) {
                return $locale;
                }
            }
        }

        // Strategy 2: Session
        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if ($sessionLocale && is_string($sessionLocale)) {
                $locale = $this->normalizeLocale($sessionLocale);
            if ($this->isSupported($locale)) {
                return $locale;
                }
            }
        }

        // Strategy 3: User preference (if authenticated)
        if ($userId) {
            $userLocale = $this->getUserLocale($userId);
            if ($userLocale && $this->isSupported($userLocale)) {
                return $userLocale;
            }
        }

        // Strategy 4: Accept-Language header (browser detection)
        $headerLocale = $this->detectFromHeader($request);
        if ($headerLocale) {
            return $headerLocale;
        }

        // Strategy 5: IP-based detection (optional, can be enabled)
        // $ipLocale = $this->detectFromIP($request->ip());
        // if ($ipLocale) {
        //     return $ipLocale;
        // }

        // Strategy 6: Default locale
        return self::DEFAULT_LOCALE;
    }

    /**
     * Detect locale from Accept-Language header
     *
     * @param  Request  $request
     * @return string|null
     */
    private function detectFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        // Example: "en-US,en;q=0.9,vi;q=0.8,fr;q=0.7"
        $languages = $this->parseAcceptLanguage($acceptLanguage);

        foreach ($languages as $language => $quality) {
            if (empty($language) || !is_string($language)) {
                continue;
            }
            
            $locale = $this->normalizeLocale($language);

            // Try exact match first
            if ($this->isSupported($locale)) {
                return $locale;
            }

            // Try language code only (e.g., 'en' from 'en-US')
            $langCode = substr($locale, 0, 2);
            if ($this->isSupported($langCode)) {
                return $langCode;
            }
        }

        return null;
    }

    /**
     * Parse Accept-Language header
     *
     * @param  string  $acceptLanguage
     * @return array
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $parts = explode(',', $acceptLanguage);

        foreach ($parts as $part) {
            $part = trim($part);
            $quality = 1.0;

            // Extract quality value if present
            if (strpos($part, ';q=') !== false) {
                [$language, $quality] = explode(';q=', $part);
                $quality = (float) $quality;
            } else {
                $language = $part;
            }

            $languages[trim($language)] = $quality;
        }

        // Sort by quality (descending)
        arsort($languages);

        return $languages;
    }

    /**
     * Normalize locale code
     *
     * @param  string  $locale
     * @return string
     */
    private function normalizeLocale(string $locale): string
    {
        // Handle null or empty
        if (empty($locale) || !is_string($locale)) {
            return self::DEFAULT_LOCALE;
        }

        // Convert to lowercase
        $locale = strtolower(trim($locale));

        // Extract language code (e.g., 'en' from 'en-US')
        if (strpos($locale, '-') !== false) {
            $locale = explode('-', $locale)[0];
        }

        // Handle special cases
        $mappings = [
            'zh-cn' => 'zh',
            'zh-tw' => 'zh',
            'pt-br' => 'pt',
            'pt-pt' => 'pt',
        ];

        return $mappings[$locale] ?? $locale;
    }

    /**
     * Check if locale is supported
     *
     * @param  string  $locale
     * @return bool
     */
    public function isSupported(string $locale): bool
    {
        $locales = $this->getSupportedLocalesArray();
        return array_key_exists($locale, $locales);
    }

    /**
     * Get all supported locales
     *
     * @return array
     */
    public function getSupportedLocales(): array
    {
        return $this->getSupportedLocalesArray();
    }

    /**
     * Get supported locales from database (cached)
     *
     * @return array
     */
    private function getSupportedLocalesArray(): array
    {
        return Cache::remember('supported_locales', 3600, function () {
            $languages = $this->languageRepository->getActive();
            $locales = [];

            foreach ($languages as $language) {
                $locales[$language->code] = [
                    'name' => $language->name,
                    'native' => $language->native_name,
                    'flag' => $language->flag ?? '',
                ];
            }

            return $locales;
        });
    }

    /**
     * Get default locale
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return Cache::remember('default_locale', 3600, function () {
            $default = $this->languageRepository->getDefault();
            return $default ? $default->code : self::DEFAULT_LOCALE;
        });
    }

    /**
     * Get user locale from database
     *
     * @param  int  $userId
     * @return string|null
     */
    private function getUserLocale(int $userId): ?string
    {
        return Cache::remember("user_locale_{$userId}", 3600, function () use ($userId) {
            $userRepository = app(\App\Repositories\Contracts\UserRepositoryInterface::class);
            $user = $userRepository->findById($userId);
            return $user?->language;
        });
    }

    /**
     * Get locale info
     *
     * @param  string  $locale
     * @return array|null
     */
    public function getLocaleInfo(string $locale): ?array
    {
        if (!$this->isSupported($locale)) {
            return null;
        }

        $locales = $this->getSupportedLocalesArray();
        return array_merge(
            $locales[$locale],
            ['code' => $locale]
        );
    }

    /**
     * Detect locale from IP address (optional feature)
     * This would require a geolocation service
     *
     * @param  string  $ip
     * @return string|null
     */
    private function detectFromIP(string $ip): ?string
    {
        // This is a placeholder - you would integrate with a geolocation API
        // For example: ipapi.co, ip-api.com, or MaxMind GeoIP
        return null;
    }
}

