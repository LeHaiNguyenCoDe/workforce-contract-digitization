<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'sku' => strtoupper($this->faker->unique()->bothify('PROD-####-????')),
            'price' => $this->faker->numberBetween(100, 10000) * 1000,
            'min_stock_level' => 10,
            'is_active' => true,
            'warehouse_type' => 'new',
            'stock_quantity' => 0,
            'category_id' => \App\Models\Category::factory(),
            'supplier_id' => \App\Models\Supplier::factory(),
        ];
    }
}
