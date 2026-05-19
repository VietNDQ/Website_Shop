<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NguoiDung;
use App\Models\MaGiamGia;
use App\Models\BienTheSanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\LichSuTrangThaiDonHang;
use App\Models\ThanhToan;
use App\Models\NhatKyHoatDong;
use Carbon\Carbon;

class DonHangSeeder extends Seeder
{
    public function run(): void
    {
        $nguoiDungs = NguoiDung::where('vai_tro', 3)->get()->all();
        $vouchers = MaGiamGia::all()->all();
        $variants = BienTheSanPham::with('sanPham')->get()->all();

        if (empty($nguoiDungs) || empty($variants)) {
            return;
        }

        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2025-12-30 08:00:00');
        $endDate = Carbon::createFromFormat('Y-m-d H:i:s', '2026-05-15 17:00:00');
        $totalDays = $startDate->diffInDays($endDate);

        // Tạo khoảng 18 đơn hàng sao cho tổng doanh thu ~ 10tr
        for ($i = 1; $i <= 18; $i++) {
            // Random ngày
            $orderDate = $startDate->copy()->addDays(rand(0, $totalDays))->addHours(rand(0, 12));
            
            $user = $nguoiDungs[array_rand($nguoiDungs)];
            // Để doanh thu cao, hầu hết sẽ cho là 'da_giao'
            $status = rand(1, 10) > 2 ? 'da_giao' : (rand(1, 10) > 5 ? 'dang_giao' : 'cho_xu_ly');

            $order = DonHang::create([
                'id_nguoi_dung' => $user->id,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(8)),
                'id_ma_giam_gia' => (!empty($vouchers) && rand(0, 1)) ? $vouchers[array_rand($vouchers)]->id : null,
                'tong_tien_hang' => 0,
                'tien_duoc_giam' => 0,
                'phi_giao_hang' => 30000,
                'tong_thanh_toan' => 0,
                'trang_thai' => $status,
                'dia_chi_giao_hang' => [
                    'ten' => $user->ho_ten,
                    'phone' => '0987654321',
                    'address' => '123 Sample St, HCM',
                ],
                'tao_luc' => $orderDate,
                'cap_nhat_luc' => $orderDate->copy()->addDays(2),
            ]);

            // Mỗi đơn 1-2 sản phẩm (để tổng 18 đơn ~ 10tr, trung bình 550k/đơn)
            $totalHang = 0;
            $numItems = rand(1, 2);
            for ($j = 0; $j < $numItems; $j++) {
                $variant = $variants[array_rand($variants)];
                $qty = 1; 
                $subTotal = $variant->gia_ban * $qty;
                $totalHang += $subTotal;

                ChiTietDonHang::create([
                    'id_don_hang' => $order->id,
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . implode(', ', $variant->thuoc_tinh ?? []) . ')',
                    'so_luong' => $qty,
                    'don_gia' => $variant->gia_ban,
                    'thanh_tien' => $subTotal,
                    'tao_luc' => $orderDate,
                ]);
            }

            $order->tong_tien_hang = $totalHang;
            $order->tong_thanh_toan = $totalHang + $order->phi_giao_hang;
            $order->save();

            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => 'cho_xu_ly',
                'ghi_chu' => 'Đơn hàng được tạo thành công.',
                'tao_luc' => $orderDate,
            ]);

            if ($status !== 'cho_xu_ly') {
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => $status,
                    'ghi_chu' => 'Cập nhật trạng thái tự động từ seeder.',
                    'tao_luc' => $orderDate->copy()->addDays(1),
                ]);
            }

            // Thanh toan
            ThanhToan::create([
                'id_don_hang' => $order->id,
                'phuong_thuc' => 'tien_mat',
                'so_tien' => $order->tong_thanh_toan,
                'trang_thai' => $status === 'da_giao' ? 'da_thanh_toan' : 'chua_thanh_toan',
                'ngay_thanh_toan' => $status === 'da_giao' ? $orderDate : null,
                'tao_luc' => $orderDate,
                'cap_nhat_luc' => $orderDate,
            ]);
            
            // Log hoạt động
            if ($status === 'da_giao') {
                NhatKyHoatDong::create([
                    'id_nguoi_dung' => $user->id,
                    'ten_nguoi_dung' => 'Hệ thống',
                    'hanh_dong' => 'Đơn hàng ' . $order->ma_don_hang . ' đã giao thành công',
                    'mau_sac' => '#22c55e',
                    'tao_luc' => $orderDate->copy()->addDays(2),
                ]);
            }
        }
    }
}
