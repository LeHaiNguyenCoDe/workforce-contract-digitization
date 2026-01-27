<?php

namespace Database\Seeders;

use App\Models\InboundBatch;
use App\Models\InternalTransfer;
use App\Models\InternalTransferItem;
use App\Models\QualityCheck;
use App\Models\Stock;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WarehouseDetailSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $warehouses = Warehouse::all();
        $receivedBatches = InboundBatch::whereIn('status', [
            InboundBatch::STATUS_RECEIVED,
            InboundBatch::STATUS_QC_IN_PROGRESS,
            InboundBatch::STATUS_QC_COMPLETED,
            InboundBatch::STATUS_COMPLETED
        ])->get();

        if ($warehouses->isEmpty() || $receivedBatches->isEmpty()) {
            return;
        }

        // 1. Seed Quality Checks
        foreach ($receivedBatches as $batch) {
            foreach ($batch->items as $item) {
                $qtyPassed = $item->quantity_received;
                $qtyFailed = 0;

                // Randomly add some failed items for variety
                if (rand(1, 10) > 8) {
                    $qtyFailed = rand(1, 5);
                    $qtyPassed -= $qtyFailed;
                }

                QualityCheck::create([
                    'inbound_batch_id' => $batch->id,
                    'warehouse_id' => $batch->warehouse_id,
                    'product_id' => $item->product_id,
                    'supplier_id' => $batch->supplier_id,
                    'inspector_id' => $admin->id,
                    'check_date' => $batch->received_date ?: Carbon::now()->subDays(1),
                    'status' => $qtyFailed > 0 ? QualityCheck::STATUS_PARTIAL : QualityCheck::STATUS_PASS,
                    'score' => rand(80, 100),
                    'quantity_passed' => $qtyPassed,
                    'quantity_failed' => $qtyFailed,
                    'notes' => 'Kiểm tra chất lượng xương đất và nước men cho ' . ($item->product->name ?? 'Sản phẩm'),
                    'issues' => $qtyFailed > 0 ? ['Vết chân chim', 'Men không đều', 'Sứt mẻ miệng'] : null,
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now(),
                ]);
            }
        }

        // 2. Seed Stocktakes (Kiểm kê)
        foreach ($warehouses as $warehouse) {
            $stocktake = Stocktake::create([
                'stocktake_code' => 'ST' . date('ymd') . rand(100, 999),
                'warehouse_id' => $warehouse->id,
                'status' => 'approved',
                'started_at' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDays(2),
                'created_by' => $admin->id,
                'notes' => 'Kiểm kê định kỳ xưởng gốm cuối năm',
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(2),
            ]);

            $stocks = Stock::where('warehouse_id', $warehouse->id)->take(5)->get();
            foreach ($stocks as $stock) {
                $actualQty = $stock->quantity + rand(-5, 5);
                StocktakeItem::create([
                    'stocktake_id' => $stocktake->id,
                    'product_id' => $stock->product_id,
                    'system_quantity' => $stock->quantity,
                    'actual_quantity' => $actualQty,
                    'difference' => $actualQty - $stock->quantity,
                    'reason' => 'Vỡ hỏng trong quá trình vận chuyển nội bộ',
                ]);
            }
        }

        // 3. Seed Internal Transfers
        if ($warehouses->count() >= 2) {
            $fromWH = $warehouses[0];
            $toWH = $warehouses[1];

            $transfer = InternalTransfer::create([
                'transfer_code' => 'TR' . date('ymd') . rand(100, 999),
                'from_warehouse_id' => $fromWH->id,
                'to_warehouse_id' => $toWH->id,
                'status' => 'received',
                'created_by' => $admin->id,
                'shipped_at' => Carbon::now()->subDay(),
                'received_at' => Carbon::now(),
                'notes' => 'Điều chuyển hàng từ xưởng lò nung sang kho trưng bày',
            ]);

            $transferItems = Stock::where('warehouse_id', $fromWH->id)->take(3)->get();
            foreach ($transferItems as $item) {
                InternalTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item->product_id,
                    'quantity' => rand(5, 10),
                    'received_quantity' => rand(5, 10),
                ]);
            }
        }

        $this->command->info('Warehouse detailed data seeded successfully!');
    }
}
