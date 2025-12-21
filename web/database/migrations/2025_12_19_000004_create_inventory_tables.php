<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()
                ->constrained('product_variants')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id', 'product_variant_id'], 'stocks_unique');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()
                ->constrained('product_variants')->nullOnDelete();
            $table->string('type'); // in, out, adjust
            $table->integer('quantity');
            $table->string('reference_type')->nullable(); // order, manual, return...
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('warehouses');
    }
};


