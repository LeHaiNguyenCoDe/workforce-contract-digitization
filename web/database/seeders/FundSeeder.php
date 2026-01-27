<?php

namespace Database\Seeders;

use App\Models\Fund;
use Illuminate\Database\Seeder;

class FundSeeder extends Seeder
{
    public function run(): void
    {
        $funds = [
            [
                'name' => 'Tiền mặt',
                'code' => 'CASH',
                'type' => 'cash',
                'balance' => 0,
                'initial_balance' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Ngân hàng VCB',
                'code' => 'VCB',
                'type' => 'bank',
                'balance' => 0,
                'initial_balance' => 0,
                'bank_name' => 'Vietcombank',
                'bank_account' => '0123456789',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($funds as $fund) {
            Fund::updateOrCreate(
                ['code' => $fund['code']],
                $fund
            );
        }

        $this->command->info('Đã tạo ' . count($funds) . ' quỹ tiền!');
    }
}
