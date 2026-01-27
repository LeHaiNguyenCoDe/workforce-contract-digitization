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
        // Permissions - Quyền chi tiết từng nút bấm
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100)->unique();
            $table->string('module', 50)->comment('products, orders, users, etc');
            $table->string('action', 50)->comment('view, create, edit, delete, export');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('module');
        });

        // Role Permissions - Gán quyền cho role
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'permission_id']);
        });

        // Audit Logs - Nhật ký hệ thống chi tiết
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 100)->nullable();
            $table->string('action', 50)->comment('create, update, delete, login, logout');
            $table->string('module', 50)->comment('products, orders, users, etc');
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('old_values')->nullable()->comment('JSON');
            $table->text('new_values')->nullable()->comment('JSON');
            $table->text('changes')->nullable()->comment('Diff of changes (JSON)');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamps();
            
            $table->index(['module', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        // Employee KPIs - KPI nhân viên
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('monthly');
            
            // Sales KPIs
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->integer('confirmed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('confirmation_rate', 5, 2)->default(0)->comment('%');
            
            // Return KPIs
            $table->integer('total_returns')->default(0);
            $table->decimal('return_rate', 5, 2)->default(0)->comment('%');
            
            // Performance
            $table->integer('avg_processing_time_minutes')->default(0);
            $table->integer('customer_complaints')->default(0);
            
            $table->timestamps();
            
            $table->unique(['user_id', 'period_start', 'period_end', 'period_type'], 'employee_kpi_unique');
        });

        // Seed default permissions
        $this->seedDefaultPermissions();
    }

    /**
     * Seed default permissions
     */
    private function seedDefaultPermissions(): void
    {
        $modules = [
            'dashboard' => ['view'],
            'products' => ['view', 'create', 'edit', 'delete', 'edit_cost', 'export'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'orders' => ['view', 'create', 'edit', 'delete', 'confirm', 'cancel', 'export'],
            'customers' => ['view', 'create', 'edit', 'delete', 'export'],
            'warehouse' => ['view', 'create_inbound', 'create_outbound', 'adjust', 'stocktake', 'transfer'],
            'batches' => ['view', 'create', 'edit', 'delete'],
            'finance' => ['view', 'create_expense', 'edit_expense', 'delete_expense', 'view_reports'],
            'returns' => ['view', 'create', 'process', 'approve', 'reject'],
            'promotions' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete', 'assign_role'],
            'roles' => ['view', 'create', 'edit', 'delete', 'assign_permission'],
            'settings' => ['view', 'edit'],
            'reports' => ['view_sales', 'view_inventory', 'view_finance', 'export'],
            'audit_logs' => ['view'],
        ];

        $permissions = [];
        $sortOrder = 0;
        
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'name' => ucfirst(str_replace('_', ' ', $action)) . ' ' . ucfirst($module),
                    'code' => $module . '.' . $action,
                    'module' => $module,
                    'action' => $action,
                    'sort_order' => $sortOrder++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        \DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpis');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
