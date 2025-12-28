<?php

namespace Database\Seeders;

use App\Models\InboundBatch;
use App\Models\InboundBatchItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InboundBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        $products = Product::all();
        $admin = User::where('email', 'admin@example.com')->first();

        if ($warehouses->isEmpty() || $suppliers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Tạo 5 lô nhập với các trạng thái khác nhau
        $statuses = [
            InboundBatch::STATUS_PENDING,
            InboundBatch::STATUS_RECEIVED,
            InboundBatch::STATUS_QC_IN_PROGRESS,
            InboundBatch::STATUS_QC_COMPLETED,
            InboundBatch::STATUS_COMPLETED,
        ];

        foreach ($statuses as $index => $status) {
            $batch = InboundBatch::create([
                'batch_number' => 'BATCH-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'warehouse_id' => $warehouses->random()->id,
                'supplier_id' => $suppliers->random()->id,
                'created_by' => $admin->id,
                'status' => $status,
                'received_date' => $status !== InboundBatch::STATUS_PENDING ? now()->subDays(rand(1, 10)) : null,
                'notes' => 'Lô hàng gốm sứ nhập từ ' . $suppliers->find($suppliers->random()->id)->name,
            ]);

            // Thêm 2-4 sản phẩm vào mỗi lô
            $batchProducts = $products->random(rand(2, 4));
            foreach ($batchProducts as $product) {
                InboundBatchItem::create([
                    'inbound_batch_id' => $batch->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $product->variants->first()?->id,
                    'quantity_received' => rand(50, 200),
                ]);
            }
        }

        $this->command->info('Đã tạo 5 lô nhập mẫu!');
    }
}
