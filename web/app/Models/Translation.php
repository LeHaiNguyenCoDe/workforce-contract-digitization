<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group',
        'key',
        'locale',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active translations
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get translations by group
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $group
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get translations by locale
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}

