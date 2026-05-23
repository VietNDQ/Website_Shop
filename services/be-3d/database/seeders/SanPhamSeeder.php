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
        $moHinhAnime = DanhMuc::where('duong_dan_mau', 'mo-hinh-anime-figure')->first();
        $moHinhLapRap = DanhMuc::where('duong_dan_mau', 'mo-hinh-lap-rap')->first();
        $dungCuCaNhan = DanhMuc::where('duong_dan_mau', 'dung-cu-ca-nhan')->first();

        // Fallbacks in case DanhMucSeeder has not run yet
        if (!$moHinhAnime) {
            $moHinhAnime = DanhMuc::create(['ten_danh_muc' => 'Mô hình Anime & Figure', 'duong_dan_mau' => 'mo-hinh-anime-figure']);
        }
        if (!$moHinhLapRap) {
            $moHinhLapRap = DanhMuc::create(['ten_danh_muc' => 'Mô hình Lắp ráp (Gundam/Plamo)', 'duong_dan_mau' => 'mo-hinh-lap-rap']);
        }
        if (!$dungCuCaNhan) {
            $dungCuCaNhan = DanhMuc::create(['ten_danh_muc' => 'Dụng cụ cá nhân', 'duong_dan_mau' => 'dung-cu-ca-nhan']);
        }

        // Clean existing data
        BienTheSanPham::query()->delete();
        HinhAnhSanPham::query()->delete();
        SanPham::query()->delete();

        // --- 15 LINK CŨ ---
        $link1 = 'https://legobox.com.vn/wp-content/uploads/2024/01/LEGO-Classic-Creative-Monsters-11017-6-600x600.jpeg';
        $link2 = 'https://cdn.shopify.com/s/files/1/0731/6514/4343/files/do-choi-lap-rap-qua-bong-da-lego-editions-sports-43019_1.jpg?v=1770193266&width=336';
        $link3 = 'https://i.bbcosplay.com/5821/ngoc-rong-3.jpg';
        $link4 = 'https://down-vn.img.susercontent.com/file/9a33debe16227bd066b9fa419255a8cc';
        $link5 = 'https://down-vn.img.susercontent.com/file/sg-11134201-22100-d77omh9bv5ivb5';
        $link6 = 'https://bizweb.dktcdn.net/100/418/981/products/z5243238613070-caa91766c95672d6aae83a0b66070ec6.jpg?v=1772354179473';
        $link7 = 'https://takishop.vn/wp-content/uploads/2025/05/FIG434-Zoro-Chibi-dung-1-400x400.jpg';
        $link8 = 'https://bizweb.dktcdn.net/100/418/981/products/z5243238613070-caa91766c95672d6aae83a0b66070ec6.jpg?v=1772354179473'; // Link trùng
        $link9 = 'https://salt.tikicdn.com/cache/w300/ts/product/75/21/02/f0178abf27dc5e1f26ab5eea76f5b0e2.jpg';
        $link10 = 'https://cf.shopee.vn/file/vn-11134207-7qukw-lfu0r9qrgxyv27';
        $link11 = 'https://down-vn.img.susercontent.com/file/vn-11134207-7r98o-lujo6q40wqvm52';
        $link12 = 'https://cf.shopee.vn/file/fe7850a85480936e51dec24e8f070563';
        $link13 = 'https://1.bp.blogspot.com/-kTBlH2lYnIY/UcUgwxWDg1I/AAAAAAAB-fs/WMQxZHWXTp4/s1600/15971-5.jpg';
        $link14 = 'https://bizweb.dktcdn.net/100/479/026/products/vn-11134207-7qukw-lf5mlmj47zvbe7-1680540683695.jpg?v=1680540785880';
        $link15 = 'https://tse3.mm.bing.net/th/id/OIP.Sx2889-Q6ln5SzqDKl5W2QHaFj?w=4000&h=3000&rs=1&pid=ImgDetMain&o=7&rm=3';

        // --- 12 LINK MỚI ---
        $link16 = 'https://down-vn.img.susercontent.com/file/20f3e37de38241459658ec199419ca90';
        $link17 = 'https://down-vn.img.susercontent.com/file/sg-11134201-7qvfh-lkbvwtshg8u889';
        $link18 = 'https://lacdau.com/media/product/250-5057-28.jpg';
        $link19 = 'https://vpokedex.com/wp-content/uploads/2023/02/Mo-hinh-Pokemon-tung-chieu-set-3-768x768.jpg';
        $link20 = 'https://file.hstatic.net/1000231532/file/mo_hinh_pokemon_swing_vignette_collection_2_chat_luong_cao_b8d59d41fad54224adb095693ae91c16_grande.jpg';
        $link21 = 'https://cf.shopee.vn/file/b2cc836d8f20e322052d33656d7431fb';
        $link22 = 'https://down-vn.img.susercontent.com/file/vn-11134207-7r98o-lwmlsuzf0e6x0c';
        $link23 = 'https://cf.shopee.vn/file/23ce9c1a28253f5d693331473961e734';
        $link24 = 'https://down-vn.img.susercontent.com/file/aea0613da98e48c883becc62039b3ad1';
        $link25 = 'https://lacdau.com/media/product/4875-5.jpg';
        $link26 = 'https://pos.nvncdn.com/f625c0-33854/ps/20241113_8P8tQVWM8Z.jpeg';
        $link27 = 'https://www.gundambienhoa.com/storage/app/uploads/public/68e/ff2/5d9/68eff25d904b2468092453.webp';

        // ---------------------------------------------------------
        // 17 SẢN PHẨM: MÔ HÌNH (Gồm Pokemon, Gundam, Anime...)
        // ---------------------------------------------------------
        
        // Mô hình lắp ráp (Gundam, Lego, Mecha...)
        $lapRapProducts = [
            [
                'name' => 'Lắp ráp LEGO Quái Vật Sáng Tạo',
                'price' => 299000,
                'img' => [$link1, $link2, $link15],
                'variants' => [
                    ['color' => 'Đỏ rực rỡ', 'size' => 'Tiêu chuẩn', 'price_offset' => 0, 'stock' => 35],
                    ['color' => 'Xanh lá tươi', 'size' => 'Tiêu chuẩn', 'price_offset' => 0, 'stock' => 25],
                    ['color' => 'Vàng chanh', 'size' => 'Đặc biệt (+Hộp sắt)', 'price_offset' => 35000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Mô hình Quả Bóng Đá Lắp Ráp Động Cơ',
                'price' => 450000,
                'img' => [$link2, $link17, $link5],
                'variants' => [
                    ['color' => 'Classic Đen Trắng', 'size' => 'Đường kính 15cm', 'price_offset' => 0, 'stock' => 39],
                    ['color' => 'Neon Xanh Hồng', 'size' => 'Đường kính 15cm', 'price_offset' => 20000, 'stock' => 25],
                    ['color' => 'Gold Edition', 'size' => 'Đường kính 18cm', 'price_offset' => 80000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Mô hình Robot Gundam RX-78-2 Bản Thường',
                'price' => 1200000,
                'img' => [$link5, $link27, $link25],
                'variants' => [
                    ['color' => 'Bản Classic (HG)', 'size' => 'Tỷ lệ 1/144', 'price_offset' => 0, 'stock' => 20],
                    ['color' => 'Bản Chi Tiết (RG)', 'size' => 'Tỷ lệ 1/144', 'price_offset' => 450000, 'stock' => 15],
                    ['color' => 'Bản Khổng Lồ (MG)', 'size' => 'Tỷ lệ 1/100', 'price_offset' => 950000, 'stock' => 8],
                ]
            ],
            [
                'name' => 'Mô Hình Lắp Ráp Robot Mecha Thế Hệ Mới',
                'price' => 310000,
                'img' => [$link17, $link18, $link5],
                'variants' => [
                    ['color' => 'Trắng Neon', 'size' => 'Tỷ lệ 1/144', 'price_offset' => 0, 'stock' => 45],
                    ['color' => 'Đỏ Crimson', 'size' => 'Tỷ lệ 1/144', 'price_offset' => 15000, 'stock' => 35],
                    ['color' => 'Đen Stealth', 'size' => 'Tỷ lệ 1/144 (Kèm vũ khí phụ)', 'price_offset' => 55000, 'stock' => 20],
                ]
            ],
            [
                'name' => 'Gundam HIRM / MG Lắp Ráp Khung Xương Động',
                'price' => 1650000,
                'img' => [$link27, $link5],
                'variants' => [
                    ['color' => 'Barbatos Lupus Rex (MG)', 'size' => 'Tỷ lệ 1/100', 'price_offset' => 0, 'stock' => 15],
                    ['color' => 'Wing Zero Custom (HiRM)', 'size' => 'Tỷ lệ 1/100 (Khung kim loại)', 'price_offset' => 850000, 'stock' => 8],
                ]
            ],
        ];

        // Mô hình Anime & Figure (Son Goku, Zoro, Pokemon...)
        $animeFigureProducts = [
            [
                'name' => 'Bộ 7 Viên Ngọc Rồng Kèm Rồng Thần',
                'price' => 550000,
                'img' => [$link3, $link4],
                'variants' => [
                    ['color' => 'Rồng Thần Xanh', 'size' => 'Hộp giấy', 'price_offset' => 0, 'stock' => 30],
                    ['color' => 'Rồng Thần Vàng (Shiny)', 'size' => 'Hộp gỗ cao cấp', 'price_offset' => 150000, 'stock' => 10],
                ]
            ],
            [
                'name' => 'Mô hình Son Goku Super Saiyan Chiến Đấu',
                'price' => 350000,
                'img' => [$link4, $link3, $link8],
                'variants' => [
                    ['color' => 'Tóc Vàng SSJ1', 'size' => 'Cao 18cm', 'price_offset' => 0, 'stock' => 45],
                    ['color' => 'Tóc Xanh SSJ Blue', 'size' => 'Cao 18cm', 'price_offset' => 30000, 'stock' => 30],
                    ['color' => 'Tóc Bạc Vô Cực', 'size' => 'Cao 20cm (Có Led)', 'price_offset' => 120000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Mô hình Zoro Chibi Cầm 3 Kiếm',
                'price' => 220000,
                'img' => [$link7, $link21, $link6],
                'variants' => [
                    ['color' => 'Trang phục Wano (Xanh)', 'size' => 'Cao 10cm', 'price_offset' => 0, 'stock' => 50],
                    ['color' => 'Trang phục Cổ điển (Đen)', 'size' => 'Cao 10cm', 'price_offset' => 0, 'stock' => 40],
                    ['color' => 'Hiệu ứng Tam Kiếm Hồn', 'size' => 'Cao 12cm', 'price_offset' => 50000, 'stock' => 20],
                ]
            ],
            [
                'name' => 'Bộ Đồ Chơi Ninja Rùa 4 Nhân Vật Cổ Điển',
                'price' => 180000,
                'img' => [$link13, $link16],
                'variants' => [
                    ['color' => 'Set 4 Nhân Vật Classic', 'size' => 'Cao 12cm', 'price_offset' => 0, 'stock' => 35],
                    ['color' => 'Set 4 Nhân Vật Battle Damaged', 'size' => 'Cao 12cm', 'price_offset' => 40000, 'stock' => 20],
                ]
            ],
            [
                'name' => 'Mô hình Thú Bông Anime Mềm Mại',
                'price' => 120000,
                'img' => [$link15, $link10, $link9],
                'variants' => [
                    ['color' => 'Pikachu tròn trịa', 'size' => 'Dài 25cm', 'price_offset' => 0, 'stock' => 60],
                    ['color' => 'Totoro xám', 'size' => 'Dài 30cm', 'price_offset' => 20000, 'stock' => 40],
                    ['color' => 'Chomper tinh nghịch', 'size' => 'Dài 25cm', 'price_offset' => 10000, 'stock' => 30],
                ]
            ],
            [
                'name' => 'Mô Hình Nhân Vật Hoạt Hình Action Figure Cao Cấp',
                'price' => 280000,
                'img' => [$link16, $link12, $link23],
                'variants' => [
                    ['color' => 'Spiderman Homecoming', 'size' => 'Cao 16cm', 'price_offset' => 0, 'stock' => 40],
                    ['color' => 'Ironman MK85', 'size' => 'Cao 16cm (Có khớp)', 'price_offset' => 70000, 'stock' => 25],
                    ['color' => 'Captain America', 'size' => 'Cao 16cm', 'price_offset' => 20000, 'stock' => 30],
                ]
            ],
            [
                'name' => 'Mô Hình Anime Scale 1/8 Tĩnh Đẹp Mắt',
                'price' => 450000,
                'img' => [$link18, $link24, $link16],
                'variants' => [
                    ['color' => 'Rem hầu gái dễ thương', 'size' => 'Cao 22cm', 'price_offset' => 0, 'stock' => 25],
                    ['color' => 'Ram hầu gái năng động', 'size' => 'Cao 22cm', 'price_offset' => 0, 'stock' => 20],
                    ['color' => 'Emilia tinh khôi', 'size' => 'Cao 24cm', 'price_offset' => 90000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Bộ Mô Hình Pokemon Tung Chiêu Thức Set 3',
                'price' => 195000,
                'img' => [$link19, $link20],
                'variants' => [
                    ['color' => 'Set Starter Kanto (Pika/Char/Squirtle)', 'size' => 'Hộp nhựa', 'price_offset' => 0, 'stock' => 55],
                    ['color' => 'Set Starter Hoenn (Treecko/Torchic/Mudkip)', 'size' => 'Hộp nhựa', 'price_offset' => 0, 'stock' => 40],
                ]
            ],
            [
                'name' => 'Mô Hình Pokemon Swing Vignette Dây Đu Đáng Yêu',
                'price' => 165000,
                'img' => [$link20, $link19, $link15],
                'variants' => [
                    ['color' => 'Chandelure Ma Quái', 'size' => 'Móc treo 8cm', 'price_offset' => 0, 'stock' => 40],
                    ['color' => 'Sylveon Điệu Đà', 'size' => 'Móc treo 8cm', 'price_offset' => 15000, 'stock' => 30],
                    ['color' => 'Pikachu & Mimikyu', 'size' => 'Móc treo song song', 'price_offset' => 30000, 'stock' => 25],
                ]
            ],
            [
                'name' => 'Mô Hình Figure Chibi Trang Trí Kệ Sách',
                'price' => 140000,
                'img' => [$link21, $link7, $link24],
                'variants' => [
                    ['color' => 'Luffy Chibi mũ rơm', 'size' => 'Cao 8cm', 'price_offset' => 0, 'stock' => 75],
                    ['color' => 'Sanji Chibi lịch lãm', 'size' => 'Cao 8cm', 'price_offset' => 0, 'stock' => 60],
                    ['color' => 'Nami Chibi quyến rũ', 'size' => 'Cao 8cm', 'price_offset' => 10000, 'stock' => 50],
                ]
            ],
            [
                'name' => 'Mô Hình Game Sưu Tầm Phiên Bản Giới Hạn',
                'price' => 590000,
                'img' => [$link22, $link17],
                'variants' => [
                    ['color' => 'Kratos God of War', 'size' => 'Cao 20cm', 'price_offset' => 0, 'stock' => 18],
                    ['color' => 'Arthas Lich King', 'size' => 'Cao 22cm (Có hộp)', 'price_offset' => 160000, 'stock' => 10],
                ]
            ],
            [
                'name' => 'Tượng Nhân Vật Nữ Anime Xinh Xắn',
                'price' => 320000,
                'img' => [$link24, $link18],
                'variants' => [
                    ['color' => 'Hatsune Miku Sakura', 'size' => 'Cao 20cm', 'price_offset' => 0, 'stock' => 30],
                    ['color' => 'Miku Winter Version', 'size' => 'Cao 21cm', 'price_offset' => 20000, 'stock' => 25],
                ]
            ],
        ];

        // ---------------------------------------------------------
        // 10 SẢN PHẨM: DỤNG CỤ CÁ NHÂN / PHỤ KIỆN
        // ---------------------------------------------------------
        $dungCuProducts = [
            [
                'name' => 'Standee Mica Anime Trong Suốt (Bản A)',
                'price' => 55000,
                'img' => [$link6, $link7, $link21],
                'variants' => [
                    ['color' => 'Luffy Gear 5', 'size' => '15cm', 'price_offset' => 0, 'stock' => 80],
                    ['color' => 'Zoro Quỷ Kiếm', 'size' => '15cm', 'price_offset' => 0, 'stock' => 70],
                    ['color' => 'Sanji Chân Lửa', 'size' => '15cm', 'price_offset' => 0, 'stock' => 65],
                ]
            ],
            [
                'name' => 'Standee Nhựa Acrylic Đứng (Bản B)',
                'price' => 55000,
                'img' => [$link8, $link4, $link3],
                'variants' => [
                    ['color' => 'Goku Bản Năng Vô Cực', 'size' => '15cm', 'price_offset' => 0, 'stock' => 85],
                    ['color' => 'Vegeta Siêu Thần', 'size' => '15cm', 'price_offset' => 0, 'stock' => 75],
                    ['color' => 'Trunks Tương Lai', 'size' => '15cm', 'price_offset' => 0, 'stock' => 70],
                ]
            ],
            [
                'name' => 'Mô Hình Khủng Long Xanh Nút Bàn Phím',
                'price' => 45000,
                'img' => [$link9, $link15, $link12],
                'variants' => [
                    ['color' => 'Xanh lá nguyên bản', 'size' => 'Keycap Profile OEM', 'price_offset' => 0, 'stock' => 120],
                    ['color' => 'Hồng Baby ngọt ngào', 'size' => 'Keycap Profile OEM', 'price_offset' => 5000, 'stock' => 90],
                    ['color' => 'Đen Dạ Quang', 'size' => 'Keycap Profile OEM', 'price_offset' => 15000, 'stock' => 50],
                ]
            ],
            [
                'name' => 'Set Nhân Vật Chibi Gắn Chậu Cây',
                'price' => 60000,
                'img' => [$link10, $link15],
                'variants' => [
                    ['color' => 'Set 3 bé Mèo cam tinh nghịch', 'size' => 'Cao 3cm', 'price_offset' => 0, 'stock' => 100],
                    ['color' => 'Set 3 bé Totoro mini', 'size' => 'Cao 3cm', 'price_offset' => 10000, 'stock' => 80],
                ]
            ],
            [
                'name' => 'Hộp Thẻ Bài Manga Random 50 Lá',
                'price' => 85000,
                'img' => [$link11, $link14, $link6],
                'variants' => [
                    ['color' => 'Pack One Piece Card Game', 'size' => 'Hộp giấy', 'price_offset' => 0, 'stock' => 150],
                    ['color' => 'Pack Naruto Kayou Edition', 'size' => 'Hộp giấy', 'price_offset' => 15000, 'stock' => 120],
                    ['color' => 'Pack Dragon Ball Super Card', 'size' => 'Hộp giấy', 'price_offset' => 10000, 'stock' => 100],
                ]
            ],
            [
                'name' => 'Tượng Gấu Bearbrick Bụng Phệ Cỡ Vừa',
                'price' => 150000,
                'img' => [$link12, $link26, $link23],
                'variants' => [
                    ['color' => 'Mạ Crom Bạc sang trọng', 'size' => 'Cao 28cm (400%)', 'price_offset' => 0, 'stock' => 45],
                    ['color' => 'Sơn mài loang nước', 'size' => 'Cao 28cm (400%)', 'price_offset' => 30000, 'stock' => 35],
                    ['color' => 'Đen nhám Classic', 'size' => 'Cao 28cm (400%)', 'price_offset' => -10000, 'stock' => 50],
                ]
            ],
            [
                'name' => 'Tiểu Cảnh Anime Để Tiểu Cảnh / Terrarium',
                'price' => 75000,
                'img' => [$link14, $link10],
                'variants' => [
                    ['color' => 'Nhà gỗ nhỏ vùng quê', 'size' => 'Đế tròn 6cm', 'price_offset' => 0, 'stock' => 90],
                    ['color' => 'Thung lũng gió Totoro', 'size' => 'Đế tròn 6cm', 'price_offset' => 15000, 'stock' => 70],
                ]
            ],
            [
                'name' => 'Móc Khóa Nhựa Cứng Gắn Balo Xinh Xắn',
                'price' => 35000,
                'img' => [$link23, $link15, $link16],
                'variants' => [
                    ['color' => 'Shin Cậu bé bút chì', 'size' => 'Dài 5cm', 'price_offset' => 0, 'stock' => 200],
                    ['color' => 'Gấu bông nhỏ', 'size' => 'Dài 5cm', 'price_offset' => 0, 'stock' => 180],
                    ['color' => 'Captain America mini', 'size' => 'Dài 5cm', 'price_offset' => 5000, 'stock' => 150],
                ]
            ],
            [
                'name' => 'Phụ Kiện Base Đế Đứng Tương Thích Mô Hình',
                'price' => 65000,
                'img' => [$link25, $link9, $link26],
                'variants' => [
                    ['color' => 'Nhựa trong suốt (Action Base 1)', 'size' => 'Phù hợp HG/RG', 'price_offset' => 0, 'stock' => 110],
                    ['color' => 'Nhựa đen nhám (Action Base 2)', 'size' => 'Phù hợp HG/RG', 'price_offset' => 0, 'stock' => 95],
                    ['color' => 'Base Led phát sáng nhiều màu', 'size' => 'Phù hợp MG/1/100', 'price_offset' => 95000, 'stock' => 40],
                ]
            ],
            [
                'name' => 'Hộp Mica Trưng Bày Chống Bụi Có Đèn LED',
                'price' => 220000,
                'img' => [$link26, $link25, $link12],
                'variants' => [
                    ['color' => 'Led trắng ấm dịu mắt', 'size' => 'Size 20x20x30cm', 'price_offset' => 0, 'stock' => 55],
                    ['color' => 'Led RGB điều khiển app', 'size' => 'Size 20x20x30cm', 'price_offset' => 65000, 'stock' => 35],
                    ['color' => 'Led trắng ấm cỡ lớn', 'size' => 'Size 30x30x40cm', 'price_offset' => 120000, 'stock' => 20],
                ]
            ],
        ];

        // Chạy hàm tạo dữ liệu
        $this->createProductsWithRelations($moHinhLapRap->id, $lapRapProducts);
        $this->createProductsWithRelations($moHinhAnime->id, $animeFigureProducts);
        $this->createProductsWithRelations($dungCuCaNhan->id, $dungCuProducts);
    }

    private function createProductsWithRelations(int $categoryId, array $productsList): void
    {
        foreach ($productsList as $productData) {
            // Tính tổng tồn kho từ tất cả biến thể
            $totalStock = collect($productData['variants'])->sum('stock');
            // Tìm giá cơ bản nhỏ nhất của biến thể
            $minPriceOffset = collect($productData['variants'])->min('price_offset');
            $giaCoBan = $productData['price'] + $minPriceOffset;
            
            // Tính toán gia_goc cho sản phẩm
            $giaGoc = round($giaCoBan * 1.15, -3);

            $product = SanPham::create([
                'id_danh_muc' => $categoryId,
                'ten_san_pham' => $productData['name'],
                'gia_co_ban' => $giaCoBan,
                'gia_goc' => $giaGoc,
                'so_luong_ton_kho' => $totalStock,
                'mo_ta' => 'Sản phẩm ' . $productData['name'] . ' chất lượng cao. Thích hợp sưu tầm, làm quà tặng hoặc trang trí bàn làm việc.',
                'tinh_trang' => 1,
            ]);

            // Xử lý hình ảnh
            foreach ($productData['img'] as $index => $imageName) {
                HinhAnhSanPham::create([
                    'id_san_pham' => $product->id,
                    'duong_dan_anh' => $imageName,
                    'la_anh_dai_dien' => $index === 0,
                    'thu_tu_hien_thi' => $index,
                ]);
            }

            // Tạo các biến thể của sản phẩm
            foreach ($productData['variants'] as $vIndex => $vData) {
                $vGiaBan = $productData['price'] + $vData['price_offset'];
                $vGiaGoc = round($vGiaBan * 1.15, -3);

                BienTheSanPham::create([
                    'id_san_pham' => $product->id,
                    'ma_kho' => 'SKU-' . $product->id . '-' . rand(1000, 9999) . '-' . $vIndex,
                    'thuoc_tinh' => ['color' => $vData['color'], 'size' => $vData['size']],
                    'hinh_anh' => $productData['img'][$vIndex] ?? $productData['img'][0],
                    'gia_ban' => $vGiaBan,
                    'gia_goc' => $vGiaGoc,
                    'so_luong_ton_kho' => $vData['stock'],
                ]);
            }
        }
    }
}