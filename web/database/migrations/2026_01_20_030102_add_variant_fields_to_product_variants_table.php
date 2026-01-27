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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('variant_type')->default('storage')->after('product_id');
            $table->string('label')->nullable()->after('variant_type');
            $table->string('color_code')->nullable()->after('color');
            $table->integer('price_adjustment')->default(0)->after('color_code');
            $table->boolean('is_default')->default(false)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['variant_type', 'label', 'color_code', 'price_adjustment', 'is_default']);
        });
    }
};
