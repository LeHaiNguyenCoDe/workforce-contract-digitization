<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockDataSeeder extends Seeder
{
    /**
     * Seed sample stock data for inventory view
     */
    public function run(): void
    {
        // Get warehouses and products
        $warehouses = DB::table('warehouses')->get();
        $products = DB::table('products')->get();

        if ($warehouses->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No warehouses or products found. Please seed warehouses and products first.');
            return;
        }

        // Get or create inbound batches for linking
        $batches = DB::table('inbound_batches')->get();
        
        $statuses = ['available', 'reserved', 'damaged', 'hold'];
        $batchIndex = 0;

        foreach ($warehouses as $warehouse) {
            foreach ($products->take(10) as $product) { // Limit to 10 products per warehouse
                $quantity = rand(50, 500);
                $availableQty = rand((int)($quantity * 0.7), $quantity);
                
                // Cycle through batches if available
                $batchId = null;
                if ($batches->isNotEmpty()) {
                    $batchId = $batches[$batchIndex % $batches->count()]->id;
                    $batchIndex++;
                }
                
                // Random expiry date (30-180 days from now)
                $expiryDate = Carbon::now()->addDays(rand(30, 180))->toDateString();
                
                // Random status
                $status = $statuses[array_rand($statuses)];

                // Check if stock already exists
                $existingStock = DB::table('stocks')
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->first();

                $data = [
                    'quantity' => $quantity,
                    'available_quantity' => $availableQty,
                    'updated_at' => now(),
                ];

                if ($existingStock) {
                    DB::table('stocks')
                        ->where('id', $existingStock->id)
                        ->update([
                            'quantity' => $quantity,
                            'available_quantity' => $availableQty,
                            'inbound_batch_id' => $batchId,
                            'expiry_date' => $expiryDate,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('stocks')->insert([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'quantity' => $quantity,
                        'available_quantity' => $availableQty,
                        'inbound_batch_id' => $batchId,
                        'expiry_date' => $expiryDate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Stock data seeded successfully!');
    }
}
