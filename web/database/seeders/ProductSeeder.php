<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->get();

        // Tạo products cho mỗi category
        foreach ($categories as $category) {
            $this->createProductsForCategory($category, 3);
        }

        // Tạo products cho subcategories
        foreach ($subcategories as $subcategory) {
            $this->createProductsForCategory($subcategory, 5);
        }
    }

    private function createProductsForCategory(Category $category, int $count): void
    {
        $productNames = [
            'Premium Quality Item',
            'Professional Grade Product',
            'Deluxe Edition',
            'Standard Model',
            'Advanced Version',
            'Classic Design',
            'Modern Style',
            'Eco-Friendly Option',
            'Budget Friendly',
            'Luxury Collection',
        ];

        for ($i = 0; $i < $count; $i++) {
            $name = $productNames[array_rand($productNames)] . ' ' . $category->name . ' ' . ($i + 1);
            $slug = Str::slug($name) . '-' . uniqid();

            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'price' => rand(100000, 5000000), // 100k - 5M VND
                'short_description' => "High quality {$category->name} product with excellent features.",
                'description' => "This is a detailed description of the {$name}. It features premium materials, excellent craftsmanship, and comes with a satisfaction guarantee. Perfect for everyday use or special occasions.",
                'thumbnail' => "https://picsum.photos/400/400?random=" . rand(1, 1000),
                'specs' => [
                    'material' => 'Premium Quality',
                    'weight' => rand(100, 2000) . 'g',
                    'dimensions' => rand(10, 50) . 'x' . rand(10, 50) . 'x' . rand(5, 30) . 'cm',
                    'warranty' => rand(6, 24) . ' months',
                ],
            ]);

            // Tạo product images
            $imageCount = rand(2, 5);
            for ($j = 0; $j < $imageCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => "https://picsum.photos/800/800?random=" . rand(1, 1000),
                    'is_main' => $j === 0,
                ]);
            }

            // Tạo product variants
            $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Gray', 'Silver'];
            $variantCount = rand(1, 4);
            $selectedColors = array_slice($colors, 0, $variantCount);

            foreach ($selectedColors as $index => $color) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color' => $color,
                    'stock' => rand(0, 100),
                ]);
            }
        }
    }
}


