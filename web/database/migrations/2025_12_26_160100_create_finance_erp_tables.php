<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Expense Categories - Danh mục chi phí
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->enum('type', ['expense', 'income'])->default('expense');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Expenses - Thu chi ngoài (tiền mặt bằng, điện, lương...)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code', 50)->unique();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('type', ['expense', 'income'])->default('expense');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('payment_method', 50)->nullable()->comment('cash, bank, etc');
            $table->string('reference_number', 100)->nullable()->comment('Số hóa đơn, chứng từ');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable()->comment('File đính kèm');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['expense_date', 'type']);
            $table->index(['category_id', 'expense_date']);
        });

        // Product Costs - Giá vốn bình quân gia quyền
        Schema::create('product_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('average_cost', 15, 2)->default(0)->comment('Giá vốn bình quân');
            $table->decimal('last_cost', 15, 2)->default(0)->comment('Giá vốn lần nhập cuối');
            $table->integer('total_quantity')->default(0)->comment('Tổng số lượng tính giá vốn');
            $table->decimal('total_value', 20, 2)->default(0)->comment('Tổng giá trị');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->unique('product_id');
        });

        // Product Cost History - Lịch sử thay đổi giá vốn
        Schema::create('product_cost_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->enum('action', ['inbound', 'outbound', 'adjustment']);
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('old_average_cost', 15, 2);
            $table->decimal('new_average_cost', 15, 2);
            $table->string('reference_type', 50)->nullable()->comment('inbound_receipt, order, adjustment');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
        });

        // COD Reconciliation - Đối soát COD vận chuyển
        Schema::create('cod_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('reconciliation_code', 50)->unique();
            $table->string('carrier_name', 100)->comment('Tên đơn vị vận chuyển');
            $table->date('reconciliation_date');
            $table->date('period_from');
            $table->date('period_to');
            $table->integer('total_orders')->default(0);
            $table->decimal('expected_amount', 15, 2)->default(0)->comment('Số tiền kỳ vọng');
            $table->decimal('received_amount', 15, 2)->default(0)->comment('Số tiền thực nhận');
            $table->decimal('difference', 15, 2)->default(0)->comment('Chênh lệch');
            $table->decimal('shipping_fee', 15, 2)->default(0)->comment('Phí vận chuyển');
            $table->decimal('cod_fee', 15, 2)->default(0)->comment('Phí COD');
            $table->enum('status', ['pending', 'matched', 'discrepancy', 'resolved'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // COD Reconciliation Items - Chi tiết từng đơn hàng
        Schema::create('cod_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('cod_reconciliations')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('tracking_number', 100)->nullable();
            $table->decimal('order_amount', 15, 2);
            $table->decimal('cod_amount', 15, 2)->nullable()->comment('Số tiền COD từ carrier');
            $table->decimal('difference', 15, 2)->nullable();
            $table->enum('status', ['pending', 'matched', 'discrepancy', 'resolved'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['reconciliation_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cod_reconciliation_items');
        Schema::dropIfExists('cod_reconciliations');
        Schema::dropIfExists('product_cost_history');
        Schema::dropIfExists('product_costs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
