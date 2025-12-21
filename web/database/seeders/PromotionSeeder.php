<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionItem;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $categories = Category::all();

        // Tạo promotions
        $promotions = [
            [
                'name' => 'Summer Sale',
                'code' => 'SUMMER2024',
                'type' => 'percent',
                'value' => 20, // 20%
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'is_active' => true,
            ],
            [
                'name' => 'New Customer Discount',
                'code' => 'NEWCUSTOMER',
                'type' => 'fixed_amount',
                'value' => 50000, // 50k VND
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'name' => 'Flash Sale',
                'code' => 'FLASH50',
                'type' => 'percent',
                'value' => 50, // 50%
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(1),
                'is_active' => true,
            ],
            [
                'name' => 'Category Discount',
                'code' => null,
                'type' => 'percent',
                'value' => 15, // 15%
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(15),
                'is_active' => true,
            ],
            [
                'name' => 'Bulk Purchase',
                'code' => 'BULK10',
                'type' => 'percent',
                'value' => 10, // 10%
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(10),
                'is_active' => true,
            ],
        ];

        foreach ($promotions as $promoData) {
            $code = $promoData['code'] ?? null;
            $promotion = Promotion::updateOrCreate(
                $code ? ['code' => $code] : ['name' => $promoData['name']],
                $promoData
            );

            // Tạo promotion items
            if ($promotion->code === 'FLASH50') {
                // Flash sale áp dụng cho một số products cụ thể
                $selectedProducts = $products->random(5);
                foreach ($selectedProducts as $product) {
                    PromotionItem::create([
                        'promotion_id' => $promotion->id,
                        'product_id' => $product->id,
                        'min_qty' => 1,
                    ]);
                }
            } elseif ($promotion->code === null) {
                // Category discount áp dụng cho một số categories
                $selectedCategories = $categories->random(3);
                foreach ($selectedCategories as $category) {
                    PromotionItem::create([
                        'promotion_id' => $promotion->id,
                        'category_id' => $category->id,
                        'min_qty' => 1,
                    ]);
                }
            } elseif ($promotion->code === 'BULK10') {
                // Bulk purchase - áp dụng khi mua từ 3 items trở lên
                $selectedProducts = $products->random(10);
                foreach ($selectedProducts as $product) {
                    PromotionItem::create([
                        'promotion_id' => $promotion->id,
                        'product_id' => $product->id,
                        'min_qty' => 3,
                    ]);
                }
            } else {
                // Các promotion khác áp dụng cho tất cả products
                // Không cần tạo promotion items, sẽ áp dụng global
            }
        }
    }
}

