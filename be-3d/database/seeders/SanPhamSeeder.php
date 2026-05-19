<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DanhMuc;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use App\Models\BienTheSanPham;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
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

        // 5. Hinh Anh San Pham & Bien The San Pham
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

            BienTheSanPham::create([
                'id_san_pham' => $prod->id,
                'ma_kho' => 'SKU-' . $prod->id . '-RED',
                'thuoc_tinh' => ['color' => 'Red', 'size' => 'L'],
                'gia_ban' => $prod->gia_co_ban + 50000,
                'so_luong_ton_kho' => rand(10, 100),
            ]);
            
            BienTheSanPham::create([
                'id_san_pham' => $prod->id,
                'ma_kho' => 'SKU-' . $prod->id . '-BLUE',
                'thuoc_tinh' => ['color' => 'Blue', 'size' => 'M'],
                'gia_ban' => $prod->gia_co_ban,
                'so_luong_ton_kho' => rand(10, 100),
            ]);
        }
    }
}
