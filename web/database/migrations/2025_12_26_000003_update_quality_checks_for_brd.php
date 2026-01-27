<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cập nhật quality_checks theo BRD:
     * - Liên kết với InboundBatch (BR-03.1)
     * - Status: PASS, FAIL, PARTIAL (BR-03.3)
     * - Không cho QC lại (BR-03.2)
     */
    public function up(): void
    {
        // Kiểm tra xem bảng inbound_batches đã tồn tại chưa
        if (!Schema::hasTable('inbound_batches')) {
            throw new \Exception('inbound_batches table must be created first. Please run migration 2025_12_26_000001_create_inbound_batches_table.php first.');
        }

        Schema::table('quality_checks', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_checks', 'inbound_batch_id')) {
                // Cho phép NULL trước để tránh lỗi với dữ liệu cũ
                $table->foreignId('inbound_batch_id')->nullable()->constrained('inbound_batches')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('quality_checks', 'warehouse_id')) {
                // Cho phép NULL để xử lý dữ liệu cũ, sau đó có thể update hoặc xóa
                $table->foreignId('warehouse_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('quality_checks', 'quantity_passed')) {
                $table->integer('quantity_passed')->default(0);
            }
            if (!Schema::hasColumn('quality_checks', 'quantity_failed')) {
                $table->integer('quantity_failed')->default(0);
            }
            if (!Schema::hasColumn('quality_checks', 'is_rollback')) {
                $table->boolean('is_rollback')->default(false);
            }
            if (!Schema::hasColumn('quality_checks', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quality_checks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
        });

        // Đổi status enum
        if (Schema::hasColumn('quality_checks', 'status')) {
            Schema::table('quality_checks', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        Schema::table('quality_checks', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_checks', 'status')) {
                $table->enum('status', ['pass', 'fail', 'partial'])->default('pass')->after('score');
            }
        });

        // Xóa batch_number unique vì giờ dùng inbound_batch_id
        if (Schema::hasColumn('quality_checks', 'batch_number')) {
            Schema::table('quality_checks', function (Blueprint $table) {
                // Thử drop unique constraint nếu có (sẽ không lỗi nếu không có)
                try {
                    $table->dropUnique(['batch_number']);
                } catch (\Exception $e) {
                    // Ignore nếu không có unique constraint
                }
                $table->dropColumn('batch_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_checks', function (Blueprint $table) {
            $table->string('batch_number')->unique();
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['inbound_batch_id']);
            $table->dropColumn([
                'inbound_batch_id',
                'warehouse_id',
                'quantity_passed',
                'quantity_failed',
                'is_rollback',
                'approved_by',
                'approved_at'
            ]);
            $table->enum('status', ['passed', 'failed', 'pending'])->default('pending')->change();
        });
    }
};

