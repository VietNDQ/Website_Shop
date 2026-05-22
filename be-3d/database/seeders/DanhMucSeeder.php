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
                'ten_danh_muc' => 'Mô hình Anime & Figure',
                'duong_dan_mau' => 'mo-hinh-anime-figure',
            ],
            [
                'ten_danh_muc' => 'Mô hình Lắp ráp (Gundam/Plamo)',
                'duong_dan_mau' => 'mo-hinh-lap-rap',
            ],
            [
                'ten_danh_muc' => 'Mô hình Xe cộ & Quân sự',
                'duong_dan_mau' => 'mo-hinh-xe-co-quan-su',
            ],
            [
                'ten_danh_muc' => 'Mô hình In 3D (FDM/Resin)',
                'duong_dan_mau' => 'mo-hinh-in-3d',
            ],
            [
                'ten_danh_muc' => 'Mô hình Kiến trúc & Diorama',
                'duong_dan_mau' => 'mo-hinh-kien-truc-diorama',
            ],
            [
                'ten_danh_muc' => 'Dụng cụ lắp ráp & Cắt gọt',
                'duong_dan_mau' => 'dung-cu-lap-rap-cat-got',
            ],
            [
                'ten_danh_muc' => 'Sơn & Hóa chất mô hình',
                'duong_dan_mau' => 'son-va-hoa-chat-mo-hinh',
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
