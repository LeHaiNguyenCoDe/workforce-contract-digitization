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
        Schema::table('products', function (Blueprint $table) {
            $table->string('manufacturer_name')->nullable()->after('thumbnail');
            $table->string('manufacturer_brand')->nullable()->after('manufacturer_name');
            $table->integer('stock_quantity')->default(0)->after('price');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('stock_quantity');
            $table->boolean('is_active')->default(true)->after('discount_percentage');
            $table->timestamp('published_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'manufacturer_name',
                'manufacturer_brand',
                'stock_quantity',
                'discount_percentage',
                'is_active',
                'published_at'
            ]);
        });
    }
};
