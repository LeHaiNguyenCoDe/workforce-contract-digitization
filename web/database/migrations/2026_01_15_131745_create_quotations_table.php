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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable(); // Có thể để nullable nếu không bắt buộc đặt tên
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Sử dụng string cho status là ok, nhưng nên có default rõ ràng
            $table->string('status')->default('pending');

            $table->date('valid_until'); // Chuyển sang kiểu date
            $table->unsignedBigInteger('total_amount')->default(0); // Dùng số để tính toán
            $table->text('note')->nullable(); // Dùng text nếu ghi chú dài
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
