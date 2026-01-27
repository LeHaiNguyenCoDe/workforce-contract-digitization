<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Chi phí vận hành
            ['name' => 'Tiền mặt bằng', 'code' => 'RENT', 'type' => 'expense', 'description' => 'Chi phí thuê mặt bằng, cửa hàng, kho'],
            ['name' => 'Tiền điện', 'code' => 'ELECTRIC', 'type' => 'expense', 'description' => 'Chi phí tiền điện'],
            ['name' => 'Tiền nước', 'code' => 'WATER', 'type' => 'expense', 'description' => 'Chi phí tiền nước'],
            ['name' => 'Internet/Điện thoại', 'code' => 'TELECOM', 'type' => 'expense', 'description' => 'Chi phí internet, điện thoại'],

            // Chi phí nhân sự
            ['name' => 'Lương nhân viên', 'code' => 'SALARY', 'type' => 'expense', 'description' => 'Chi phí lương nhân viên'],
            ['name' => 'Bảo hiểm xã hội', 'code' => 'INSURANCE', 'type' => 'expense', 'description' => 'Chi phí BHXH, BHYT, BHTN'],
            ['name' => 'Thưởng/Phụ cấp', 'code' => 'BONUS', 'type' => 'expense', 'description' => 'Chi phí thưởng, phụ cấp nhân viên'],

            // Chi phí vận chuyển & logistics
            ['name' => 'Phí vận chuyển', 'code' => 'SHIPPING', 'type' => 'expense', 'description' => 'Chi phí giao hàng, ship hàng'],
            ['name' => 'Phí COD', 'code' => 'COD_FEE', 'type' => 'expense', 'description' => 'Phí thu hộ COD từ đơn vị vận chuyển'],
            ['name' => 'Đóng gói/Bao bì', 'code' => 'PACKAGING', 'type' => 'expense', 'description' => 'Chi phí mua bao bì, đóng gói'],

            // Chi phí marketing
            ['name' => 'Quảng cáo', 'code' => 'ADS', 'type' => 'expense', 'description' => 'Chi phí quảng cáo Facebook, Google, Tiktok...'],
            ['name' => 'Khuyến mãi/Giảm giá', 'code' => 'PROMO', 'type' => 'expense', 'description' => 'Chi phí chương trình khuyến mãi'],

            // Chi phí khác
            ['name' => 'Văn phòng phẩm', 'code' => 'OFFICE', 'type' => 'expense', 'description' => 'Chi phí văn phòng phẩm, đồ dùng'],
            ['name' => 'Sửa chữa/Bảo trì', 'code' => 'MAINTAIN', 'type' => 'expense', 'description' => 'Chi phí sửa chữa, bảo trì thiết bị'],
            ['name' => 'Thuế/Phí/Lệ phí', 'code' => 'TAX', 'type' => 'expense', 'description' => 'Chi phí thuế, phí, lệ phí các loại'],
            ['name' => 'Chi phí khác', 'code' => 'OTHER_EXP', 'type' => 'expense', 'description' => 'Các chi phí khác'],

            // Khoản thu
            ['name' => 'Doanh thu bán hàng', 'code' => 'SALES', 'type' => 'income', 'description' => 'Thu từ bán hàng (nếu ghi nhận thủ công)'],
            ['name' => 'Thu tiền COD', 'code' => 'COD_INCOME', 'type' => 'income', 'description' => 'Thu tiền COD từ đơn vị vận chuyển'],
            ['name' => 'Thu lãi tiền gửi', 'code' => 'INTEREST', 'type' => 'income', 'description' => 'Lãi tiền gửi ngân hàng'],
            ['name' => 'Hoàn thuế', 'code' => 'TAX_REFUND', 'type' => 'income', 'description' => 'Hoàn thuế VAT, thuế TNDN...'],
            ['name' => 'Thu khác', 'code' => 'OTHER_INC', 'type' => 'income', 'description' => 'Các khoản thu khác'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $this->command->info('Đã tạo ' . count($categories) . ' danh mục thu chi!');
    }
}
