<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::where('status', '!=', 'cancelled')->get();
        $carriers = ['GHN', 'GHTK', 'Viettel Post', 'J&T Express', 'Ninja Van'];
        $statusMap = [
            'pending' => 'pending',
            'processing' => 'picking',
            'shipped' => 'shipping',
            'delivered' => 'delivered',
        ];

        foreach ($orders as $order) {
            $shipmentStatus = $statusMap[$order->status] ?? 'pending';
            $hasTrackingCode = in_array($shipmentStatus, ['shipping', 'delivered']);

            Shipment::create([
                'order_id' => $order->id,
                'carrier' => $carriers[array_rand($carriers)],
                'tracking_code' => $hasTrackingCode ? 'TRK' . strtoupper(uniqid()) : null,
                'status' => $shipmentStatus,
                'shipping_fee' => rand(20000, 50000), // 20k - 50k VND
                'estimated_delivery_date' => $order->status === 'delivered' 
                    ? $order->created_at->addDays(rand(2, 5))
                    : now()->addDays(rand(2, 5)),
                'created_at' => $order->created_at,
            ]);
        }
    }
}


