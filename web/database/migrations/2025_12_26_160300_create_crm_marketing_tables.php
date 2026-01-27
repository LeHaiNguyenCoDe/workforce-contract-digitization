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
        // Membership Tiers - Hạng thành viên
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);  // Đồng, Bạc, Vàng, Kim cương
            $table->string('code', 20)->unique();
            $table->integer('min_spent')->default(0)->comment('Chi tiêu tối thiểu để đạt hạng');
            $table->integer('max_spent')->nullable()->comment('Chi tiêu tối đa của hạng');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('% chiết khấu');
            $table->decimal('point_multiplier', 3, 2)->default(1)->comment('Nhân hệ số điểm');
            $table->integer('sort_order')->default(0);
            $table->string('color', 20)->nullable()->comment('Màu badge');
            $table->string('icon', 100)->nullable();
            $table->text('benefits')->nullable()->comment('Quyền lợi (JSON)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Customer Membership - Hạng của từng khách
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tier_id')->constrained('membership_tiers')->onDelete('cascade');
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->date('tier_achieved_at')->nullable();
            $table->date('tier_expires_at')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Customer Points - Điểm tích lũy
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('available_points')->default(0);
            $table->integer('used_points')->default(0);
            $table->integer('expired_points')->default(0);
            $table->integer('total_earned')->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Point Transactions - Lịch sử giao dịch điểm
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['earn', 'redeem', 'expire', 'refund', 'adjust']);
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('reference_type', 50)->nullable()->comment('order, return, adjustment');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });

        // Marketing Automations
        Schema::create('marketing_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('trigger_type', [
                'order_placed',
                'order_shipped', 
                'order_delivered',
                'cart_abandoned',
                'inactive_customer',
                'birthday',
                'tier_upgraded',
                'custom'
            ]);
            $table->integer('delay_days')->default(0)->comment('Delay sau trigger');
            $table->integer('delay_hours')->default(0);
            $table->enum('action_type', ['email', 'sms', 'notification', 'coupon']);
            $table->foreignId('email_template_id')->nullable();
            $table->text('conditions')->nullable()->comment('JSON conditions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('subject', 255);
            $table->text('body');
            $table->enum('type', ['email', 'sms']);
            $table->text('variables')->nullable()->comment('Available variables (JSON)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add foreign key for automations
        Schema::table('marketing_automations', function (Blueprint $table) {
            $table->foreign('email_template_id')
                  ->references('id')
                  ->on('email_templates')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_automations', function (Blueprint $table) {
            $table->dropForeign(['email_template_id']);
        });
        
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('marketing_automations');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('customer_points');
        Schema::dropIfExists('customer_memberships');
        Schema::dropIfExists('membership_tiers');
    }
};
