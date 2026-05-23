<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaiKhoanNganHang;

class TaiKhoanNganHangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaiKhoanNganHang::updateOrCreate(
            ['bank_account_no' => '109878678022'],
            [
                'bank_id' => 'vietinbank',
                'bank_account_name' => 'Nguyễn Quốc Việt',
                'is_active' => true,
            ]
        );
    }
}
