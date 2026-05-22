<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\LichSuTrangThaiDonHang;
use App\Models\ThanhToan;
use App\Models\DanhGia;
use App\Models\AnhDanhGia;
use Carbon\Carbon;

class DanhGiaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get 15 products
        $products = SanPham::with('bienThes')->take(15)->get();
        if ($products->isEmpty()) {
            $this->command->warn('No products found in the database. Please run SanPhamSeeder first.');
            return;
        }

        // 2. Get customers
        $customers = NguoiDung::where('vai_tro', 3)->get();
        if ($customers->isEmpty()) {
            $this->command->warn('No customers found in the database. Please run NguoiDungSeeder first.');
            return;
        }

        // Clean existing reviews
        DanhGia::query()->delete();
        AnhDanhGia::query()->delete();

        // Authentic review comments in Vietnamese (4 and 5 stars)
        $comments5 = [
            'Mô hình quá đẹp luôn, độ hoàn thiện cực kỳ cao!',
            'Giao hàng nhanh, đóng gói siêu cẩn thận 3 lớp xốp bong bóng.',
            'Chi tiết sắc nét, màu sắc chuẩn chỉnh giống hệt ảnh quảng cáo. 10/10!',
            'Chất lượng nhựa resin cầm rất chắc tay và nặng. Đáng tiền lắm nha mọi người.',
            'Shop hỗ trợ tư vấn siêu nhiệt tình, mô hình lắp ráp khớp khít khịt.',
            'Quá xuất sắc! Box đẹp không móp méo tí nào, rất đáng mua làm quà tặng.',
            'Tuyệt vời ông mặt trời, sẽ tiếp tục ủng hộ shop trong các đợt tới.',
            'Hàng về tay nguyên vẹn, sơn mịn và sắc sảo vô cùng.',
            'Sản phẩm đẹp vượt kỳ vọng, đầm tay, chi tiết nhỏ cũng được làm rất kỹ.',
            'Không có gì để chê, giao hàng thần tốc, mô hình siêu ngầu!'
        ];

        $comments4 = [
            'Mô hình đẹp, tuy nhiên giao hàng hơi lâu một chút.',
            'Chi tiết gia công khá tốt, khớp hơi lỏng nhẹ ở tay nhưng không sao.',
            'Màu sơn đẹp nhưng có một vài vệt nhỏ bị lem, tổng thể vẫn rất ok.',
            'Chất lượng tương xứng với tầm giá, đóng gói kỹ càng.',
            'Mô hình xịn xò, giá cả hợp lý, mọi người nên mua nhé.',
            'Đóng gói cẩn thận nhưng hộp hơi bị móp nhẹ góc do vận chuyển. Sản phẩm bên trong vẫn nguyên vẹn.',
            'Mô hình nét căng, chỉ tiếc là không kèm theo đế trưng bày phụ.',
            'Sản phẩm tốt, nhân viên cskh trả lời hơi chậm một xíu nhưng nhiệt tình.'
        ];

        $adminReplies = [
            'Dạ cảm ơn quý khách rất nhiều đã tin tưởng ủng hộ shop ạ! Chúc quý khách trưng bày mô hình thật đẹp nhé.',
            'Cảm ơn đánh giá 5 sao của bạn! Shop sẽ cố gắng cải thiện thời gian giao hàng hơn nữa ạ.',
            'Cảm ơn phản hồi của bạn! Nếu khớp lỏng bạn có thể ib shop để shop hướng dẫn fix bằng keo chuyên dụng miễn phí nha.',
            'Cảm ơn bạn đã ủng hộ! Rất mong được tiếp tục phục vụ bạn trong những đơn hàng tiếp theo.',
            'Dạ shop cảm ơn phản hồi của mình ạ, shop sẽ làm việc lại với bên vận chuyển để bọc hộp bảo vệ tốt hơn nữa.',
            'Cảm ơn bạn! Sự hài lòng của bạn là động lực lớn nhất để shop hoàn thiện hơn mỗi ngày.'
        ];

        // Sample review image paths
        $sampleImages = [
            'style_admin/images/product-sample.jpg', // we can use existing files if any, or mock paths
        ];

        $reviewCountTotal = 0;

        foreach ($products as $product) {
            $variants = $product->bienThes;
            if ($variants->isEmpty()) {
                continue;
            }

            // Decide how many reviews for this product: 4 to 6 reviews
            $numReviews = rand(4, 6);

            // Select random unique customers for these reviews to avoid multiple reviews by same customer on same order
            $selectedCustomers = $customers->random(min($numReviews, $customers->count()));

            foreach ($selectedCustomers as $customer) {
                // Pick a random variant of this product
                $variant = $variants->random();

                // Generate order date (within the last 15 days to ensure not expired)
                $orderDate = Carbon::now()->subDays(rand(1, 14))->subHours(rand(1, 23));
                $deliveryDate = $orderDate->copy()->addDays(rand(1, 2));

                // Create default address
                $diaChiArr = [
                    'ten' => $customer->ho_ten,
                    'so_dien_thoai' => '0987654321',
                    'dia_chi' => '123 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
                ];

                // Create Order
                $order = DonHang::create([
                    'id_nguoi_dung' => $customer->id,
                    'ma_don_hang' => 'ORD-RV-' . strtoupper(Str::random(6)),
                    'tong_tien_hang' => $variant->gia_ban,
                    'tien_duoc_giam' => 0,
                    'phi_giao_hang' => 30000,
                    'tong_thanh_toan' => $variant->gia_ban + 30000,
                    'trang_thai' => 'da_giao',
                    'dia_chi_giao_hang' => $diaChiArr,
                    'tao_luc' => $orderDate,
                    'cap_nhat_luc' => $deliveryDate,
                ]);

                // Create Order Item (ChiTietDonHang)
                $detail = ChiTietDonHang::create([
                    'id_don_hang' => $order->id,
                    'id_bien_the' => $variant->id,
                    'ten_bien_the_luc_mua' => $product->ten_san_pham . ' (' . implode(', ', is_array($variant->thuoc_tinh) ? $variant->thuoc_tinh : (json_decode($variant->thuoc_tinh, true) ?? [])) . ')',
                    'so_luong' => 1,
                    'don_gia' => $variant->gia_ban,
                    'thanh_tien' => $variant->gia_ban,
                    'tao_luc' => $orderDate,
                ]);

                // Create Order Status History
                LichSuTrangThaiDonHang::unguard();
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => 'cho_xu_ly',
                    'ghi_chu' => 'Đơn hàng được tạo thành công.',
                    'tao_luc' => $orderDate,
                ]);
                LichSuTrangThaiDonHang::create([
                    'id_don_hang' => $order->id,
                    'trang_thai' => 'da_giao',
                    'ghi_chu' => 'Đơn hàng đã giao thành công cho khách.',
                    'tao_luc' => $deliveryDate,
                ]);
                LichSuTrangThaiDonHang::reguard();

                // Create Payment
                ThanhToan::create([
                    'id_don_hang' => $order->id,
                    'phuong_thuc' => 'tien_mat',
                    'so_tien' => $order->tong_thanh_toan,
                    'trang_thai' => 'da_thanh_toan',
                    'ngay_thanh_toan' => $deliveryDate,
                    'tao_luc' => $orderDate,
                    'cap_nhat_luc' => $deliveryDate,
                ]);

                // Determine star rating: 4 to 5 stars
                $stars = rand(4, 5);
                $comment = ($stars === 5) ? $comments5[array_rand($comments5)] : $comments4[array_rand($comments4)];

                // 30% chance of Admin reply
                $adminReply = (rand(1, 10) <= 3) ? $adminReplies[array_rand($adminReplies)] : null;

                // Create Review (DanhGia)
                $review = DanhGia::create([
                    'id_nguoi_dung' => $customer->id,
                    'id_san_pham' => $product->id,
                    'id_bien_the' => $variant->id,
                    'id_chi_tiet_don_hang' => $detail->id,
                    'so_sao' => $stars,
                    'binh_luan' => $comment,
                    'phan_hoi_admin' => $adminReply,
                    'trang_thai' => 'hien_thi',
                    'tao_luc' => $deliveryDate->copy()->addHours(rand(1, 4)),
                    'cap_nhat_luc' => $deliveryDate->copy()->addHours(rand(1, 4)),
                ]);

                // 40% chance of attaching 1-2 images
                if (rand(1, 10) <= 4) {
                    $numImages = rand(1, 2);
                    for ($imgIdx = 1; $imgIdx <= $numImages; $imgIdx++) {
                        // Create a mock image record (we can point to a placeholder or a mock name)
                        // This will display beautiful placeholders or missing icons that the UI handles safely.
                        AnhDanhGia::create([
                            'id_danh_gia' => $review->id,
                            'duong_dan_anh' => 'uploads/reviews/mock_review_' . rand(1, 5) . '.jpg',
                            'tao_luc' => $review->tao_luc,
                        ]);
                    }
                }

                $reviewCountTotal++;
            }
        }

        $this->command->info("Successfully seeded {$reviewCountTotal} product reviews for 15 products!");
    }
}
