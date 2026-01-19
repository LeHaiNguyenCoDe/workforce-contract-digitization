<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPartner extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo',
        'api_key',
        'api_secret',
        'api_url',
        'is_active',
        'config',
        'supported_services',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'supported_services' => 'array',
    ];

    // Scope for active partners
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
