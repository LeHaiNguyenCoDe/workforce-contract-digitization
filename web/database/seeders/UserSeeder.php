<?php

namespace Database\Seeders;

use App\Models\Right;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo Roles
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'description' => 'Administrator role with full access',
                'is_active' => true,
            ]
        );

        $managerRole = Role::updateOrCreate(
            ['name' => 'manager'],
            [
                'description' => 'Manager role with limited admin access',
                'is_active' => true,
            ]
        );

        $customerRole = Role::updateOrCreate(
            ['name' => 'customer'],
            [
                'description' => 'Customer role',
                'is_active' => true,
            ]
        );

        // Tạo Rights
        $rights = [
            ['name' => 'users.view', 'description' => 'View users'],
            ['name' => 'users.create', 'description' => 'Create users'],
            ['name' => 'users.update', 'description' => 'Update users'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'products.view', 'description' => 'View products'],
            ['name' => 'products.create', 'description' => 'Create products'],
            ['name' => 'products.update', 'description' => 'Update products'],
            ['name' => 'products.delete', 'description' => 'Delete products'],
            ['name' => 'orders.view', 'description' => 'View orders'],
            ['name' => 'orders.manage', 'description' => 'Manage orders'],
            ['name' => 'promotions.manage', 'description' => 'Manage promotions'],
            ['name' => 'warehouses.manage', 'description' => 'Manage warehouses'],
        ];

        $createdRights = [];
        foreach ($rights as $right) {
            $createdRights[$right['name']] = Right::updateOrCreate(
                ['name' => $right['name']],
                [
                    'description' => $right['description'],
                    'is_active' => true,
                ]
            );
        }

        // Gán tất cả rights cho admin role (sync để tránh duplicate)
        $adminRole->rights()->sync(array_column($createdRights, 'id'));

        // Gán một số rights cho manager role
        $managerRole->rights()->sync([
            $createdRights['users.view']->id,
            $createdRights['products.view']->id,
            $createdRights['products.create']->id,
            $createdRights['products.update']->id,
            $createdRights['orders.view']->id,
            $createdRights['orders.manage']->id,
        ]);

        // Tạo Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'en',
            ]
        );

        $customer1 = User::updateOrCreate(
            ['email' => 'customer1@example.com'],
            [
                'name' => 'Customer One',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        $customer2 = User::updateOrCreate(
            ['email' => 'customer2@example.com'],
            [
                'name' => 'Customer Two',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'en',
            ]
        );

        // Tạo thêm một số users khác
        for ($i = 3; $i <= 10; $i++) {
            User::updateOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password123'),
                    'active' => true,
                    'language' => $i % 2 === 0 ? 'en' : 'vi',
                ]
            );
        }

        // Gán roles cho users (sync để tránh duplicate)
        $admin->roles()->sync([$adminRole->id]);
        $manager->roles()->sync([$managerRole->id]);
        $customer1->roles()->sync([$customerRole->id]);
        $customer2->roles()->sync([$customerRole->id]);

        // Gán thêm một số rights trực tiếp cho admin
        $admin->rights()->sync([
            $createdRights['users.view']->id,
            $createdRights['users.create']->id,
            $createdRights['users.update']->id,
            $createdRights['users.delete']->id,
        ]);
    }
}

