<?php

namespace App\Helpers;

use App\Services\Core\LanguageService;
use Illuminate\Support\Facades\App;

class LanguageHelper
{
    /**
     * Translate message with auto locale
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return trans($key, $replace, $locale ?? App::getLocale());
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public static function getLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Check if locale is supported
     *
     * @param  string  $locale
     * @return bool
     */
    public static function isSupported(string $locale): bool
    {
        $detectionService = app(\App\Services\LanguageDetectionService::class);
        return $detectionService->isSupported($locale);
    }

    /**
     * Get all supported locales
     *
     * @return array
     */
    public static function getSupportedLocales(): array
    {
        $detectionService = app(\App\Services\LanguageDetectionService::class);
        return $detectionService->getSupportedLocales();
    }

    /**
     * Translation groups to search (in order of priority)
     */
    private static array $translationGroups = [
        'api',        // Legacy - for backward compatibility
        'common',     // Common messages
        'auth',       // Authentication
        'user',       // User management
        'product',    // Products
        'order',      // Orders
        'cart',       // Cart
        'category',   // Categories
        'warehouse',  // Warehouse, inventory, transfer
        'finance',    // Finance, expenses, debt
        'store',      // Store module (articles, promotions, reviews, etc)
    ];

    /**
     * Translate API response message
     * Smart hybrid approach:
     * 1. Search in all translation files/DB
     * 2. Auto-convert key to readable text
     * 3. Real-time Google Translate
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $group  Optional specific group to search in
     * @return string
     */
    public static function apiMessage(string $key, array $replace = [], ?string $group = null): string
    {
        $locale = self::getLocale();
        $translationService = app(\App\Services\Core\TranslationService::class);
        
        // If specific group provided, search only in that group
        $groupsToSearch = $group ? [$group] : self::$translationGroups;
        
        // Try file/DB translation in each group
        foreach ($groupsToSearch as $searchGroup) {
            $translated = $translationService->get($searchGroup, $key, $locale, $replace);
            if ($translated !== $key) {
                return $translated;
            }
        }

        // Auto-discovery: Convert key to readable text and translate
        $defaultLocale = config('app.locale', 'vi');
        
        // Convert snake_case key to readable English text
        $readableText = self::keyToReadableText($key);
        
        // If current locale is not default, translate via Google
        if ($locale !== $defaultLocale && config('services.realtime_translation.enabled', true)) {
            try {
                $rtService = app(\App\Services\Core\RealTimeTranslationService::class);
                return $rtService->translate($readableText, $locale, 'en');
            } catch (\Exception $e) {
                \Log::warning("Real-time translation failed: {$e->getMessage()}");
            }
        }
        
        // For default locale (vi), translate from English
        if ($locale === 'vi' && config('services.realtime_translation.enabled', true)) {
            try {
                $rtService = app(\App\Services\Core\RealTimeTranslationService::class);
                return $rtService->translate($readableText, 'vi', 'en');
            } catch (\Exception $e) {
                \Log::warning("Real-time translation failed: {$e->getMessage()}");
            }
        }
        
        // Final fallback: return readable text
        return $readableText;
    }

    /**
     * Convert snake_case key to readable text
     * e.g., 'product_created_successfully' → 'Product created successfully'
     *
     * @param string $key
     * @return string
     */
    public static function keyToReadableText(string $key): string
    {
        // Replace underscores with spaces
        $text = str_replace('_', ' ', $key);
        
        // Capitalize first letter
        $text = ucfirst($text);
        
        return $text;
    }

    /**
     * Translate validation message
     *
     * @param  string  $key
     * @param  array  $replace
     * @return string
     */
    public static function validation(string $key, array $replace = []): string
    {
        $locale = self::getLocale();
        $translationService = app(\App\Services\Core\TranslationService::class);
        
        return $translationService->get('validation', $key, $locale, $replace);
    }

    /**
     * Translate model name
     *
     * @param  string  $model
     * @return string
     */
    public static function model(string $model): string
    {
        $locale = self::getLocale();
        $translationService = app(\App\Services\Core\TranslationService::class);
        
        return $translationService->get('models', $model, $locale);
    }
}


