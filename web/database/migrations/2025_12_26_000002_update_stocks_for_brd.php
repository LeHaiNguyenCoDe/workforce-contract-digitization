<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cập nhật stocks table theo BRD:
     * - Thêm batch_id, qc_id để truy vết (BR-04.2)
     * - Thêm available_quantity (BR-06.1)
     * - Xóa quality_status, quality_notes (chuyển sang QC)
     */
    public function up(): void
    {
        // Kiểm tra xem bảng inbound_batches đã tồn tại chưa (phải chạy migration 000001 trước)
        if (!Schema::hasTable('inbound_batches')) {
            throw new \Exception('inbound_batches table must be created first. Please run migration 2025_12_26_000001_create_inbound_batches_table.php first.');
        }

        Schema::table('stocks', function (Blueprint $table) {
            // Thêm các cột mới
            if (!Schema::hasColumn('stocks', 'inbound_batch_id')) {
                $table->foreignId('inbound_batch_id')->nullable()->constrained('inbound_batches')->nullOnDelete();
            }
            if (!Schema::hasColumn('stocks', 'quality_check_id')) {
                $table->foreignId('quality_check_id')->nullable()->constrained('quality_checks')->nullOnDelete();
            }
            if (!Schema::hasColumn('stocks', 'available_quantity')) {
                $table->integer('available_quantity')->default(0)->after('quantity'); // Tồn kho có thể xuất
            }
            if (!Schema::hasColumn('stocks', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            
            // Xóa các trường không cần thiết (chuyển sang QC)
            if (Schema::hasColumn('stocks', 'quality_status')) {
                $table->dropColumn('quality_status');
            }
            if (Schema::hasColumn('stocks', 'quality_notes')) {
                $table->dropColumn('quality_notes');
            }
        });

        // Đổi tên stock_movements thành inventory_logs (BR-09.2)
        if (Schema::hasTable('stock_movements') && !Schema::hasTable('inventory_logs')) {
            Schema::rename('stock_movements', 'inventory_logs');
        }
        
        if (Schema::hasTable('inventory_logs')) {
            // Thêm các cột mới trước
            Schema::table('inventory_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_logs', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_logs', 'inbound_batch_id')) {
                    $table->foreignId('inbound_batch_id')->nullable()->constrained('inbound_batches')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_logs', 'quality_check_id')) {
                    $table->foreignId('quality_check_id')->nullable()->constrained('quality_checks')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_logs', 'quantity_before')) {
                    $table->integer('quantity_before')->default(0);
                }
                if (!Schema::hasColumn('inventory_logs', 'quantity_after')) {
                    $table->integer('quantity_after')->default(0);
                }
                if (!Schema::hasColumn('inventory_logs', 'reason')) {
                    $table->string('reason')->nullable();
                }
            });
            
            // Xử lý đổi tên cột type -> movement_type
            if (Schema::hasColumn('inventory_logs', 'type') && !Schema::hasColumn('inventory_logs', 'movement_type')) {
                // Tạo cột mới dạng text trước để chuyển dữ liệu
                \DB::statement('ALTER TABLE inventory_logs ADD COLUMN movement_type_temp VARCHAR(20)');
                // Chuyển dữ liệu từ type sang movement_type_temp
                \DB::statement("UPDATE inventory_logs SET movement_type_temp = CASE 
                    WHEN \"type\" = 'in' THEN 'inbound'
                    WHEN \"type\" = 'out' THEN 'outbound'
                    WHEN \"type\" = 'adjust' THEN 'adjust'
                    ELSE 'inbound'
                END");
                // Xóa cột cũ
                Schema::table('inventory_logs', function (Blueprint $table) {
                    $table->dropColumn('type');
                });
                // Đổi tên cột temp thành movement_type và tạo enum
                \DB::statement("ALTER TABLE inventory_logs RENAME COLUMN movement_type_temp TO movement_type");
                // Tạo enum constraint (PostgreSQL)
                $driver = \DB::connection()->getDriverName();
                if ($driver === 'pgsql') {
                    \DB::statement("ALTER TABLE inventory_logs DROP CONSTRAINT IF EXISTS inventory_logs_movement_type_check");
                    \DB::statement("ALTER TABLE inventory_logs ADD CONSTRAINT inventory_logs_movement_type_check 
                        CHECK (movement_type IN ('inbound', 'qc_pass', 'qc_fail', 'outbound', 'adjust', 'return'))");
                    \DB::statement("ALTER TABLE inventory_logs ALTER COLUMN movement_type SET DEFAULT 'inbound'");
                } else {
                    // MySQL
                    Schema::table('inventory_logs', function (Blueprint $table) {
                        $table->enum('movement_type', ['inbound', 'qc_pass', 'qc_fail', 'outbound', 'adjust', 'return'])->default('inbound')->change();
                    });
                }
            } else if (!Schema::hasColumn('inventory_logs', 'movement_type')) {
                // Nếu không có cả type và movement_type, tạo mới
                Schema::table('inventory_logs', function (Blueprint $table) {
                    $table->enum('movement_type', ['inbound', 'qc_pass', 'qc_fail', 'outbound', 'adjust', 'return'])->default('inbound');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_logs')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_logs', 'quality_check_id')) {
                    $table->dropForeign(['quality_check_id']);
                }
                if (Schema::hasColumn('inventory_logs', 'inbound_batch_id')) {
                    $table->dropForeign(['inbound_batch_id']);
                }
                if (Schema::hasColumn('inventory_logs', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
                $columnsToDrop = [];
                if (Schema::hasColumn('inventory_logs', 'user_id')) $columnsToDrop[] = 'user_id';
                if (Schema::hasColumn('inventory_logs', 'inbound_batch_id')) $columnsToDrop[] = 'inbound_batch_id';
                if (Schema::hasColumn('inventory_logs', 'quality_check_id')) $columnsToDrop[] = 'quality_check_id';
                if (Schema::hasColumn('inventory_logs', 'quantity_before')) $columnsToDrop[] = 'quantity_before';
                if (Schema::hasColumn('inventory_logs', 'quantity_after')) $columnsToDrop[] = 'quantity_after';
                if (Schema::hasColumn('inventory_logs', 'reason')) $columnsToDrop[] = 'reason';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Đổi movement_type về type
                if (Schema::hasColumn('inventory_logs', 'movement_type') && !Schema::hasColumn('inventory_logs', 'type')) {
                    \DB::statement('ALTER TABLE inventory_logs ADD COLUMN type VARCHAR(20)');
                    \DB::statement("UPDATE inventory_logs SET type = CASE 
                        WHEN movement_type = 'inbound' THEN 'in'
                        WHEN movement_type = 'outbound' THEN 'out'
                        WHEN movement_type = 'adjust' THEN 'adjust'
                        ELSE 'in'
                    END");
                    $table->dropColumn('movement_type');
                }
            });

            if (Schema::hasTable('inventory_logs') && !Schema::hasTable('stock_movements')) {
                Schema::rename('inventory_logs', 'stock_movements');
            }
        }

        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                if (Schema::hasColumn('stocks', 'quality_check_id')) {
                    $table->dropForeign(['quality_check_id']);
                }
                if (Schema::hasColumn('stocks', 'inbound_batch_id')) {
                    $table->dropForeign(['inbound_batch_id']);
                }
                $columnsToDrop = [];
                if (Schema::hasColumn('stocks', 'inbound_batch_id')) $columnsToDrop[] = 'inbound_batch_id';
                if (Schema::hasColumn('stocks', 'quality_check_id')) $columnsToDrop[] = 'quality_check_id';
                if (Schema::hasColumn('stocks', 'available_quantity')) $columnsToDrop[] = 'available_quantity';
                if (Schema::hasColumn('stocks', 'expiry_date')) $columnsToDrop[] = 'expiry_date';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                if (!Schema::hasColumn('stocks', 'quality_status')) {
                    $table->string('quality_status')->default('passed');
                }
                if (!Schema::hasColumn('stocks', 'quality_notes')) {
                    $table->text('quality_notes')->nullable();
                }
            });
        }
    }
};

