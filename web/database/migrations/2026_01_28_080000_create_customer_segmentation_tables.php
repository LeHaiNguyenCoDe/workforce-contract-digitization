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
        if (Schema::hasTable('customer_segments')) {
            return;
        }
        // Customer Segments - Phân khúc khách hàng
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['static', 'dynamic'])->default('dynamic')->comment('Static = manual add, Dynamic = auto by conditions');
            $table->json('conditions')->nullable()->comment('Query conditions for dynamic segments');
            $table->integer('customer_count')->default(0);
            $table->string('color', 20)->nullable()->comment('Display color');
            $table->string('icon', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });

        // Segment Customers - Many-to-many relationship
        Schema::create('segment_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained('customer_segments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('source', ['manual', 'auto'])->default('auto');
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['segment_id', 'user_id']);
            $table->index('user_id');
        });

        // Segment History - Track changes
        Schema::create('segment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained('customer_segments')->onDelete('cascade');
            $table->integer('customer_count');
            $table->integer('customers_added')->default(0);
            $table->integer('customers_removed')->default(0);
            $table->timestamp('calculated_at');

            $table->index(['segment_id', 'calculated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segment_histories');
        Schema::dropIfExists('segment_customers');
        Schema::dropIfExists('customer_segments');
    }
};
