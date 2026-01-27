<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create Suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Link products to suppliers
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('warehouse_type', ['new', 'stock'])->default('new');
        });

        // 3. Update stocks with expiry and quality
        Schema::table('stocks', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
            $table->string('quality_status')->default('passed'); // passed, failed, pending
            $table->text('quality_notes')->nullable();
        });

        // 4. Update shipments with shipper and GPS
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('shipper_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->string('tracking_number')->nullable()->unique();
            $table->timestamp('delivered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['shipper_id', 'current_lat', 'current_lng', 'tracking_number', 'delivered_at']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'quality_status', 'quality_notes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'warehouse_type']);
        });

        Schema::dropIfExists('suppliers');
    }
};
