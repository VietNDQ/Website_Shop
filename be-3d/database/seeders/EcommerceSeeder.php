<?php

namespace Database\Seeders;

use App\Models\BienTheSanPham;
use App\Models\ChiTietDonHang;
use App\Models\DanhMuc;
use App\Models\DiaChiNguoiDung;
use App\Models\DonHang;
use App\Models\HinhAnhSanPham;
use App\Models\LichSuTrangThaiDonHang;
use App\Models\MaGiamGia;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\ThanhToan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Nguoi Dung (10 rows)
        $users = [];
        $roles = ['quan_tri', 'quan_ly', 'khach_hang'];

        // Admin
        $users[] = NguoiDung::create([
            'ho_ten' => 'Admin System',
            'email' => 'admin@gmail.com',
            'mat_khau' => Hash::make('password'),
            'vai_tro' => 1,
            'dang_hoat_dong' => true,
        ]);

        // Manager
        $users[] = NguoiDung::create([
            'ho_ten' => 'Manager One',
            'email' => 'manager@gmail.com',
            'mat_khau' => Hash::make('password'),
            'vai_tro' => 2,
            'dang_hoat_dong' => true,
        ]);

        // Customers
        for ($i = 1; $i <= 8; $i++) {
            $users[] = NguoiDung::create([
                'ho_ten' => "Customer $i",
                'email' => "customer$i@gmail.com",
                'mat_khau' => Hash::make('password'),
                'vai_tro' => 3,
                'dang_hoat_dong' => true,
            ]);
        }

        // 2. Dia Chi Nguoi Dung (20 rows)
        foreach ($users as $user) {
            if ($user->vai_tro === 'khach_hang') {
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

        // 3. Danh Muc (10 rows)
        $categories = [
            ['ten' => 'Điện tử', 'slug' => 'dien-tu'],
            ['ten' => 'Thời trang', 'slug' => 'thoi-trang'],
            ['ten' => 'Gia dụng', 'slug' => 'gia-dung'],
            ['ten' => 'Sách', 'slug' => 'sach'],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[] = DanhMuc::create([
                'ten_danh_muc' => $cat['ten'],
                'duong_dan_mau' => $cat['slug'],
            ]);
        }

        // Sub categories
        $subCats = [
            ['id_cha' => $catModels[0]->id, 'ten' => 'Điện thoại', 'slug' => 'dien-thoai'],
            ['id_cha' => $catModels[0]->id, 'ten' => 'Laptop', 'slug' => 'laptop'],
            ['id_cha' => $catModels[1]->id, 'ten' => 'Áo thun', 'slug' => 'ao-thun'],
            ['id_cha' => $catModels[1]->id, 'ten' => 'Quần jean', 'slug' => 'quan-jean'],
        ];

        foreach ($subCats as $sub) {
            $catModels[] = DanhMuc::create([
                'id_danh_muc_cha' => $sub['id_cha'],
                'ten_danh_muc' => $sub['ten'],
                'duong_dan_mau' => $sub['slug'],
            ]);
        }

        // 4. San Pham (20 rows)
        $products = [];
        for ($i = 1; $i <= 20; $i++) {
            $cat = $catModels[array_rand($catModels)];
            $products[] = SanPham::create([
                'id_danh_muc' => $cat->id,
                'ten_san_pham' => "Sản phẩm mẫu $i",
                'gia_co_ban' => rand(100000, 1000000),
                'mo_ta' => "Mô tả cho sản phẩm mẫu $i. Đây là một sản phẩm chất lượng cao.",
            ]);
        }

        // 5. Hinh Anh San Pham (40 rows)
        foreach ($products as $prod) {
            HinhAnhSanPham::create([
                'id_san_pham' => $prod->id,
                'duong_dan_anh' => 'https://picsum.photos/400/400?random=' . $prod->id,
                'la_anh_dai_dien' => true,
                'thu_tu_hien_thi' => 0,
            ]);
            HinhAnhSanPham::create([
                'id_san_pham' => $prod->id,
                'duong_dan_anh' => 'https://picsum.photos/400/400?random=' . ($prod->id + 100),
                'la_anh_dai_dien' => false,
                'thu_tu_hien_thi' => 1,
            ]);
        }

        // 6. Bien The San Pham (40 rows)
        $variants = [];
        foreach ($products as $prod) {
            $variants[] = BienTheSanPham::create([
                'id_san_pham' => $prod->id,
                'ma_kho' => 'SKU-' . $prod->id . '-RED',
                'thuoc_tinh' => ['color' => 'Red', 'size' => 'L'],
                'gia_ban' => $prod->gia_co_ban + 50000,
                'so_luong_ton_kho' => rand(10, 100),
            ]);
            $variants[] = BienTheSanPham::create([
                'id_san_pham' => $prod->id,
                'ma_kho' => 'SKU-' . $prod->id . '-BLUE',
                'thuoc_tinh' => ['color' => 'Blue', 'size' => 'M'],
                'gia_ban' => $prod->gia_co_ban,
                'so_luong_ton_kho' => rand(10, 100),
            ]);
        }

        // 7. Ma Giam Gia (5 rows)
        $vouchers = [];
        $vouchers[] = MaGiamGia::create([
            'ma_code' => 'CHA mung',
            'loai_giam_gia' => 'phan_tram',
            'gia_tri_giam' => 10,
            'don_hang_toi_thieu' => 200000,
            'muc_giam_toi_da' => 50000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
            'gioi_han_su_dung' => 100,
        ]);
        $vouchers[] = MaGiamGia::create([
            'ma_code' => 'GIAM50K',
            'loai_giam_gia' => 'tien_mat',
            'gia_tri_giam' => 50000,
            'don_hang_toi_thieu' => 500000,
            'ngay_bat_dau' => now(),
            'ngay_ket_thuc' => now()->addMonth(),
        ]);

        // 8. Don Hang & Chi Tiet (10 rows)
        $orderStatuses = ['cho_xu_ly', 'dang_chuan_bi', 'dang_giao', 'da_giao', 'da_huy'];
        for ($i = 1; $i <= 10; $i++) {
            $user = $users[array_rand(array_filter($users, fn($u) => $u->vai_tro === 'khach_hang'))];
            $status = $orderStatuses[array_rand($orderStatuses)];

            $order = DonHang::create([
                'id_nguoi_dung' => $user->id,
                'ma_don_hang' => 'ORD-' . strtoupper(Str::random(8)),
                'id_ma_giam_gia' => rand(0, 1) ? $vouchers[array_rand($vouchers)]->id : null,
                'tong_tien_hang' => 0,
                'tien_duoc_giam' => 0,
                'phi_giao_hang' => 30000,
                'tong_thanh_toan' => 0,
                'trang_thai' => $status,
                'dia_chi_giao_hang' => [
                    'ten' => $user->ho_ten,
                    'phone' => '0987654321',
                    'address' => '123 Sample St, District 1, HCM',
                ],
            ]);

            // Add 1-3 details
            $totalHang = 0;
            $numItems = rand(1, 3);
            for ($j = 0; $j < $numItems; $j++) {
                $variant = $variants[array_rand($variants)];
                $qty = rand(1, 2);
                $subTotal = $variant->gia_ban * $qty;
                $totalHang += $subTotal;

                ChiTietDonHang::create([
                    'id_don_hang' => $order->id,
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $variant->sanPham->ten_san_pham . ' (' . implode(', ', $variant->thuoc_tinh) . ')',
                    'so_luong' => $qty,
                    'don_gia' => $variant->gia_ban,
                    'thanh_tien' => $subTotal,
                ]);
            }

            $order->tong_tien_hang = $totalHang;
            $order->tong_thanh_toan = $totalHang + $order->phi_giao_hang;
            $order->save();

            // History
            LichSuTrangThaiDonHang::create([
                'id_don_hang' => $order->id,
                'trang_thai' => 'cho_xu_ly',
                'ghi_chu' => 'Đơn hàng được tạo thành công.',
            ]);

            if ($status !== 'cho_xu_ly') {
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => $status,
                    'ghi_chu' => 'Cập nhật trạng thái tự động từ seeder.',
                ]);
            }

            // Thanh toan
            ThanhToan::create([
                'id_don_hang' => $order->id,
                'phuong_thuc' => 'tien_mat',
                'so_tien' => $order->tong_thanh_toan,
                'trang_thai' => $status === 'da_giao' ? 'da_thanh_toan' : 'chua_thanh_toan',
            ]);
        }
    }
}
