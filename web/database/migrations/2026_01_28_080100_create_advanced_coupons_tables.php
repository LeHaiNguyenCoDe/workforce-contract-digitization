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
        if (Schema::hasTable('coupons')) {
            return;
        }
        // Coupons - Mã giảm giá nâng cao
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'bxgy', 'free_shipping'])->default('percentage');
            $table->decimal('value', 15, 2)->comment('% or fixed amount');
            $table->decimal('min_purchase_amount', 15, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable()->comment('Max discount for percentage type');

            // Usage limits
            $table->integer('usage_limit_total')->nullable()->comment('Total usage limit');
            $table->integer('usage_limit_per_user')->nullable()->comment('Per user limit');
            $table->integer('usage_limit_per_day')->nullable()->comment('Per day limit');
            $table->integer('usage_count')->default(0);

            // Applicability
            $table->json('applicable_products')->nullable()->comment('Product IDs');
            $table->json('applicable_categories')->nullable()->comment('Category IDs');
            $table->json('applicable_segments')->nullable()->comment('Segment IDs');
            $table->json('excluded_products')->nullable();

            // BXGY (Buy X Get Y) specific
            $table->integer('bxgy_buy_quantity')->nullable();
            $table->integer('bxgy_get_quantity')->nullable();
            $table->json('bxgy_get_products')->nullable();

            // Rules
            $table->boolean('stackable')->default(false)->comment('Can combine with other coupons');
            $table->boolean('auto_apply')->default(false)->comment('Auto apply if conditions met');
            $table->boolean('first_order_only')->default(false);

            // Validity
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index(['is_active', 'valid_from', 'valid_to']);
        });

        // Coupon Usages
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('order_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->timestamp('used_at')->useCurrent();

            $table->index(['coupon_id', 'used_at']);
            $table->index(['user_id', 'coupon_id']);
        });

        // Coupon Generation Batches (for bulk code generation)
        Schema::create('coupon_generation_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('prefix', 20);
            $table->integer('quantity');
            $table->integer('generated_count')->default(0);
            $table->json('template')->comment('Coupon template settings');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_generation_batches');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
