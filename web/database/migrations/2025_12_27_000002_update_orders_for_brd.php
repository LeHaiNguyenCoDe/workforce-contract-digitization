<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Update Orders Table for BR-SALES
 * Adds cost tracking and payment tracking columns
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add cost tracking columns only if they don't exist
            if (!Schema::hasColumn('orders', 'cost_amount')) {
                $table->decimal('cost_amount', 20, 2)->default(0)->after('total_amount')
                    ->comment('Giá vốn đơn hàng');
            }
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 20, 2)->default(0)->after('cost_amount')
                    ->comment('Số tiền đã thanh toán');
            }
            if (!Schema::hasColumn('orders', 'remaining_amount')) {
                $table->decimal('remaining_amount', 20, 2)->default(0)->after('paid_amount')
                    ->comment('Số tiền còn lại');
            }

            // Add status timestamps only if they don't exist
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['cost_amount', 'paid_amount', 'remaining_amount', 'confirmed_at', 'delivered_at', 'completed_at', 'cancelled_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
