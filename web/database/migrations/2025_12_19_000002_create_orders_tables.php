<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('address_line');
            $table->string('ward')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('payment_method'); // cod, credit_card, bank_transfer, e_wallet
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('total_amount');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('qty');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};


