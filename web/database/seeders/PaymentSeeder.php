<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $providers = ['vnpay', 'momo', 'cod', 'bank_transfer'];
        $statuses = ['pending', 'success', 'failed', 'refunded'];

        foreach ($orders as $order) {
            // Mỗi order có một payment
            $status = $order->payment_method === 'cod'
                ? ($order->status === 'delivered' ? 'success' : 'pending')
                : ($order->status === 'cancelled' ? 'failed' : ($order->status === 'delivered' ? 'success' : 'pending'));

            Payment::create([
                'order_id' => $order->id,
                'provider' => $order->payment_method === 'cod' ? 'cod' : $providers[array_rand($providers)],
                'amount' => (int) $order->total_amount,
                'status' => $status,
                'transaction_id' => $status === 'success' ? 'TXN' . strtoupper(uniqid()) : null,
                'payload' => $status === 'success' ? [
                    'payment_date' => $order->created_at->toDateTimeString(),
                    'method' => $order->payment_method,
                ] : null,
                'created_at' => $order->created_at,
            ]);
        }
    }
}


