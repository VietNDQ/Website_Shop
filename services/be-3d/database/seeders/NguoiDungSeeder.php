<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\NguoiDung;
use App\Models\DiaChiNguoiDung;
use Carbon\Carbon;

class NguoiDungSeeder extends Seeder
{
    public function run(): void
    {
        // Mật khẩu mặc định cho tất cả tài khoản
        $defaultPassword = Hash::make('123456');

        // 1. Nhóm Quản trị (Admin) - Vai trò: 1
        NguoiDung::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'ho_ten' => 'Admin System',
                'mat_khau' => $defaultPassword,
                'vai_tro' => 1,
                'dang_hoat_dong' => true,
            ]
        );

        // 2. Nhóm Quản lý và Vận hành (Staff)
        $staffs = [
            ['ho_ten' => 'E-commerce Manager', 'email' => 'manager@gmail.com', 'vai_tro' => 2],
            ['ho_ten' => 'Content Admin', 'email' => 'content@gmail.com', 'vai_tro' => 2],
            ['ho_ten' => 'Order Fulfillment', 'email' => 'khovan@gmail.com', 'vai_tro' => 4],
            ['ho_ten' => 'Customer Service', 'email' => 'cskh@gmail.com', 'vai_tro' => 5],
            ['ho_ten' => 'Digital Marketer', 'email' => 'marketing@gmail.com', 'vai_tro' => 5],
            ['ho_ten' => 'Kế toán', 'email' => 'ketoan@gmail.com', 'vai_tro' => 2],
        ];

        foreach ($staffs as $staff) {
            NguoiDung::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'ho_ten' => $staff['ho_ten'],
                    'mat_khau' => $defaultPassword,
                    'vai_tro' => $staff['vai_tro'],
                    'dang_hoat_dong' => true,
                ]
            );
        }

        // 3. Nhóm Người dùng (Customers) - Vai trò: 3
        $danhSachKhachHang = [
            ['ho_ten' => 'Nguyễn Quốc Việt', 'email' => 'nguyenqviet3885@gmail.com'],
            ['ho_ten' => 'Trần Văn An', 'email' => 'tranvanan@gmail.com'],
            ['ho_ten' => 'Lê Thị Bình', 'email' => 'lethibinh@gmail.com'],
            ['ho_ten' => 'Phạm Văn Cường', 'email' => 'phamvancuong@gmail.com'],
            ['ho_ten' => 'Hoàng Thị Dung', 'email' => 'hoangthidung@gmail.com'],
            ['ho_ten' => 'Vũ Văn Em', 'email' => 'vuvanem@gmail.com'],
            ['ho_ten' => 'Đặng Thị Phương', 'email' => 'dangthiphuong@gmail.com'],
            ['ho_ten' => 'Bùi Văn Giang', 'email' => 'buivangiang@gmail.com'],
            ['ho_ten' => 'Đỗ Thị Hoa', 'email' => 'dothihoa@gmail.com'],
            ['ho_ten' => 'Hồ Văn Inh', 'email' => 'hovaninh@gmail.com'],
            ['ho_ten' => 'Ngô Thị Kim', 'email' => 'ngothikim@gmail.com'],
            ['ho_ten' => 'Dương Văn Long', 'email' => 'duongvanlong@gmail.com'],
            ['ho_ten' => 'Lý Thị Mai', 'email' => 'lythimai@gmail.com'],
            ['ho_ten' => 'Đào Văn Nam', 'email' => 'daovannam@gmail.com'],
            ['ho_ten' => 'Đoàn Thị Oanh', 'email' => 'doanthioanh@gmail.com'],
            ['ho_ten' => 'Vương Văn Phát', 'email' => 'vuongvanphat@gmail.com'],
            ['ho_ten' => 'Trịnh Thị Quỳnh', 'email' => 'trinhthiquynh@gmail.com'],
            ['ho_ten' => 'Lâm Văn Rạng', 'email' => 'lamvanrang@gmail.com'],
            ['ho_ten' => 'Phùng Thị Sen', 'email' => 'phungthisen@gmail.com'],
            ['ho_ten' => 'Mai Văn Tài', 'email' => 'maivantai@gmail.com'],
        ];

        $i = 1;
        foreach ($danhSachKhachHang as $khachHang) {
            $user = NguoiDung::updateOrCreate(
                ['email' => $khachHang['email']],
                [
                    'ho_ten' => $khachHang['ho_ten'],
                    'mat_khau' => $defaultPassword,
                    'vai_tro' => 3,
                    'dang_hoat_dong' => true,
                    'tao_luc' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-12-25 10:00:00')->addDays($i),
                ]
            );

            // Địa chỉ mặc định
            DiaChiNguoiDung::updateOrCreate(
                [
                    'id_nguoi_dung' => $user->id,
                    'la_mac_dinh' => true
                ],
                [
                    'so_dien_thoai' => '09876543' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'dia_chi_chi_tiet' => $i * 12 . ' Nguyễn Văn Linh',
                    'thanh_pho' => 'Đà Nẵng',
                    'quan_huyen' => 'Hải Châu',
                ]
            );

            // Địa chỉ phụ
            DiaChiNguoiDung::updateOrCreate(
                [
                    'id_nguoi_dung' => $user->id,
                    'la_mac_dinh' => false
                ],
                [
                    'so_dien_thoai' => '01234567' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'dia_chi_chi_tiet' => $i * 34 . ' Lê Duẩn',
                    'thanh_pho' => 'Đà Nẵng',
                    'quan_huyen' => 'Thanh Khê',
                ]
            );

            $i++;
        }
    }
}
