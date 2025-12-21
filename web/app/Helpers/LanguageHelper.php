<?php

namespace App\Helpers;

use App\Services\LanguageService;
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
     * Translate API response message
     *
     * @param  string  $key
     * @param  array  $replace
     * @return string
     */
    public static function apiMessage(string $key, array $replace = []): string
    {
        $locale = self::getLocale();
        $translationService = app(\App\Services\TranslationService::class);
        
        return $translationService->get('api', $key, $locale, $replace);
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
        $translationService = app(\App\Services\TranslationService::class);
        
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
        $translationService = app(\App\Services\TranslationService::class);
        
        return $translationService->get('models', $model, $locale);
    }
}

