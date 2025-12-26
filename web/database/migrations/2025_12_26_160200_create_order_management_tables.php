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
        // Update orders table - Thêm trạng thái quy trình 4 bước
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'workflow_status')) {
                $table->enum('workflow_status', [
                    'pending_confirmation',  // Chờ xác nhận
                    'picking',               // Đang nhặt hàng
                    'packing',               // Đóng gói
                    'shipped',               // Đã xuất kho
                    'delivered',             // Đã giao
                    'cancelled',             // Đã hủy
                    'returned'               // Đã trả
                ])->default('pending_confirmation')->after('status');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'picked_at')) {
                $table->timestamp('picked_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'packed_at')) {
                $table->timestamp('packed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Picking Lists - Phiếu nhặt hàng
        Schema::create('picking_lists', function (Blueprint $table) {
            $table->id();
            $table->string('picking_code', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Picking List Items
        Schema::create('picking_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_list_id')->constrained('picking_lists')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('location', 100)->nullable()->comment('Vị trí kệ');
            $table->integer('required_quantity');
            $table->integer('picked_quantity')->nullable();
            $table->boolean('is_picked')->default(false);
            $table->timestamps();
        });

        // Returns - Phiếu trả hàng/RMA
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_code', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'requested',       // Khách yêu cầu
                'approved',        // Đã duyệt
                'receiving',       // Đang nhận hàng
                'inspecting',      // Đang kiểm tra
                'completed',       // Hoàn thành
                'rejected',        // Từ chối
                'cancelled'        // Đã hủy
            ])->default('requested');
            $table->enum('return_type', ['refund', 'exchange', 'store_credit'])->default('refund');
            $table->text('reason')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->integer('points_returned')->default(0)->comment('Điểm hoàn lại');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Return Items - Chi tiết sản phẩm trả
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->enum('condition', ['good', 'damaged', 'defective', 'opened'])->default('good');
            $table->boolean('can_restock')->default(true)->comment('Có thể nhập lại kho');
            $table->text('inspection_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('picking_list_items');
        Schema::dropIfExists('picking_lists');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['workflow_status', 'confirmed_at', 'picked_at', 'packed_at', 'shipped_at']);
            $table->dropConstrainedForeignId('confirmed_by');
        });
    }
};
