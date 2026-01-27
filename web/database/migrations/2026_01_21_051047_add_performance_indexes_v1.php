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
        // Products indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('category_id');
            $table->index('supplier_id');
            $table->index('published_at');
        });

        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index(['created_at', 'status']);
        });

        // Employees indexes
        Schema::table('employees', function (Blueprint $table) {
            $table->index('status');
            $table->index('department');
        });

        // Tasks indexes
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('assignee_id');
            $table->index('priority');
            $table->index('due_date');
        });

        // Quotations indexes
        Schema::table('quotations', function (Blueprint $table) {
            $table->index('status');
            $table->index('customer_id');
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['published_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['department']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assignee_id']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['due_date']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['valid_until']);
        });
    }
};
