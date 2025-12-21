<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', 'like', 'customer%@example.com')->get();
        $products = Product::all();

        foreach ($users as $user) {
            // Mỗi user có 3-10 items trong wishlist
            $wishlistCount = rand(3, 10);
            $selectedProducts = $products->random(min($wishlistCount, $products->count()));

            foreach ($selectedProducts as $product) {
                WishlistItem::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}


