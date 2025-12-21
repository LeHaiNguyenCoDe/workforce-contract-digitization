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

        // Seed products (requires categories)
        $this->call([
            ProductSeeder::class,
        ]);

        // Seed reviews (requires products and users)
        $this->call([
            ReviewSeeder::class,
        ]);

        // Seed articles
        $this->call([
            ArticleSeeder::class,
        ]);

        // Seed orders (requires users and products)
        $this->call([
            OrderSeeder::class,
        ]);

        // Seed wishlist (requires users and products)
        $this->call([
            WishlistSeeder::class,
        ]);

        // Seed promotions (requires products and categories)
        $this->call([
            PromotionSeeder::class,
        ]);

        // Seed warehouses and stocks (requires products)
        $this->call([
            WarehouseSeeder::class,
        ]);

        // Seed loyalty (requires users and orders)
        $this->call([
            LoyaltySeeder::class,
        ]);

        // Seed payments (requires orders)
        $this->call([
            PaymentSeeder::class,
        ]);

        // Seed shipments (requires orders)
        $this->call([
            ShipmentSeeder::class,
        ]);

        // Seed carts (requires users and products)
        $this->call([
            CartSeeder::class,
        ]);
    }
}
