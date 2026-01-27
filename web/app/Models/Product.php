<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'sku',
        'price',
        'min_stock_level',
        'warehouse_type',
        'storage_location',
        'short_description',
        'description',
        'thumbnail',
        'specs',
        'manufacturer_name',
        'manufacturer_brand',
        'stock_quantity',
        'discount_percentage',
        'is_active',
        'published_at',
        'faqs',
    ];

    /**
     * BR-01.1: Product không được chứa thông tin tồn kho
     * BR-01.2: Một Product có thể thuộc nhiều NCC
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected $casts = [
        'specs' => 'array',
        'faqs' => 'array',
        'published_at' => 'datetime',
        'discount_percentage' => 'float',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}


