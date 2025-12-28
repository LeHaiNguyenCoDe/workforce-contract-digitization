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
            'Bình Gốm',
            'Ấm Trà',
            'Đĩa Sứ',
            'Tượng Nghệ Thuật',
            'Bộ Bát Đĩa',
            'Chậu Hoa',
            'Hũ Đựng',
            'Tranh Gốm',
        ];

        for ($i = 0; $i < $count; $i++) {
            $baseName = $productNames[array_rand($productNames)];
            $name = "{$baseName} {$category->name} " . ($i + 1);
            $slug = Str::slug($name) . '-' . uniqid();

            $ceramicImages = [
                'https://images.unsplash.com/photo-1578749553375-35071f65682b?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1565191999001-551c187427bb?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1593166541910-f199347d9595?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1544259217-061078c1df24?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1525414674753-48b4e7052994?auto=format&fit=crop&q=80&w=800',
                'https://images.unsplash.com/photo-1613274554329-70f997f5789f?auto=format&fit=crop&q=80&w=800',
            ];

            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'price' => rand(200000, 15000000), // 200k - 15M VND
                'short_description' => "Sản phẩm {$category->name} được làm thủ công từ nghệ nhân xưởng gốm làng nghề.",
                'description' => "Sản phẩm {$name} được tạo hình tỉ mỉ, nung ở nhiệt độ cao trên 1200 độ C. Lớp men sáng bóng, bền màu theo thời gian. Thích hợp cho trang trí không gian sang trọng hoặc làm quà tặng ý nghĩa.",
                'thumbnail' => $ceramicImages[array_rand($ceramicImages)],
                'specs' => [
                    'chất_liệu' => 'Gốm sứ cao cấp',
                    'nhiệt_độ_nung' => '1200 - 1300 độ C',
                    'xuất_xứ' => 'Việt Nam',
                    'bảo_hành' => 'Trọn đời về màu men',
                ],
            ]);

            // Tạo product images
            $imageCount = rand(2, 5);
            for ($j = 0; $j < $imageCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $ceramicImages[array_rand($ceramicImages)],
                    'is_main' => $j === 0,
                ]);
            }

            // Tạo product variants (Sử dụng loại men làm variant)
            $types = ['Men Xanh', 'Men Rạn', 'Men Ngọc', 'Gốm Thô', 'Vẽ Vàng'];
            $variantCount = rand(1, 3);
            $selectedTypes = (array) array_rand(array_flip($types), $variantCount);

            foreach ($selectedTypes as $type) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color' => $type,
                    'stock' => rand(5, 50),
                ]);
            }
        }
    }
}


