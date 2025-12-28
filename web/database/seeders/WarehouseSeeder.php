<?php

namespace Database\Seeders;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
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
                'name' => 'Kho Lò Nung - Bát Tràng',
                'code' => 'WH001',
                'address' => 'Xóm 2, Bát Tràng, Hà Nội',
                'is_active' => true,
            ],
            [
                'name' => 'Kho Thành Phẩm - Minh Long',
                'code' => 'WH002',
                'address' => 'Thuận An, Bình Dương',
                'is_active' => true,
            ],
            [
                'name' => 'Kho Trưng Bày - Chu Đậu',
                'code' => 'WH003',
                'address' => 'Nam Sách, Hải Dương',
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

        // Get admin user for logging
        $adminUser = User::where('email', 'admin@example.com')->first();
        $userId = $adminUser?->id ?? 1;

        // Tạo stocks và inventory logs cho mỗi warehouse
        foreach ($createdWarehouses as $warehouse) {
            foreach ($products as $product) {
                // Tạo stock cho product (không có variant)
                $quantity = rand(50, 500);
                $availableQty = rand((int) ($quantity * 0.7), $quantity);
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

                // Tạo initial inventory log
                InventoryLog::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'movement_type' => InventoryLog::MOVEMENT_TYPE_INBOUND,
                    'quantity' => $quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $quantity,
                    'user_id' => $userId,
                    'reference_type' => 'manual',
                    'note' => 'Initial stock',
                    'created_at' => now()->subDays(rand(30, 60)),
                ]);

                // Tạo stocks cho variants
                foreach ($product->variants as $variant) {
                    $variantQuantity = rand(10, 100);
                    Stock::updateOrCreate(
                        [
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                        ],
                        [
                            'quantity' => $variantQuantity,
                            'available_quantity' => $variantQuantity,
                        ]
                    );

                    // Tạo initial inventory log cho variant
                    InventoryLog::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'movement_type' => InventoryLog::MOVEMENT_TYPE_INBOUND,
                        'quantity' => $variantQuantity,
                        'quantity_before' => 0,
                        'quantity_after' => $variantQuantity,
                        'user_id' => $userId,
                        'reference_type' => 'manual',
                        'note' => 'Initial stock for variant',
                        'created_at' => now()->subDays(rand(30, 60)),
                    ]);
                }

                // Tạo một số inventory logs khác (outbound, adjust)
                $movementCount = rand(2, 5);
                $currentQty = $quantity;
                for ($i = 0; $i < $movementCount; $i++) {
                    $movementType = [InventoryLog::MOVEMENT_TYPE_OUTBOUND, InventoryLog::MOVEMENT_TYPE_ADJUST][rand(0, 1)];
                    $movementQty = rand(5, 50);

                    $qtyBefore = $currentQty;
                    if ($movementType === InventoryLog::MOVEMENT_TYPE_OUTBOUND) {
                        $currentQty -= $movementQty;
                    } else {
                        $currentQty += $movementQty;
                    }

                    InventoryLog::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'movement_type' => $movementType,
                        'quantity' => $movementType === InventoryLog::MOVEMENT_TYPE_OUTBOUND ? -$movementQty : $movementQty,
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $currentQty,
                        'user_id' => $userId,
                        'reason' => $movementType === InventoryLog::MOVEMENT_TYPE_ADJUST ? 'Stock adjustment' : null,
                        'reference_type' => $movementType === InventoryLog::MOVEMENT_TYPE_OUTBOUND ? 'order' : 'manual',
                        'note' => $movementType === InventoryLog::MOVEMENT_TYPE_OUTBOUND ? 'Stock out for order' : 'Stock adjustment',
                        'created_at' => now()->subDays(rand(1, 25)),
                    ]);
                }
            }
        }
    }
}

