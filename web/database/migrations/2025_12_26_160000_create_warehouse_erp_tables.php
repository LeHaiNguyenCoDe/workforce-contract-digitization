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
        // Batches - Lô hàng với mã ID, ngày sản xuất, hạn sử dụng
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique()->comment('Mã lô hàng');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('quantity')->default(0)->comment('Số lượng ban đầu');
            $table->integer('remaining_quantity')->default(0)->comment('Số lượng còn lại');
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('Giá vốn đơn vị');
            $table->date('manufacturing_date')->nullable()->comment('Ngày sản xuất');
            $table->date('expiry_date')->nullable()->comment('Hạn sử dụng');
            $table->enum('status', ['available', 'reserved', 'expired', 'depleted'])->default('available');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['product_id', 'expiry_date']); // For FEFO queries
            $table->index(['warehouse_id', 'status']);
        });

        // Inventory Settings - Ngưỡng min/max cho từng sản phẩm
        Schema::create('inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->integer('min_quantity')->default(0)->comment('Ngưỡng tồn kho tối thiểu');
            $table->integer('max_quantity')->default(0)->comment('Ngưỡng tồn kho tối đa');
            $table->integer('reorder_quantity')->default(0)->comment('Số lượng đề xuất nhập');
            $table->boolean('auto_create_purchase_request')->default(false);
            $table->timestamps();
            
            $table->unique(['product_id', 'warehouse_id']);
        });

        // Purchase Requests - Phiếu đề nghị nhập hàng tự động
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 50)->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('requested_quantity');
            $table->integer('current_stock')->comment('Tồn kho lúc tạo request');
            $table->integer('min_stock')->comment('Ngưỡng min lúc tạo request');
            $table->enum('status', ['pending', 'approved', 'rejected', 'ordered', 'completed'])->default('pending');
            $table->enum('source', ['auto', 'manual'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Stocktakes - Phiên kiểm kê định kỳ
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id();
            $table->string('stocktake_code', 50)->unique();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('status', ['draft', 'in_progress', 'pending_approval', 'approved', 'cancelled'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_locked')->default(false)->comment('Khóa kho khi kiểm kê');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Stocktake Items - Chi tiết từng sản phẩm trong kiểm kê
        Schema::create('stocktake_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocktake_id')->constrained('stocktakes')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->integer('system_quantity')->comment('Tồn hệ thống');
            $table->integer('actual_quantity')->nullable()->comment('Tồn thực tế');
            $table->integer('difference')->nullable()->comment('Chênh lệch');
            $table->text('reason')->nullable()->comment('Giải trình chênh lệch');
            $table->timestamps();
            
            $table->unique(['stocktake_id', 'product_id', 'batch_id']);
        });

        // Internal Transfers - Luân chuyển kho nội bộ
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code', 50)->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->enum('status', ['draft', 'pending', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Internal Transfer Items
        Schema::create('internal_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('internal_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->integer('quantity');
            $table->integer('received_quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_transfer_items');
        Schema::dropIfExists('internal_transfers');
        Schema::dropIfExists('stocktake_items');
        Schema::dropIfExists('stocktakes');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('inventory_settings');
        Schema::dropIfExists('batches');
    }
};
