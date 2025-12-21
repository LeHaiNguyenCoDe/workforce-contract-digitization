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
        $electronics = Category::updateOrCreate(
            ['slug' => Str::slug('Electronics')],
            ['name' => 'Electronics']
        );

        $clothing = Category::updateOrCreate(
            ['slug' => Str::slug('Clothing')],
            ['name' => 'Clothing']
        );

        $books = Category::updateOrCreate(
            ['slug' => Str::slug('Books')],
            ['name' => 'Books']
        );

        $home = Category::updateOrCreate(
            ['slug' => Str::slug('Home & Garden')],
            ['name' => 'Home & Garden']
        );

        $sports = Category::updateOrCreate(
            ['slug' => Str::slug('Sports & Outdoors')],
            ['name' => 'Sports & Outdoors']
        );

        // Tạo subcategories cho Electronics
        Category::updateOrCreate(
            ['slug' => Str::slug('Smartphones')],
            [
                'name' => 'Smartphones',
                'parent_id' => $electronics->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Laptops')],
            [
                'name' => 'Laptops',
                'parent_id' => $electronics->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Tablets')],
            [
                'name' => 'Tablets',
                'parent_id' => $electronics->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Electronics Accessories')],
            [
                'name' => 'Accessories',
                'parent_id' => $electronics->id,
            ]
        );

        // Tạo subcategories cho Clothing
        Category::updateOrCreate(
            ['slug' => Str::slug('Mens Clothing')],
            [
                'name' => 'Men\'s Clothing',
                'parent_id' => $clothing->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Womens Clothing')],
            [
                'name' => 'Women\'s Clothing',
                'parent_id' => $clothing->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Kids Clothing')],
            [
                'name' => 'Kids\' Clothing',
                'parent_id' => $clothing->id,
            ]
        );

        // Tạo subcategories cho Books
        Category::updateOrCreate(
            ['slug' => Str::slug('Fiction Books')],
            [
                'name' => 'Fiction',
                'parent_id' => $books->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Non-Fiction Books')],
            [
                'name' => 'Non-Fiction',
                'parent_id' => $books->id,
            ]
        );

        Category::updateOrCreate(
            ['slug' => Str::slug('Educational Books')],
            [
                'name' => 'Educational',
                'parent_id' => $books->id,
            ]
        );
    }
}

