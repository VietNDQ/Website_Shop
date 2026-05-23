<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\MaGiamGia;
use Illuminate\Support\Facades\Schema;
 
class MaGiamGiaSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa sạch bảng liên quan trước để tránh lỗi ràng buộc hoặc trùng lặp unique
        Schema::disableForeignKeyConstraints();
        \App\Models\NguoiDungVoucher::truncate();
        MaGiamGia::truncate();
        Schema::enableForeignKeyConstraints();
 
        // 1. Voucher Đại trà (Public) - Chiết khấu theo %
        MaGiamGia::create([
            'ma_code' => 'CHAMUNG10',
            'loai_giam_gia' => 'phan_tram',
            'gia_tri_giam' => 10,
            'don_hang_toi_thieu' => 200000,
            'muc_giam_toi_da' => 50000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
            'gioi_han_su_dung' => 100,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 1000000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'public',
            'dang_hoat_dong' => true,
        ]);
 
        // 2. Voucher Đại trà (Public) - Chiết khấu theo số tiền cố định
        MaGiamGia::create([
            'ma_code' => 'GIAM50K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 50000,
            'don_hang_toi_thieu' => 500000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
            'gioi_han_su_dung' => 50,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 2500000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'public',
            'dang_hoat_dong' => true,
        ]);
 
        // 3. Voucher Thu thập (Claimable) - Chiết khấu cố định
        MaGiamGia::create([
            'ma_code' => 'THUTHAP20K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 20000,
            'don_hang_toi_thieu' => 150000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonths(2),
            'gioi_han_su_dung' => 200,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 4000000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'claimable',
            'dang_hoat_dong' => true,
        ]);
 
        // 4. Voucher Thu thập (Claimable) - Chiết khấu theo %
        MaGiamGia::create([
            'ma_code' => 'THUTHAP15',
            'loai_giam_gia' => 'phan_tram',
            'gia_tri_giam' => 15,
            'don_hang_toi_thieu' => 300000,
            'muc_giam_toi_da' => 60000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonths(2),
            'gioi_han_su_dung' => 150,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 9000000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'claimable',
            'dang_hoat_dong' => true,
        ]);
 
        // 5. Voucher Phân bổ kín (Targeted) - Siêu ưu đãi VIP
        MaGiamGia::create([
            'ma_code' => 'VIPGIAM100K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 100000,
            'don_hang_toi_thieu' => 1000000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonths(3),
            'gioi_han_su_dung' => 10,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 1000000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'targeted',
            'dang_hoat_dong' => true,
        ]);
 
        // 6. Voucher Phân bổ kín (Targeted) - Tặng quà tri ân
        MaGiamGia::create([
            'ma_code' => 'QUATANG50K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 50000,
            'don_hang_toi_thieu' => 400000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonths(3),
            'gioi_han_su_dung' => 20,
            'so_lan_da_dung' => 0,
            'ngan_sach' => 1000000.00,
            'ngan_sach_da_dung' => 0.00,
            'hinh_thuc_phat_hanh' => 'targeted',
            'dang_hoat_dong' => true,
        ]);
    }
}
