<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', 'like', 'customer%@example.com')->get();
        $products = Product::all();

        // Tạo carts cho users đã đăng nhập
        foreach ($users as $user) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'session_id' => null,
            ]);

            // Thêm items vào cart
            $itemCount = rand(1, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $variant = $product->variants()->inRandomOrder()->first();

                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'qty' => rand(1, 3),
                    'price' => $product->price,
                ]);
            }
        }

        // Tạo carts cho session-based (guest users)
        for ($i = 0; $i < 10; $i++) {
            $sessionId = Str::random(40);
            $cart = Cart::create([
                'user_id' => null,
                'session_id' => $sessionId,
            ]);

            // Thêm items vào cart
            $itemCount = rand(1, 4);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $variant = $product->variants()->inRandomOrder()->first();

                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'qty' => rand(1, 2),
                    'price' => $product->price,
                ]);
            }
        }
    }
}


