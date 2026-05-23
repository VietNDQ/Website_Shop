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

        // Clean existing data
        NhatKyHoatDong::query()->delete();
        ThanhToan::query()->delete();
        LichSuTrangThaiDonHang::query()->delete();
        ChiTietDonHang::query()->delete();
        DonHang::query()->delete();

        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2025-12-29 08:00:00');
        $endDate = Carbon::createFromFormat('Y-m-d H:i:s', '2026-05-22 22:00:00');
        $totalSeconds = $startDate->diffInSeconds($endDate);

        // Pick a target total between 110M and 130M VND
        $targetTotal = mt_rand(115000000, 125000000);
        $accumulatedTotal = 0;
        $ordersData = [];

        while ($accumulatedTotal < $targetTotal) {
            $user = $nguoiDungs[array_rand($nguoiDungs)];
            
            // Pick 1 to 3 items
            $orderItems = [];
            $orderTotalHang = 0;
            $numItems = rand(1, 3);
            $selectedVariantIds = [];
            
            for ($j = 0; $j < $numItems; $j++) {
                // Avoid duplicates in the same order
                $variant = null;
                for ($attempt = 0; $attempt < 10; $attempt++) {
                    $tempVariant = $variants[array_rand($variants)];
                    if (!in_array($tempVariant->id, $selectedVariantIds)) {
                        $variant = $tempVariant;
                        break;
                    }
                }
                if (!$variant) {
                    $variant = $variants[array_rand($variants)];
                }
                $selectedVariantIds[] = $variant->id;

                // Determine quantity
                $qty = 1;
                if ($variant->gia_ban < 200000) {
                    $qty = rand(1, 3);
                } elseif ($variant->gia_ban < 500000) {
                    $qty = rand(1, 2);
                }
                
                $subTotal = $variant->gia_ban * $qty;
                
                $orderItems[] = [
                    'variant' => $variant,
                    'qty' => $qty,
                    'don_gia' => $variant->gia_ban,
                    'subtotal' => $subTotal
                ];
                $orderTotalHang += $subTotal;
            }
            
            // Calculate voucher discount
            $voucher = null;
            $tienDuocGiam = 0;
            if (!empty($vouchers) && rand(1, 10) <= 2) { // 20% chance
                $voucher = $vouchers[array_rand($vouchers)];
                if ($orderTotalHang >= $voucher->don_hang_toi_thieu) {
                    if ($voucher->loai_giam_gia === 'phan_tram') {
                        $tienDuocGiam = $orderTotalHang * ($voucher->gia_tri_giam / 100);
                        if ($voucher->muc_giam_toi_da) {
                            $tienDuocGiam = min($tienDuocGiam, $voucher->muc_giam_toi_da);
                        }
                    } else {
                        $tienDuocGiam = $voucher->gia_tri_giam;
                    }
                } else {
                    $voucher = null;
                }
            }
            
            $phiGiaoHang = $orderTotalHang > 500000 ? 0 : 30000;
            $tongThanhToan = $orderTotalHang - $tienDuocGiam + $phiGiaoHang;
            
            // Check if this order overshoots our maximum threshold (130M)
            if ($accumulatedTotal + $tongThanhToan > 130000000) {
                // If we are already in the target range, we can just stop
                if ($accumulatedTotal >= 110000000) {
                    break;
                }
                
                // Otherwise, force a small order
                $cheapVariants = array_filter($variants, function($v) {
                    return $v->gia_ban < 100000;
                });
                if (empty($cheapVariants)) {
                    $cheapVariants = $variants;
                }
                $variant = $cheapVariants[array_rand($cheapVariants)];
                $qty = 1;
                $subTotal = $variant->gia_ban * $qty;
                $orderItems = [[
                    'variant' => $variant,
                    'qty' => $qty,
                    'don_gia' => $variant->gia_ban,
                    'subtotal' => $subTotal
                ]];
                $orderTotalHang = $subTotal;
                $voucher = null;
                $tienDuocGiam = 0;
                $phiGiaoHang = 30000;
                $tongThanhToan = $orderTotalHang + $phiGiaoHang;
                
                if ($accumulatedTotal + $tongThanhToan > 130000000) {
                    break;
                }
            }
            
            // Pick a random order date
            $randomSeconds = mt_rand(0, $totalSeconds);
            $orderDate = $startDate->copy()->addSeconds($randomSeconds);
            
            $ordersData[] = [
                'user' => $user,
                'voucher' => $voucher,
                'order_items' => $orderItems,
                'tong_tien_hang' => $orderTotalHang,
                'tien_duoc_giam' => $tienDuocGiam,
                'phi_giao_hang' => $phiGiaoHang,
                'tong_thanh_toan' => $tongThanhToan,
                'order_date' => $orderDate
            ];
            
            $accumulatedTotal += $tongThanhToan;
        }

        // Sort orders chronologically so database auto-increment IDs match date order
        usort($ordersData, function($a, $b) {
            return $a['order_date']->timestamp <=> $b['order_date']->timestamp;
        });

        // Write to database
        foreach ($ordersData as $oData) {
            $user = $oData['user'];
            $orderDate = $oData['order_date'];
            
            // status distribution: ~80% da_giao, ~10% dang_giao, ~10% cho_xu_ly
            $status = rand(1, 10) > 2 ? 'da_giao' : (rand(1, 10) > 5 ? 'dang_giao' : 'cho_xu_ly');
            
            // Fetch default address if possible
            $diaChi = $user->diaChis()->where('la_mac_dinh', true)->first();
            $diaChiArr = [
                'ten' => $user->ho_ten,
                'so_dien_thoai' => $diaChi ? $diaChi->so_dien_thoai : '0987654321',
                'phone' => $diaChi ? $diaChi->so_dien_thoai : '0987654321',
                'dia_chi' => $diaChi ? ($diaChi->dia_chi_chi_tiet . ', ' . $diaChi->quan_huyen . ', ' . $diaChi->thanh_pho) : '123 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
                'address' => $diaChi ? ($diaChi->dia_chi_chi_tiet . ', ' . $diaChi->quan_huyen . ', ' . $diaChi->thanh_pho) : '123 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
            ];

            $order = DonHang::create([
                'id_nguoi_dung' => $user->id,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(8)),
                'id_ma_giam_gia' => $oData['voucher'] ? $oData['voucher']->id : null,
                'tong_tien_hang' => $oData['tong_tien_hang'],
                'tien_duoc_giam' => $oData['tien_duoc_giam'],
                'phi_giao_hang' => $oData['phi_giao_hang'],
                'tong_thanh_toan' => $oData['tong_thanh_toan'],
                'trang_thai' => $status,
                'dia_chi_giao_hang' => $diaChiArr,
                'tao_luc' => $orderDate,
                'cap_nhat_luc' => $status === 'da_giao' ? $orderDate->copy()->addDays(rand(1, 3)) : $orderDate,
            ]);

            // Save details
            foreach ($oData['order_items'] as $item) {
                $variant = $item['variant'];
                ChiTietDonHang::create([
                    'id_don_hang' => $order->id,
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . implode(', ', $variant->thuoc_tinh ?? []) . ')',
                    'so_luong' => $item['qty'],
                    'don_gia' => $item['don_gia'],
                    'thanh_tien' => $item['subtotal'],
                    'tao_luc' => $orderDate,
                ]);
            }

            // Status history
            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => 'cho_xu_ly',
                'ghi_chu' => 'Đơn hàng được tạo thành công.',
                'tao_luc' => $orderDate,
            ]);

            if ($status !== 'cho_xu_ly') {
                $historyDate = $status === 'da_giao' ? $order->cap_nhat_luc : $orderDate->copy()->addHours(rand(2, 12));
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => $status,
                    'ghi_chu' => 'Cập nhật trạng thái tự động từ hệ thống.',
                    'tao_luc' => $historyDate,
                ]);
            }

            // Payment record
            ThanhToan::create([
                'id_don_hang' => $order->id,
                'phuong_thuc' => 'tien_mat',
                'so_tien' => $order->tong_thanh_toan,
                'trang_thai' => $status === 'da_giao' ? 'da_thanh_toan' : 'chua_thanh_toan',
                'ngay_thanh_toan' => $status === 'da_giao' ? $order->cap_nhat_luc : null,
                'tao_luc' => $orderDate,
                'cap_nhat_luc' => $status === 'da_giao' ? $order->cap_nhat_luc : $orderDate,
            ]);
            
            // Log activity
            if ($status === 'da_giao') {
                NhatKyHoatDong::create([
                    'id_nguoi_dung' => $user->id,
                    'ten_nguoi_dung' => 'Hệ thống',
                    'hanh_dong' => 'Đơn hàng ' . $order->ma_don_hang . ' đã giao thành công',
                    'mau_sac' => '#22c55e',
                    'tao_luc' => $order->cap_nhat_luc,
                ]);
            }
        }

        // Update statistics for each user
        foreach (NguoiDung::all() as $user) {
            $totalSpent = DonHang::where('id_nguoi_dung', $user->id)
                ->whereIn('trang_thai', ['hoan_thanh', 'da_giao'])
                ->sum('tong_thanh_toan');
            $points = (int) floor($totalSpent / 1000);
            
            $user->update([
                'tong_chi_tieu' => $totalSpent,
                'diem_thanh_vien' => $points,
            ]);
        }
    }
}
