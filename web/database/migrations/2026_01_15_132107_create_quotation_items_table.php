<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->unsignedInteger('quantity'); // Số lượng
            $table->unsignedBigInteger('unit_price'); // GIÁ QUAN TRỌNG: Lưu giá tại thời điểm báo
            $table->unsignedBigInteger('discount')->default(0); // Số tiền giảm hoặc %
            $table->unsignedBigInteger('tax')->default(0); // Số tiền thuế
            $table->unsignedInteger('subtotal')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
