<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Xưởng Gốm Bát Tràng Hùng Thịnh',
                'contact_person' => 'Nguyễn Văn Hùng',
                'email' => 'hungthinh@battrang.vn',
                'phone' => '0912345678',
                'address' => 'Xóm 3, Bát Tràng, Gia Lâm, Hà Nội',
                'status' => 'active',
                'rating' => 5.0,
            ],
            [
                'name' => 'Công ty Gốm sứ Minh Long I',
                'contact_person' => 'Trần Minh Hoà',
                'email' => 'info@minhlong.com',
                'phone' => '02743668899',
                'address' => '333 Hưng Lộc, Hưng Định, Thuận An, Bình Dương',
                'status' => 'active',
                'rating' => 5.0,
            ],
            [
                'name' => 'Gốm sứ Chu Đậu - Hải Dương',
                'contact_person' => 'Lê Thị Thu',
                'email' => 'chudau@pottery.vn',
                'phone' => '02203853110',
                'address' => 'Nam Sách, Hải Dương',
                'status' => 'active',
                'rating' => 4.5,
            ],
            [
                'name' => 'Làng Gốm Bàu Trúc',
                'contact_person' => 'Đàng Thị Phan',
                'email' => 'bautruc@ninhthuan.gov.vn',
                'phone' => '0987654321',
                'address' => 'Ninh Phước, Ninh Thuận',
                'status' => 'active',
                'rating' => 4.0,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('Đã tạo ' . count($suppliers) . ' nhà cung cấp!');
    }
}
