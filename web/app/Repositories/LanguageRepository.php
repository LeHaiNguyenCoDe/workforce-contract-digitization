<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LanguageRepository implements LanguageRepositoryInterface
{
    /**
     * Get all active languages
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return Language::active()->ordered()->get();
    }

    /**
     * Get all languages
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Language::ordered()->get();
    }

    /**
     * Find language by code
     *
     * @param  string  $code
     * @return Language|null
     */
    public function findByCode(string $code): ?Language
    {
        return Language::where('code', $code)->first();
    }

    /**
     * Get default language
     *
     * @return Language|null
     */
    public function getDefault(): ?Language
    {
        return Language::default()->first();
    }

    /**
     * Create language
     *
     * @param  array  $data
     * @return Language
     */
    public function create(array $data): Language
    {
        return Language::create($data);
    }

    /**
     * Update language
     *
     * @param  Language  $language
     * @param  array  $data
     * @return Language
     */
    public function update(Language $language, array $data): Language
    {
        $language->update($data);
        return $language->fresh();
    }
}

