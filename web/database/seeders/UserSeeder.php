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
        // === Tạo Roles ===
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'description' => 'Administrator - Toàn quyền hệ thống',
                'is_active' => true,
            ]
        );

        $managerRole = Role::updateOrCreate(
            ['name' => 'manager'],
            [
                'description' => 'Quản lý - Quyền vận hành, không có cài đặt hệ thống',
                'is_active' => true,
            ]
        );

        $staffRole = Role::updateOrCreate(
            ['name' => 'staff'],
            [
                'description' => 'Nhân viên - Chỉ xem thông tin cơ bản',
                'is_active' => true,
            ]
        );

        $warehouseRole = Role::updateOrCreate(
            ['name' => 'warehouse'],
            [
                'description' => 'Nhân viên kho - Quản lý kho hàng',
                'is_active' => true,
            ]
        );

        $customerRole = Role::updateOrCreate(
            ['name' => 'customer'],
            [
                'description' => 'Khách hàng - Chỉ mua hàng',
                'is_active' => true,
            ]
        );

        // === Tạo Rights (matching frontend permissionGroups) ===
        $rightsData = [
            // Dashboard
            'view_dashboard' => 'Xem Dashboard',

            // Đơn hàng
            'view_orders' => 'Xem đơn hàng',
            'create_orders' => 'Tạo đơn hàng',
            'edit_orders' => 'Sửa đơn hàng',
            'cancel_orders' => 'Hủy đơn hàng',
            'delete_orders' => 'Xóa đơn hàng',

            // Trả hàng
            'view_returns' => 'Xem trả hàng',
            'create_returns' => 'Tạo phiếu trả',
            'edit_returns' => 'Sửa phiếu trả',
            'approve_returns' => 'Duyệt trả hàng',

            // Khách hàng
            'view_customers' => 'Xem khách hàng',
            'create_customers' => 'Tạo khách hàng',
            'edit_customers' => 'Sửa khách hàng',
            'delete_customers' => 'Xóa khách hàng',

            // Sản phẩm
            'view_products' => 'Xem sản phẩm',
            'create_products' => 'Tạo sản phẩm',
            'edit_products' => 'Sửa sản phẩm',
            'delete_products' => 'Xóa sản phẩm',

            // Danh mục
            'view_categories' => 'Xem danh mục',
            'create_categories' => 'Tạo danh mục',
            'edit_categories' => 'Sửa danh mục',
            'delete_categories' => 'Xóa danh mục',

            // Kho
            'view_warehouse' => 'Xem thông tin kho',
            'create_inbound' => 'Tạo phiếu nhập',
            'create_outbound' => 'Tạo phiếu xuất',
            'adjust_stock' => 'Điều chỉnh tồn kho',
            'transfer_stock' => 'Chuyển kho',

            // Nhà cung cấp
            'view_suppliers' => 'Xem nhà cung cấp',
            'create_suppliers' => 'Tạo nhà cung cấp',
            'edit_suppliers' => 'Sửa nhà cung cấp',
            'delete_suppliers' => 'Xóa nhà cung cấp',

            // Tài chính
            'view_finance' => 'Xem tài chính',
            'create_transactions' => 'Tạo giao dịch',
            'edit_transactions' => 'Sửa giao dịch',
            'manage_funds' => 'Quản lý quỹ',

            // Phải thu
            'view_receivables' => 'Xem công nợ phải thu',
            'collect_receivables' => 'Thu công nợ',
            'write_off_receivables' => 'Xóa nợ phải thu',

            // Phải trả
            'view_payables' => 'Xem công nợ phải trả',
            'pay_payables' => 'Thanh toán công nợ',

            // Hạng thành viên
            'view_membership' => 'Xem hạng thành viên',
            'edit_membership' => 'Sửa hạng thành viên',

            // Khuyến mãi
            'view_promotions' => 'Xem khuyến mãi',
            'create_promotions' => 'Tạo khuyến mãi',
            'edit_promotions' => 'Sửa khuyến mãi',
            'delete_promotions' => 'Xóa khuyến mãi',

            // Điểm thưởng
            'view_points' => 'Xem điểm thưởng',
            'manage_points' => 'Quản lý điểm',

            // Bài viết
            'view_articles' => 'Xem bài viết',
            'create_articles' => 'Tạo bài viết',
            'edit_articles' => 'Sửa bài viết',
            'delete_articles' => 'Xóa bài viết',

            // Báo cáo
            'view_reports' => 'Xem báo cáo',
            'export_reports' => 'Xuất báo cáo',

            // Nhân sự
            'view_users' => 'Xem nhân sự',
            'create_users' => 'Tạo nhân sự',
            'edit_users' => 'Sửa nhân sự',
            'delete_users' => 'Xóa nhân sự',

            // Phân quyền
            'view_permissions' => 'Xem phân quyền',
            'edit_permissions' => 'Sửa phân quyền',

            // Chi nhánh/Kho
            'view_warehouses' => 'Xem chi nhánh/kho',
            'create_warehouses' => 'Tạo chi nhánh/kho',
            'edit_warehouses' => 'Sửa chi nhánh/kho',

            // Cài đặt
            'view_settings' => 'Xem cài đặt',
            'edit_settings' => 'Sửa cài đặt',

            // Nhật ký
            'view_audit_logs' => 'Xem nhật ký hệ thống',
        ];

        $createdRights = [];
        foreach ($rightsData as $name => $description) {
            $createdRights[$name] = Right::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }

        // === Gán Rights cho Roles ===

        // Admin: Toàn quyền
        $adminRole->rights()->sync(collect($createdRights)->pluck('id')->toArray());

        // Manager: Quyền vận hành
        $managerRights = [
            'view_dashboard',
            'view_orders',
            'edit_orders',
            'cancel_orders',
            'view_returns',
            'edit_returns',
            'approve_returns',
            'view_customers',
            'edit_customers',
            'view_products',
            'edit_products',
            'view_categories',
            'view_warehouse',
            'create_inbound',
            'create_outbound',
            'view_suppliers',
            'view_finance',
            'create_transactions',
            'view_receivables',
            'collect_receivables',
            'view_payables',
            'pay_payables',
            'view_membership',
            'view_promotions',
            'edit_promotions',
            'view_points',
            'view_articles',
            'edit_articles',
            'view_reports',
            'export_reports',
            'view_users',
        ];
        $managerRole->rights()->sync(
            collect($managerRights)->map(fn($r) => $createdRights[$r]->id)->toArray()
        );

        // Staff: Chỉ xem
        $staffRights = [
            'view_dashboard',
            'view_orders',
            'view_customers',
            'view_products',
            'view_categories',
            'view_warehouse',
        ];
        $staffRole->rights()->sync(
            collect($staffRights)->map(fn($r) => $createdRights[$r]->id)->toArray()
        );

        // Warehouse: Quản lý kho
        $warehouseRights = [
            'view_dashboard',
            'view_products',
            'view_categories',
            'view_warehouse',
            'create_inbound',
            'create_outbound',
            'adjust_stock',
            'view_suppliers',
        ];
        $warehouseRole->rights()->sync(
            collect($warehouseRights)->map(fn($r) => $createdRights[$r]->id)->toArray()
        );

        // === Tạo Users ===
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Quản lý',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Nhân viên',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        $warehouse = User::updateOrCreate(
            ['email' => 'warehouse@example.com'],
            [
                'name' => 'Nhân viên kho',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        // Khách hàng mẫu
        $customer = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Khách hàng mẫu',
                'password' => Hash::make('password123'),
                'active' => true,
                'language' => 'vi',
            ]
        );

        // === Gán Roles cho Users ===
        $admin->roles()->sync([$adminRole->id]);
        $manager->roles()->sync([$managerRole->id]);
        $staff->roles()->sync([$staffRole->id]);
        $warehouse->roles()->sync([$warehouseRole->id]);
        $customer->roles()->sync([$customerRole->id]);

        $this->command->info('Created users: admin, manager, staff, warehouse, customer (password: password123)');
    }
}
