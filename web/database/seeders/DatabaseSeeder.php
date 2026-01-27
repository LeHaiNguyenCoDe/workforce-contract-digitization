<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed languages first (required for translations)
        $this->call([
            LanguageSeeder::class,
        ]);

        // Seed users, roles, and rights
        $this->call([
            UserSeeder::class,
        ]);

        // Seed categories
        $this->call([
            CategorySeeder::class,
        ]);

        // Seed expense categories (for finance module)
        $this->call([
            ExpenseCategorySeeder::class,
        ]);

        // Seed suppliers (NEW)
        $this->call([
            SupplierSeeder::class,
        ]);

        // Seed products (requires categories and suppliers)
        $this->call([
            ProductSeeder::class,
        ]);

        // Seed warehouses (requires products)
        $this->call([
            WarehouseSeeder::class,
        ]);

        // Seed inbound batches (NEW)
        $this->call([
            InboundBatchSeeder::class,
        ]);

        // Seed stock data (NEW)
        $this->call([
            StockDataSeeder::class,
        ]);

        // Seed warehouse details (QC, Stocktake, Transfers)
        $this->call([
            WarehouseDetailSeeder::class,
        ]);

        // Seed finance (Transactions, Debt, Expenses)
        $this->call([
            FinanceSeeder::class,
        ]);

        // Seed funds (NEW)
        $this->call([
            FundSeeder::class,
        ]);

        // Seed other modules...
        $this->call([
            ReviewSeeder::class,
            ArticleSeeder::class,
            OrderSeeder::class,
            WishlistSeeder::class,
            PromotionSeeder::class,
            LoyaltySeeder::class,
            PaymentSeeder::class,
            ShipmentSeeder::class,
            CartSeeder::class,
        ]);
    }
}
