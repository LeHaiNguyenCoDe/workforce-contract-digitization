<?php

namespace App\Services;

use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationService
{
    public function __construct(
        private TranslationRepositoryInterface $translationRepository
    ) {
    }

    /**
     * Get translation with fallback chain
     * 1. Database (cached)
     * 2. File-based
     * 3. Default locale
     * 4. Key itself
     *
     * @param  string  $group
     * @param  string  $key
     * @param  string  $locale
     * @param  array  $replace
     * @return string
     */
    public function get(string $group, string $key, string $locale, array $replace = []): string
    {
        $cacheKey = "translation_{$group}_{$key}_{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $locale, $replace) {
            // 1. Try database first
            $translation = $this->translationRepository->get($group, $key, $locale);
            if ($translation && $translation->is_active) {
                return $this->replacePlaceholders($translation->value, $replace);
            }

            // 2. Try file-based translation
            $fileTranslation = Lang::get("{$group}.{$key}", $replace, $locale);
            if ($fileTranslation !== "{$group}.{$key}") {
                return $fileTranslation;
            }

            // 3. Try default locale
            $defaultLocale = app(LanguageDetectionService::class)->getDefaultLocale();
            if ($locale !== $defaultLocale) {
                $defaultTranslation = $this->translationRepository->get($group, $key, $defaultLocale);
                if ($defaultTranslation && $defaultTranslation->is_active) {
                    return $this->replacePlaceholders($defaultTranslation->value, $replace);
                }

                $defaultFileTranslation = Lang::get("{$group}.{$key}", $replace, $defaultLocale);
                if ($defaultFileTranslation !== "{$group}.{$key}") {
                    return $defaultFileTranslation;
                }
            }

            // 4. Return key if all fail
            return $key;
        });
    }

    /**
     * Replace placeholders in translation
     *
     * @param  string  $text
     * @param  array  $replace
     * @return string
     */
    private function replacePlaceholders(string $text, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $text = str_replace(":{$key}", $value, $text);
            $text = str_replace("{{{$key}}}", $value, $text);
        }

        return $text;
    }

    /**
     * Import translations from language files to database
     *
     * @param  string|null  $locale
     * @return int
     */
    public function importFromFiles(?string $locale = null): int
    {
        $langPath = lang_path();
        $imported = 0;

        if (!File::exists($langPath)) {
            return 0;
        }

        $locales = $locale ? [$locale] : File::directories($langPath);

        foreach ($locales as $localePath) {
            $currentLocale = $locale ? $locale : basename($localePath);
            $files = File::files($localePath);

            foreach ($files as $file) {
                $group = $file->getFilenameWithoutExtension();
                $translations = require $file->getPathname();

                if (is_array($translations)) {
                    $imported += $this->importTranslations($group, $translations, $currentLocale);
                }
            }
        }

        // Clear cache after import
        Cache::flush();

        return $imported;
    }

    /**
     * Import translations recursively
     *
     * @param  string  $group
     * @param  array  $translations
     * @param  string  $locale
     * @param  string  $prefix
     * @return int
     */
    private function importTranslations(string $group, array $translations, string $locale, string $prefix = ''): int
    {
        $imported = 0;

        foreach ($translations as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $imported += $this->importTranslations($group, $value, $locale, $fullKey);
            } else {
                $this->translationRepository->createOrUpdate([
                    'group' => $group,
                    'key' => $fullKey,
                    'locale' => $locale,
                    'value' => $value,
                    'is_active' => true,
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Export translations from database to files
     *
     * @param  string|null  $locale
     * @return int
     */
    public function exportToFiles(?string $locale = null): int
    {
        $exported = 0;

        if ($locale) {
            $translations = $this->translationRepository->getByLocale($locale);
        } else {
            $translations = $this->translationRepository->getByLocale('*');
        }

        $grouped = $translations->groupBy('group');

        foreach ($grouped as $group => $groupTranslations) {
            $localeTranslations = $groupTranslations->groupBy('locale');

            foreach ($localeTranslations as $currentLocale => $localeGroup) {
                $langPath = lang_path("{$currentLocale}/{$group}.php");
                $array = [];

                foreach ($localeGroup as $translation) {
                    $this->setNestedKey($array, $translation->key, $translation->value);
                }

                $content = "<?php\n\nreturn " . var_export($array, true) . ";\n";
                File::put($langPath, $content);
                $exported += $localeGroup->count();
            }
        }

        return $exported;
    }

    /**
     * Set nested array key
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    private function setNestedKey(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Sync: Import from files and ensure database is up to date
     *
     * @return array
     */
    public function sync(): array
    {
        $imported = $this->importFromFiles();
        $exported = $this->exportToFiles();

        Cache::flush();

        return [
            'imported' => $imported,
            'exported' => $exported,
        ];
    }
}

