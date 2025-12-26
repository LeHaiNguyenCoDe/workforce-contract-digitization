<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo warehouses
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'address' => '123 Warehouse Street, Ho Chi Minh City',
                'is_active' => true,
            ],
            [
                'name' => 'North Warehouse',
                'code' => 'WH002',
                'address' => '456 Storage Road, Hanoi',
                'is_active' => true,
            ],
            [
                'name' => 'Central Warehouse',
                'code' => 'WH003',
                'address' => '789 Distribution Center, Da Nang',
                'is_active' => true,
            ],
        ];

        $createdWarehouses = [];
        foreach ($warehouses as $warehouse) {
            $createdWarehouses[] = Warehouse::updateOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
        }

        $products = Product::all();

        // Tạo stocks và stock movements cho mỗi warehouse
        foreach ($createdWarehouses as $warehouse) {
            foreach ($products as $product) {
                // Tạo stock cho product (không có variant)
                $quantity = rand(50, 500);
                $availableQty = rand((int)($quantity * 0.7), $quantity);
                $stock = Stock::updateOrCreate(
                    [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    ],
                    [
                    'quantity' => $quantity,
                    'available_quantity' => $availableQty,
                    ]
                );

                // Tạo initial stock movement
                StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'reference_type' => 'manual',
                    'note' => 'Initial stock',
                    'created_at' => now()->subDays(rand(30, 60)),
                ]);

                // Tạo stocks cho variants
                foreach ($product->variants as $variant) {
                    $variantQuantity = rand(10, 100);
                    Stock::firstOrCreate(
                        [
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        ],
                        [
                        'quantity' => $variantQuantity,
                        ]
                    );

                    // Tạo initial stock movement cho variant
                    StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'quantity' => $variantQuantity,
                        'reference_type' => 'manual',
                        'note' => 'Initial stock for variant',
                        'created_at' => now()->subDays(rand(30, 60)),
                    ]);
                }

                // Tạo một số stock movements khác (out, adjust)
                $movementCount = rand(2, 5);
                for ($i = 0; $i < $movementCount; $i++) {
                    $movementType = ['out', 'adjust'][rand(0, 1)];
                    $movementQty = rand(5, 50);

                    StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'type' => $movementType,
                        'quantity' => $movementType === 'out' ? -$movementQty : $movementQty,
                        'reference_type' => $movementType === 'out' ? 'order' : 'manual',
                        'note' => $movementType === 'out' ? 'Stock out for order' : 'Stock adjustment',
                        'created_at' => now()->subDays(rand(1, 25)),
                    ]);
                }
            }
        }
    }
}

