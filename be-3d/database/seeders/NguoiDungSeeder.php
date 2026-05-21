<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\NguoiDung;
use App\Models\DiaChiNguoiDung;

class NguoiDungSeeder extends Seeder
{
    public function run(): void
    {
        // Vai trò: 1 (quan_tri), 2 (quan_ly), 3 (nguoi_dung)
        
        // Admin
        NguoiDung::create([
            'ho_ten' => 'Admin System',
            'email' => 'admin@gmail.com',
            'mat_khau' => Hash::make('123456'),
            'vai_tro' => 1,
            'dang_hoat_dong' => true,
        ]);

        // Manager
        NguoiDung::create([
            'ho_ten' => 'Manager One',
            'email' => 'manager@gmail.com',
            'mat_khau' => Hash::make('123456'),
            'vai_tro' => 2,
            'dang_hoat_dong' => true,
        ]);

        // Customers
        for ($i = 1; $i <= 10; $i++) {
            $email = ($i === 1) ? "nguyenqviet3885@gmail.com" : "nguyenqviet3885+{$i}@gmail.com";
            $user = NguoiDung::create([
                'ho_ten' => $i === 1 ? "Nguyễn Quốc Việt" : "Khách Hàng Mẫu $i",
                'email' => $email,
                'mat_khau' => Hash::make('123456'),
                'vai_tro' => 3,
                'dang_hoat_dong' => true,
                'tao_luc' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2025-12-25 10:00:00')->addDays($i),
            ]);

            DiaChiNguoiDung::create([
                'id_nguoi_dung' => $user->id,
                'so_dien_thoai' => '0987654321',
                'dia_chi_chi_tiet' => '123 ABC Street',
                'thanh_pho' => 'Ho Chi Minh',
                'quan_huyen' => 'District 1',
                'la_mac_dinh' => true,
            ]);
            
            DiaChiNguoiDung::create([
                'id_nguoi_dung' => $user->id,
                'so_dien_thoai' => '0123456789',
                'dia_chi_chi_tiet' => '456 XYZ Road',
                'thanh_pho' => 'Ha Noi',
                'quan_huyen' => 'Hoan Kiem',
                'la_mac_dinh' => false,
            ]);
        }
    }
}
