<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;
use Stichoza\GoogleTranslate\GoogleTranslate;

class RealTimeTranslationService
{
    private ?GoogleTranslate $translator = null;
    private string $defaultLocale = 'vi';

    /**
     * Translate text to target locale with caching
     * 
     * @param string $text Text to translate
     * @param string $targetLocale Target locale (e.g., 'ja', 'ko')
     * @param string|null $sourceLocale Source locale (auto-detect if null)
     * @return string Translated text
     */
    public function translate(string $text, string $targetLocale, ?string $sourceLocale = null): string
    {
        // Skip if target is same as source or default
        if ($targetLocale === $sourceLocale || $targetLocale === $this->defaultLocale) {
            return $text;
        }

        // Skip empty text
        if (empty(trim($text))) {
            return $text;
        }

        // Check cache first
        $cacheKey = $this->getCacheKey($text, $targetLocale);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Translate via Google
        try {
            $translated = $this->getTranslator()
                ->setSource($sourceLocale)
                ->setTarget($targetLocale)
                ->translate($text);

            // Cache for 24 hours
            Cache::put($cacheKey, $translated, 86400);

            return $translated;
        } catch (\Exception $e) {
            // Log error and return original text
            \Log::warning("Translation failed: {$e->getMessage()}", [
                'text' => $text,
                'target' => $targetLocale,
            ]);
            return $text;
        }
    }

    /**
     * Batch translate multiple texts
     */
    public function translateBatch(array $texts, string $targetLocale, ?string $sourceLocale = null): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate($text, $targetLocale, $sourceLocale);
            usleep(50000); // 50ms delay between requests
        }
        return $results;
    }

    /**
     * Get Google Translate instance
     */
    private function getTranslator(): GoogleTranslate
    {
        if ($this->translator === null) {
            $this->translator = new GoogleTranslate();
        }
        return $this->translator;
    }

    /**
     * Generate cache key for translation
     */
    private function getCacheKey(string $text, string $targetLocale): string
    {
        return 'rt_trans_' . md5($text) . '_' . $targetLocale;
    }

    /**
     * Clear translation cache
     */
    public function clearCache(?string $targetLocale = null): void
    {
        if ($targetLocale) {
            // Clear specific locale cache would require tracking keys
            // For now, just flush all translation cache
            Cache::flush();
        } else {
            Cache::flush();
        }
    }
}
