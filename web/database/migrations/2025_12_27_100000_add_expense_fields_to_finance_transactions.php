<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add expense-related fields to finance_transactions
 * Consolidates expenses table into finance_transactions
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Add expense-specific fields if not exists
            if (!Schema::hasColumn('finance_transactions', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('category_id')
                    ->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('finance_transactions', 'attachment')) {
                $table->string('attachment')->nullable()->after('payment_method')
                    ->comment('File đính kèm');
            }
            if (!Schema::hasColumn('finance_transactions', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('attachment');
            }
            if (!Schema::hasColumn('finance_transactions', 'recurring_period')) {
                $table->enum('recurring_period', ['daily', 'weekly', 'monthly', 'yearly'])
                    ->nullable()->after('is_recurring');
            }
            if (!Schema::hasColumn('finance_transactions', 'reference_number')) {
                $table->string('reference_number', 100)->nullable()->after('reference_id')
                    ->comment('Số hóa đơn, chứng từ bên ngoài');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $columns = ['warehouse_id', 'attachment', 'is_recurring', 'recurring_period', 'reference_number'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('finance_transactions', $column)) {
                    if ($column === 'warehouse_id') {
                        $table->dropForeign(['warehouse_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
