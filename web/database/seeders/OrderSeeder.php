<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', 'like', 'customer%@example.com')->get();
        $products = Product::all();
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['cod', 'credit_card', 'bank_transfer', 'e_wallet'];

        // Tạo orders cho mỗi user
        foreach ($users as $user) {
            $orderCount = rand(1, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'code' => 'ORD' . strtoupper(Str::random(8)),
                    'full_name' => $user->name,
                    'phone' => '0' . rand(100000000, 999999999),
                    'email' => $user->email,
                    'address_line' => rand(100, 999) . ' Street Name',
                    'ward' => 'Ward ' . rand(1, 20),
                    'district' => 'District ' . rand(1, 10),
                    'province' => ['Ho Chi Minh', 'Hanoi', 'Da Nang', 'Hai Phong'][rand(0, 3)],
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => $statuses[array_rand($statuses)],
                    'total_amount' => 0, // Sẽ được tính sau
                    'note' => rand(0, 1) ? 'Please deliver in the morning.' : null,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                // Tạo order items
                $itemCount = rand(1, 5);
                $selectedProducts = $products->random(min($itemCount, $products->count()));
                $totalAmount = 0;

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $price = $product->price;
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;

                    // Chọn variant ngẫu nhiên nếu có
                    $variant = $product->variants()->inRandomOrder()->first();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Cập nhật total_amount
                $order->update(['total_amount' => $totalAmount]);
            }
        }
    }
}


