<?php

namespace App\Repositories\Contracts;

use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;

interface LanguageRepositoryInterface
{
    /**
     * Get all active languages
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get all languages
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Find language by code
     *
     * @param  string  $code
     * @return Language|null
     */
    public function findByCode(string $code): ?Language;

    /**
     * Get default language
     *
     * @return Language|null
     */
    public function getDefault(): ?Language;

    /**
     * Create language
     *
     * @param  array  $data
     * @return Language
     */
    public function create(array $data): Language;

    /**
     * Update language
     *
     * @param  Language  $language
     * @param  array  $data
     * @return Language
     */
    public function update(Language $language, array $data): Language;
}

