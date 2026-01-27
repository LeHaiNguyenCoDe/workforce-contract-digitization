<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // For PostgreSQL, we need to drop and recreate the column with new enum values
        if (DB::getDriverName() === 'pgsql') {
            // Drop the check constraint first
            DB::statement("ALTER TABLE products DROP CONSTRAINT IF EXISTS products_warehouse_type_check");
            
            // Change column type to text temporarily
            DB::statement("ALTER TABLE products ALTER COLUMN warehouse_type TYPE text");
            
            // Add check constraint with new values
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_warehouse_type_check CHECK (warehouse_type IN ('new', 'stock', 'low_stock', 'out_of_stock'))");
            
            // Set default value
            DB::statement("ALTER TABLE products ALTER COLUMN warehouse_type SET DEFAULT 'new'");
        } else {
            // For MySQL, modify the column directly
            Schema::table('products', function (Blueprint $table) {
                $table->enum('warehouse_type', ['new', 'stock', 'low_stock', 'out_of_stock'])
                    ->default('new')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Drop the check constraint
            DB::statement("ALTER TABLE products DROP CONSTRAINT IF EXISTS products_warehouse_type_check");
            
            // Change column type to text temporarily
            DB::statement("ALTER TABLE products ALTER COLUMN warehouse_type TYPE text");
            
            // Add check constraint with original values
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_warehouse_type_check CHECK (warehouse_type IN ('new', 'stock'))");
            
            // Set default value
            DB::statement("ALTER TABLE products ALTER COLUMN warehouse_type SET DEFAULT 'new'");
        } else {
            // For MySQL, revert to original enum values
            Schema::table('products', function (Blueprint $table) {
                $table->enum('warehouse_type', ['new', 'stock'])
                    ->default('new')
                    ->change();
            });
        }
    }
};
