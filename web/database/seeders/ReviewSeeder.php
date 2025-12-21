<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $users = User::where('email', 'like', 'customer%@example.com')->get();

        $reviewTemplates = [
            'Great product! Very satisfied with the quality.',
            'Excellent value for money. Highly recommend!',
            'Good product but could be better.',
            'Amazing quality and fast shipping.',
            'Not bad, but expected more for the price.',
            'Perfect! Exactly as described.',
            'Good product overall, minor issues.',
            'Outstanding quality and service.',
            'Average product, nothing special.',
            'Love it! Will buy again.',
        ];

        foreach ($products as $product) {
            // Mỗi product có 3-8 reviews
            $reviewCount = rand(3, 8);
            $selectedUsers = $users->random(min($reviewCount, $users->count()));

            foreach ($selectedUsers as $user) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(3, 5), // 3-5 stars
                    'content' => $reviewTemplates[array_rand($reviewTemplates)],
                ]);
            }
        }
    }
}


