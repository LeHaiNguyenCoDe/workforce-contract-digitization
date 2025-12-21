<?php

namespace App\Repositories\Contracts;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;

interface TranslationRepositoryInterface
{
    /**
     * Get translation by group, key and locale
     *
     * @param  string  $group
     * @param  string  $key
     * @param  string  $locale
     * @return Translation|null
     */
    public function get(string $group, string $key, string $locale): ?Translation;

    /**
     * Get all translations by group and locale
     *
     * @param  string  $group
     * @param  string  $locale
     * @return Collection
     */
    public function getByGroup(string $group, string $locale): Collection;

    /**
     * Create or update translation
     *
     * @param  array  $data
     * @return Translation
     */
    public function createOrUpdate(array $data): Translation;

    /**
     * Get all translations for a locale
     *
     * @param  string  $locale
     * @return Collection
     */
    public function getByLocale(string $locale): Collection;
}

