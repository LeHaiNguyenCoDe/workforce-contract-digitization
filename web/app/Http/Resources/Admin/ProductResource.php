<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier?->name,
            'stock_quantity' => (int) $this->stock_quantity,
            'min_stock_level' => (int) $this->min_stock_level,
            'warehouse_type' => $this->warehouse_type,
            'is_active' => (bool) $this->is_active,
            'thumbnail' => $this->thumbnail,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'specs' => $this->specs,
            'faqs' => $this->faqs,
            'manufacturer_name' => $this->manufacturer_name,
            'manufacturer_brand' => $this->manufacturer_brand,
            'discount_percentage' => (float) $this->discount_percentage,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'images' => $this->whenLoaded('images'),
            'variants' => $this->whenLoaded('variants'),
            'category' => $this->whenLoaded('category'),
            'supplier' => $this->whenLoaded('supplier'),
        ];
    }
}
