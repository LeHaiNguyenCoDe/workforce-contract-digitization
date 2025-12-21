<?php

namespace Database\Seeders;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', 'like', 'customer%@example.com')->get();

        foreach ($users as $user) {
            // Tạo loyalty account
            $account = LoyaltyAccount::create([
                'user_id' => $user->id,
                'points' => 0,
            ]);

            // Tạo transactions dựa trên orders của user
            $orders = Order::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->get();

            $totalPoints = 0;

            foreach ($orders as $order) {
                // Tính points: 1 point per 10,000 VND
                $pointsEarned = (int)($order->total_amount / 10000);

                if ($pointsEarned > 0) {
                    $totalPoints += $pointsEarned;

                    LoyaltyTransaction::create([
                        'loyalty_account_id' => $account->id,
                        'points' => $pointsEarned,
                        'type' => 'earn',
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'note' => "Points earned from order {$order->code}",
                        'created_at' => $order->created_at,
                    ]);
                }
            }

            // Tạo một số transactions khác (redeem, adjust)
            if ($totalPoints > 0) {
                // Một số redeem transactions
                $redeemCount = rand(0, 2);
                for ($i = 0; $i < $redeemCount && $totalPoints > 1000; $i++) {
                    $redeemPoints = rand(500, min(1000, $totalPoints));
                    $totalPoints -= $redeemPoints;

                    LoyaltyTransaction::create([
                        'loyalty_account_id' => $account->id,
                        'points' => -$redeemPoints,
                        'type' => 'redeem',
                        'reference_type' => 'manual',
                        'note' => 'Points redeemed for discount',
                        'created_at' => now()->subDays(rand(1, 20)),
                    ]);
                }

                // Cập nhật total points
                $account->update(['points' => $totalPoints]);
            } else {
                // Nếu không có orders, tạo một số points ban đầu
                $initialPoints = rand(100, 500);
                $totalPoints = $initialPoints;

                LoyaltyTransaction::create([
                    'loyalty_account_id' => $account->id,
                    'points' => $initialPoints,
                    'type' => 'adjust',
                    'reference_type' => 'manual',
                    'note' => 'Initial points adjustment',
                ]);

                $account->update(['points' => $totalPoints]);
            }
        }
    }
}


