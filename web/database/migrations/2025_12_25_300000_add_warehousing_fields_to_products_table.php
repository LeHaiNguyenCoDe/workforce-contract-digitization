<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name');
            $table->integer('min_stock_level')->default(5)->after('warehouse_type');
            $table->string('storage_location')->nullable()->after('min_stock_level');
            $table->integer('stock_qty')->default(0)->after('price'); // Cached total stock
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'min_stock_level', 'storage_location', 'stock_qty']);
        });
    }
};
