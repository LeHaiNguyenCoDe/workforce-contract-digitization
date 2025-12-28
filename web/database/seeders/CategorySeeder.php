<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo categories cha
        $decor = Category::updateOrCreate(
            ['slug' => Str::slug('Gốm Trang Trí')],
            ['name' => 'Gốm Trang Trí']
        );

        $household = Category::updateOrCreate(
            ['slug' => Str::slug('Gốm Gia Dụng')],
            ['name' => 'Gốm Gia Dụng']
        );

        $spiritual = Category::updateOrCreate(
            ['slug' => Str::slug('Gốm Tâm Linh')],
            ['name' => 'Gốm Tâm Linh']
        );

        $art = Category::updateOrCreate(
            ['slug' => Str::slug('Gốm Nghệ Thuật')],
            ['name' => 'Gốm Nghệ Thuật']
        );

        // Tạo subcategories cho Gốm Trang Trí
        Category::updateOrCreate(
            ['slug' => Str::slug('Bình Gốm')],
            [
                'name' => 'Bình Gốm',
                'parent_id' => $decor->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Tranh Gốm')],
            [
                'name' => 'Tranh Gốm',
                'parent_id' => $decor->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Tượng Gốm')],
            [
                'name' => 'Tượng Gốm',
                'parent_id' => $decor->id,
            ]
        );

        // Tạo subcategories cho Gốm Gia Dụng
        Category::updateOrCreate(
            ['slug' => Str::slug('Bộ Ấm Chén')],
            [
                'name' => 'Bộ Ấm Chén',
                'parent_id' => $household->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Bát Đĩa')],
            [
                'name' => 'Bát Đĩa',
                'parent_id' => $household->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Hũ Gạo - Chum Rượu')],
            [
                'name' => 'Hũ Gạo - Chum Rượu',
                'parent_id' => $household->id,
            ]
        );

        // Tạo subcategories cho Gốm Tâm Linh
        Category::updateOrCreate(
            ['slug' => Str::slug('Đồ Thờ Cúng')],
            [
                'name' => 'Đồ Thờ Cúng',
                'parent_id' => $spiritual->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Quà Tặng Tâm Linh')],
            [
                'name' => 'Quà Tặng Tâm Linh',
                'parent_id' => $spiritual->id,
            ]
        );
    }
}

