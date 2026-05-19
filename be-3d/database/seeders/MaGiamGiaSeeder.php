<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaGiamGia;

class MaGiamGiaSeeder extends Seeder
{
    public function run(): void
    {
        MaGiamGia::create([
            'ma_code' => 'CHA mung',
            'loai_giam_gia' => 'phan_tram',
            'gia_tri_giam' => 10,
            'don_hang_toi_thieu' => 200000,
            'muc_giam_toi_da' => 50000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
            'gioi_han_su_dung' => 100,
        ]);
        
        MaGiamGia::create([
            'ma_code' => 'GIAM50K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 50000,
            'don_hang_toi_thieu' => 500000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
        ]);
    }
}
