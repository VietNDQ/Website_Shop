<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DanhMuc;

class DanhMucSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'ten_danh_muc' => 'Mô hình',
                'duong_dan_mau' => 'mo-hinh',
            ],
            [
                'ten_danh_muc' => 'Dụng cụ cá nhân',
                'duong_dan_mau' => 'dung-cu-ca-nhan',
            ],
        ];

        foreach ($categories as $cat) {
            DanhMuc::updateOrCreate(
                ['duong_dan_mau' => $cat['duong_dan_mau']],
                ['ten_danh_muc' => $cat['ten_danh_muc']]
            );
        }
    }
}
