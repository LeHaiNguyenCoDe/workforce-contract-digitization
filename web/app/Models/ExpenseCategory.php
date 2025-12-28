<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const TYPE_EXPENSE = 'expense';
    const TYPE_INCOME = 'income';

    /**
     * Get transactions in this category
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'category_id');
    }

    /**
     * Scope active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
