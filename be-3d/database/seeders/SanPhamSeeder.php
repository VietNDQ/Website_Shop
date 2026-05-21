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
        $moHinh = DanhMuc::where('duong_dan_mau', 'mo-hinh')->first();
        $dungCu = DanhMuc::where('duong_dan_mau', 'dung-cu-ca-nhan')->first();

        // Fallback in case DanhMucSeeder has not run yet (though it should run first)
        if (!$moHinh) {
            $moHinh = DanhMuc::create(['ten_danh_muc' => 'Mô hình', 'duong_dan_mau' => 'mo-hinh']);
        }
        if (!$dungCu) {
            $dungCu = DanhMuc::create(['ten_danh_muc' => 'Dụng cụ cá nhân', 'duong_dan_mau' => 'dung-cu-ca-nhan']);
        }

        // Clean existing product-related data to avoid duplicate key errors or mismatched references
        BienTheSanPham::query()->delete();
        HinhAnhSanPham::query()->delete();
        SanPham::query()->delete();

        // Cập nhật dữ liệu dựa trên hình ảnh mô hình Pokemon
        $moHinhProducts = [
            [
                'ten_san_pham' => 'Mô hình Pokemon kiểu dáng nằm ngủ điệu đà dễ thương bao gồm hộp đựng nguyên seal',
                'gia_co_ban' => 85000,
                'mo_ta' => 'Mô hình Pokemon kiểu dáng nằm ngủ cực kỳ dễ thương. Sản phẩm bao gồm hộp đựng nguyên seal. Rất thích hợp làm quà tặng, sưu tầm hoặc trang trí bàn làm việc.',
                'images' => [
                    'https://xuhishop.vn/upload/products/4381645371475_xuhi/pikachu_0.jpg',
                    'https://xuhishop.vn/upload/products/2571679244462_xuhi/mewto_0.jpg',
                    'https://xuhishop.vn/upload/products/2501621395545_xuhi/lucario-0.jpg',
                ],
                'variants' => [
                    ['color' => 'Pikachu(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 0, 'stock' => 50],
                    ['color' => 'Ếch kỳ diệu(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 0, 'stock' => 45],
                    ['color' => 'Snorlax(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 10000, 'stock' => 30],
                    ['color' => 'Eve(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 5000, 'stock' => 40],
                    ['color' => 'Jirachi(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 35000, 'stock' => 20],
                    ['color' => 'Komala(nằm ngủ)', 'size' => 'Tiêu chuẩn', 'price_offset' => 0, 'stock' => 55],
                    ['color' => 'Rồng lửa', 'size' => 'Tiêu chuẩn', 'price_offset' => 65000, 'stock' => 15],
                    ['color' => 'Vịt', 'size' => 'Tiêu chuẩn', 'price_offset' => 25000, 'stock' => 25],
                    ['color' => 'Combo 5 mô hình ngẫu nhiên', 'size' => 'Combo', 'price_offset' => 195000, 'stock' => 10]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Pikachu Trận Chiến Sét Đánh',
                'gia_co_ban' => 95000,
                'mo_ta' => 'Mô hình Pikachu với các hiệu ứng sấm sét sống động xung quanh, biểu cảm chiến đấu dũng mãnh, nhựa PVC cao cấp.',
                'images' => [
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/08/kinh-nghiem-chon-mo-hinh-pokemon-cho-moi-lua-tuoi-2.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-gengar-cos-sasuke-3.jpg',
                ],
                'variants' => [
                    ['color' => 'Tiêu chuẩn', 'size' => '10cm', 'price_offset' => 0, 'stock' => 50],
                    ['color' => 'Biểu cảm giận dữ', 'size' => '10cm', 'price_offset' => 10000, 'stock' => 30],
                    ['color' => 'Có kèm hiệu ứng tia sét', 'size' => '10cm', 'price_offset' => 30000, 'stock' => 20],
                    ['color' => 'Mạ vàng cao cấp', 'size' => '12cm', 'price_offset' => 50000, 'stock' => 10]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Bulbasaur Ếch Kỳ Diệu Chậu Cây',
                'gia_co_ban' => 88000,
                'mo_ta' => 'Mô hình Bulbasaur cõng chậu cây nhỏ trên lưng vô cùng sáng tạo, thích hợp để trang trí bàn học hoặc văn phòng.',
                'images' => [
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/08/kinh-nghiem-chon-mo-hinh-pokemon-cho-moi-lua-tuoi-1.jpg',
                    'https://www.gundambienhoa.com/storage/app/uploads/public/685/3d2/2d5/6853d22d59d90953739154.jpg',
                    'https://pos.nvncdn.com/f625c0-33854/ps/20241007_VQoYrNwMrb.jpeg',
                ],
                'variants' => [
                    ['color' => 'Xanh lá nhạt', 'size' => '8cm', 'price_offset' => 0, 'stock' => 60],
                    ['color' => 'Trang trí nơ hồng', 'size' => '8cm', 'price_offset' => 10000, 'stock' => 30],
                    ['color' => 'Chậu cây thật mini', 'size' => '10cm', 'price_offset' => 40000, 'stock' => 20],
                    ['color' => 'Xanh lá đậm (Shiny)', 'size' => '8cm', 'price_offset' => 25000, 'stock' => 15]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Snorlax Ham Ăn Siêu Bự',
                'gia_co_ban' => 120000,
                'mo_ta' => 'Mô hình chú Snorlax tròn trịa siêu đáng yêu đang nằm ôm quả táo, kích thước đầm tay và chất liệu sơn mịn màng.',
                'images' => [
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-pikachu-cosplay-ninja-ban-cao-cap-1.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-pikachu-cosplay-nyasu-ban-cao-cap-2.jpg',
                ],
                'variants' => [
                    ['color' => 'Xanh đậm truyền thống', 'size' => '15cm', 'price_offset' => 0, 'stock' => 40],
                    ['color' => 'Xanh ngọc đặc biệt', 'size' => '15cm', 'price_offset' => 15000, 'stock' => 25],
                    ['color' => 'Mặc áo ngủ Pyjama', 'size' => '16cm', 'price_offset' => 35000, 'stock' => 20]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Eevee Tinh Nghịch Đa Hệ',
                'gia_co_ban' => 90000,
                'mo_ta' => 'Mô hình Eevee thiết kế nhỏ gọn với nét mặt tươi vui đón chào ngày mới, các chi tiết lông cổ bồng bềnh.',
                'images' => [
                    'https://lacdau.com/media/product/2332-4.jpg',
                    'https://xuhishop.vn/upload/products/4381645371475_xuhi/pikachu_0.jpg',
                ],
                'variants' => [
                    ['color' => 'Nâu hạt dẻ', 'size' => '12cm', 'price_offset' => 0, 'stock' => 80],
                    ['color' => 'Hệ Nước (Vaporeon)', 'size' => '12cm', 'price_offset' => 20000, 'stock' => 30],
                    ['color' => 'Hệ Lửa (Flareon)', 'size' => '12cm', 'price_offset' => 20000, 'stock' => 30],
                    ['color' => 'Hệ Lôi (Jolteon)', 'size' => '12cm', 'price_offset' => 20000, 'stock' => 30]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Jirachi Điều Ước Ngôi Sao',
                'gia_co_ban' => 110000,
                'mo_ta' => 'Mô hình Pokemon huyền thoại Jirachi mang lại may mắn và những điều ước tốt lành. Chi tiết ruy băng xanh bay bổng tinh tế.',
                'images' => [
                    'https://xuhishop.vn/upload/products/2571679244462_xuhi/mewto_0.jpg',
                    'https://xuhishop.vn/upload/products/2501621395545_xuhi/lucario-0.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/08/kinh-nghiem-chon-mo-hinh-pokemon-cho-moi-lua-tuoi-2.jpg',
                ],
                'variants' => [
                    ['color' => 'Vàng sao tiêu chuẩn', 'size' => '9cm', 'price_offset' => 0, 'stock' => 30],
                    ['color' => 'Phiên bản mắt nhắm ngủ', 'size' => '9cm', 'price_offset' => 10000, 'stock' => 25],
                    ['color' => 'Kèm đế mây bay bổng', 'size' => '11cm', 'price_offset' => 25000, 'stock' => 15]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Komala Ôm Gỗ Bình Yên',
                'gia_co_ban' => 85000,
                'mo_ta' => 'Mô hình chú gấu túi Komala say ngủ ôm chặt khúc gỗ nhỏ. Biểu cảm bình yên thư thái giúp giải tỏa căng thẳng.',
                'images' => [
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-gengar-cos-sasuke-3.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/08/kinh-nghiem-chon-mo-hinh-pokemon-cho-moi-lua-tuoi-1.jpg',
                ],
                'variants' => [
                    ['color' => 'Xám tiêu chuẩn', 'size' => '10cm', 'price_offset' => 0, 'stock' => 45],
                    ['color' => 'Thân gỗ hoa văn', 'size' => '10cm', 'price_offset' => 15000, 'stock' => 20],
                    ['color' => 'Mũ Noel dễ thương', 'size' => '10cm', 'price_offset' => 20000, 'stock' => 15]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Gengar Quỷ Bóng Đêm',
                'gia_co_ban' => 115000,
                'mo_ta' => 'Mô hình Gengar hệ Ma cực ngầu với nụ cười đặc trưng đầy tinh quái, chất liệu nhựa cứng bền bỉ và màu sơn đều tay.',
                'images' => [
                    'https://www.gundambienhoa.com/storage/app/uploads/public/685/3d2/2d5/6853d22d59d90953739154.jpg',
                    'https://pos.nvncdn.com/f625c0-33854/ps/20241007_VQoYrNwMrb.jpeg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-pikachu-cosplay-ninja-ban-cao-cap-1.jpg',
                ],
                'variants' => [
                    ['color' => 'Tím đậm ma quái', 'size' => '12cm', 'price_offset' => 0, 'stock' => 50],
                    ['color' => 'Biểu cảm thè lưỡi nghịch ngợm', 'size' => '12cm', 'price_offset' => 15000, 'stock' => 35],
                    ['color' => 'Tím phát quang', 'size' => '12cm', 'price_offset' => 45000, 'stock' => 20],
                    ['color' => 'Kèm hiệu ứng bóng tối', 'size' => '13cm', 'price_offset' => 60000, 'stock' => 10]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Togepi Trứng Cưng Hạnh Phúc',
                'gia_co_ban' => 80000,
                'mo_ta' => 'Mô hình bé Togepi nửa trong vỏ trứng giơ tay vui mừng cực kỳ đáng yêu, màu sắc tươi tắn trung thực.',
                'images' => [
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-pikachu-cosplay-nyasu-ban-cao-cap-2.jpg',
                    'https://lacdau.com/media/product/2332-4.jpg',
                ],
                'variants' => [
                    ['color' => 'Vỏ trứng màu trắng sữa', 'size' => '8cm', 'price_offset' => 0, 'stock' => 55],
                    ['color' => 'Vỏ trứng vẽ tay ánh kim', 'size' => '8cm', 'price_offset' => 20000, 'stock' => 20],
                    ['color' => 'Kèm nôi ngủ lót bông', 'size' => '10cm', 'price_offset' => 35000, 'stock' => 15]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Psyduck Bối Rối Ôm Đầu',
                'gia_co_ban' => 87000,
                'mo_ta' => 'Mô hình vịt ngáo Psyduck ôm đầu với vẻ mặt ngơ ngác thương hiệu, tạo hình hài hước đem lại tiếng cười vui nhộn.',
                'images' => [
                    'https://xuhishop.vn/upload/products/4381645371475_xuhi/pikachu_0.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/02/mo-hinh-pokemon-gengar-cos-sasuke-3.jpg',
                    'https://nguyenngocfigure.com/wp-content/uploads/2025/08/kinh-nghiem-chon-mo-hinh-pokemon-cho-moi-lua-tuoi-1.jpg',
                ],
                'variants' => [
                    ['color' => 'Vàng chanh nguyên bản', 'size' => '11cm', 'price_offset' => 0, 'stock' => 70],
                    ['color' => 'Mắt kính siêu ngố', 'size' => '11cm', 'price_offset' => 15000, 'stock' => 30],
                    ['color' => 'Mặc áo phao bơi', 'size' => '12cm', 'price_offset' => 25000, 'stock' => 25],
                    ['color' => 'Bản siêu bự khổng lồ', 'size' => '20cm', 'price_offset' => 120000, 'stock' => 10]
                ]
            ],
            [
                'ten_san_pham' => 'Mô hình Rồng Lửa Charizard Chiến Đấu',
                'gia_co_ban' => 150000,
                'mo_ta' => 'Mô hình Charizard giương cánh dũng mãnh, đuôi có ngọn lửa rực cháy sống động, chi tiết tạo hình cơ bắp vô cùng chân thật.',
                'images' => [
                    'https://xuhishop.vn/upload/products/2571679244462_xuhi/mewto_0.jpg',
                    'https://www.gundambienhoa.com/storage/app/uploads/public/685/3d2/2d5/6853d22d59d90953739154.jpg',
                ],
                'variants' => [
                    ['color' => 'Cam tiêu chuẩn', 'size' => '18cm', 'price_offset' => 0, 'stock' => 40],
                    ['color' => 'Đen xám (Shiny)', 'size' => '18cm', 'price_offset' => 70000, 'stock' => 15],
                    ['color' => 'Mega Charizard X', 'size' => '18cm', 'price_offset' => 90000, 'stock' => 12],
                    ['color' => 'Hiệu ứng lửa bốc cháy (LED)', 'size' => '20cm', 'price_offset' => 150000, 'stock' => 10]
                ]
            ],
        ];

        // Do chưa có hình ảnh về dụng cụ, tạm thời dọn dẹp hoặc để trống mảng này để bạn bổ sung sau
        $dungCuProducts = [];

        // Seeding Mô hình products
        if (!empty($moHinhProducts)) {
            $this->createProductsWithRelations($moHinh->id, $moHinhProducts);
        }

        // Seeding Dụng cụ products
        if (!empty($dungCuProducts)) {
            $this->createProductsWithRelations($dungCu->id, $dungCuProducts);
        }
    }

    private function createProductsWithRelations(int $categoryId, array $productsList): void
    {
        foreach ($productsList as $productData) {
            $product = SanPham::create([
                'id_danh_muc' => $categoryId,
                'ten_san_pham' => $productData['ten_san_pham'],
                'gia_co_ban' => $productData['gia_co_ban'],
                'mo_ta' => $productData['mo_ta'],
                'tinh_trang' => 1, // 1: dang_ban, 0: het_hang, 2: an
            ]);

            // Add all images dynamically
            if (isset($productData['images'])) {
                foreach ($productData['images'] as $index => $imageName) {
                    $dbPath = $imageName; // Mặc định gán luôn chuỗi ban đầu (Dành cho URL)

                    // Kiểm tra xem chuỗi có phải là một URL hợp lệ không. Nếu không, coi đó là file local và thực hiện copy.
                    if (!filter_var($imageName, FILTER_VALIDATE_URL)) {
                        $sourcePath = database_path('seeders/image/' . $imageName);
                        $destDir = public_path('uploads/products');
                        $destPath = $destDir . '/' . $imageName;

                        if (file_exists($sourcePath)) {
                            if (!file_exists($destDir)) {
                                mkdir($destDir, 0755, true);
                            }
                            copy($sourcePath, $destPath);
                            $dbPath = '/uploads/products/' . $imageName;
                        }
                    }

                    HinhAnhSanPham::create([
                        'id_san_pham' => $product->id,
                        'duong_dan_anh' => $dbPath,
                        'la_anh_dai_dien' => $index === 0, // Ảnh đầu tiên trong mảng sẽ là ảnh đại diện
                        'thu_tu_hien_thi' => $index,
                    ]);
                }
            }

            // Add variants
            foreach ($productData['variants'] as $variantData) {
                BienTheSanPham::create([
                    'id_san_pham' => $product->id,
                    'ma_kho' => 'SKU-' . $product->id . '-' . strtoupper(str_replace([' ', '(', ')'], '', $variantData['color'])),
                    'thuoc_tinh' => ['color' => $variantData['color'], 'size' => $variantData['size']],
                    'gia_ban' => $product->gia_co_ban + $variantData['price_offset'],
                    'so_luong_ton_kho' => $variantData['stock'],
                ]);
            }
        }
    }
}