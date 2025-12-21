<?php

namespace App\Repositories;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TranslationRepository implements TranslationRepositoryInterface
{
    /**
     * Get translation by group, key and locale
     *
     * @param  string  $group
     * @param  string  $key
     * @param  string  $locale
     * @return Translation|null
     */
    public function get(string $group, string $key, string $locale): ?Translation
    {
        try {
            return Translation::active()
                ->where('group', $group)
                ->where('key', $key)
                ->where('locale', $locale)
                ->first();
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, return null to use file-based fallback
            return null;
        }
    }

    /**
     * Get all translations by group and locale
     *
     * @param  string  $group
     * @param  string  $locale
     * @return Collection
     */
    public function getByGroup(string $group, string $locale): Collection
    {
        try {
            return Translation::active()
                ->byGroup($group)
                ->byLocale($locale)
                ->get();
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, return empty collection
            return new Collection();
        }
    }

    /**
     * Create or update translation
     *
     * @param  array  $data
     * @return Translation
     */
    public function createOrUpdate(array $data): Translation
    {
        return Translation::updateOrCreate(
            [
                'group' => $data['group'],
                'key' => $data['key'],
                'locale' => $data['locale'],
            ],
            [
                'value' => $data['value'],
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    /**
     * Get all translations for a locale
     *
     * @param  string  $locale
     * @return Collection
     */
    public function getByLocale(string $locale): Collection
    {
        try {
            return Translation::active()->byLocale($locale)->get();
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, return empty collection
            return new Collection();
        }
    }
}

