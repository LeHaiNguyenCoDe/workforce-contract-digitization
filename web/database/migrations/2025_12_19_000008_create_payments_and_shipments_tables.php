<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // vnpay, momo, cod...
            $table->unsignedBigInteger('amount');
            $table->string('status'); // pending, success, failed, refunded
            $table->string('transaction_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('carrier')->nullable(); // GHN, GHTK...
            $table->string('tracking_code')->nullable();
            $table->string('status')->default('pending'); // pending, picking, shipping, delivered, cancelled
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
    }
};


