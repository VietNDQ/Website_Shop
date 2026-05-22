<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BaiViet;
use App\Models\NguoiDung;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tài khoản admin làm người đăng mặc định
        $admin = NguoiDung::where('email', 'admin@gmail.com')->first();
        $adminId = $admin ? $admin->id : 1;

        $posts = [
            [
                'tieu_de' => 'Hướng dẫn mua sắm trực tuyến an toàn và thông minh',
                'slug' => 'huong-dan-mua-sam-truc-tuyen-an-toan-va-thong-minh',
                'anh_dai_dien' => "https://cdn.sforum.vn/sforum/wp-content/uploads/2021/07/Mua-ho-hang-hoa-quoc-te.png",
                'tom_tat' => 'Làm thế nào để mua sắm trực tuyến vừa tiết kiệm thời gian, chi phí lại vừa đảm bảo an toàn bảo mật? Xem ngay 5 mẹo hữu ích từ BALAB Store.',
                'noi_dung' => '
                    <p>Trong thời đại số hóa hiện nay, mua sắm trực tuyến đã trở thành một phần không thể thiếu trong cuộc sống hàng ngày. Tuy nhiên, đi kèm với sự tiện lợi là những rủi ro về bảo mật thông tin và chất lượng sản phẩm. Dưới đây là 5 nguyên tắc giúp bạn luôn mua sắm an toàn và thông minh:</p>

                    <h3>1. Lựa chọn các nhà bán hàng uy tín</h3>
                    <p>Luôn ưu tiên mua hàng tại các website có thương hiệu rõ ràng, địa chỉ liên hệ đầy đủ và hỗ trợ khách hàng chuyên nghiệp như BALAB Store. Kiểm tra kỹ thông tin phản hồi của các khách hàng cũ trước khi quyết định mua.</p>

                    <h3>2. So sánh giá cả và sử dụng mã giảm giá</h3>
                    <p>Đừng vội thanh toán ngay! Hãy kiểm tra các chương trình khuyến mãi hiện có, so sánh giá của sản phẩm với các bên để đảm bảo nhận được ưu đãi tốt nhất. Đăng ký nhận tin tức để không bỏ lỡ các đợt giảm giá lớn.</p>

                    <h3>3. Đọc kỹ mô tả sản phẩm và chính sách đổi trả</h3>
                    <p>Hãy chú ý đến các thông số kỹ thuật, xuất xứ và chính sách bảo hành, hoàn tiền nếu sản phẩm gặp lỗi hoặc không đúng mô tả. Việc này sẽ giúp bạn tránh các tranh chấp không đáng có.</p>

                    <blockquote>Mua sắm thông minh không chỉ là tìm kiếm giá rẻ nhất, mà là nhận được giá trị tốt nhất xứng đáng với số tiền bạn bỏ ra.</blockquote>

                    <h3>4. Bảo mật thông tin thanh toán cá nhân</h3>
                    <p>Chỉ thực hiện thanh toán trên các kết nối mạng an toàn (tránh WiFi công cộng). Không chia sẻ mã OTP hoặc mật khẩu tài khoản cho bất kỳ ai khác.</p>
                ',
                'luot_xem' => 125,
                'loai' => 'huong_dan',
                'trang_thai' => true,
                'id_nguoi_dang' => $adminId,
            ],
            [
                'tieu_de' => 'Đánh giá chi tiết các dòng sản phẩm gia dụng thông minh thế hệ mới',
                'slug' => 'danh-gia-chi-tiet-cac-dong-san-pham-gia-dung-thong-minh-the-he-moi',
                'anh_dai_dien' => "https://zafago.com/wp-content/uploads/2025/06/Ban-do-gia-dung-online-khong-kho-neu-ban-biet-nhung-dieu-nay.webp",
                'tom_tat' => 'Đánh giá khách quan về thiết kế, tính năng công nghệ và độ bền thực tế của các sản phẩm gia dụng thông minh đang được săn đón hàng đầu hiện nay.',
                'noi_dung' => '
                    <p>Các thiết bị gia dụng thông minh ngày càng trở nên phổ biến nhờ khả năng tiết kiệm công sức và nâng tầm chất lượng cuộc sống. Trong bài viết này, chúng tôi sẽ đánh giá chi tiết trải nghiệm sử dụng thực tế của các dòng máy lọc không khí và robot hút bụi mới nhất.</p>

                    <h3>Về Thiết Kế & Thẩm Mỹ</h3>
                    <p>Hầu hết các sản phẩm thế hệ mới đều theo đuổi phong cách tối giản (minimalism) với tông màu chủ đạo trắng hoặc xám sang trọng. Kích thước nhỏ gọn giúp tiết kiệm diện tích tối đa và dễ dàng hòa hợp với mọi phong cách thiết kế nội thất.</p>

                    <h3>Hiệu Năng Thực Tế</h3>
                    <p>Điểm nâng cấp đáng giá nhất nằm ở hệ thống cảm biến thông minh và kết nối ứng dụng điện thoại:</p>
                    <ul>
                        <li><b>Robot hút bụi:</b> Bản đồ phòng quét chính xác hơn nhờ cảm biến Lidar, khả năng tự động đổ rác tại dock sạc cực kỳ tiện lợi.</li>
                        <li><b>Máy lọc không khí:</b> Hiển thị chỉ số bụi mịn PM2.5 thời gian thực, độ ồn cực thấp khi hoạt động ở chế độ ngủ (Sleep Mode).</li>
                    </ul>

                    <h3>Đánh giá chung</h3>
                    <p>Với mức giá hợp lý cùng những cải tiến công nghệ vượt bậc, đây chắc chắn là khoản đầu tư thông minh cho tổ ấm hiện đại của bạn để bảo vệ sức khỏe cả gia đình.</p>
                ',
                'luot_xem' => 84,
                'loai' => 'danh_gia',
                'trang_thai' => true,
                'id_nguoi_dang' => $adminId,
            ],
            [
                'tieu_de' => 'BALAB Store bùng nổ siêu khuyến mãi chào hè cực khủng',
                'slug' => 'balab-store-bung-no-sieu-khuyen-mai-chao-he-cuc-khung',
                'anh_dai_dien' => "https://static.salekit.com/image/shop/2/source/cac-chuong-trinh-khuyen-mai-hay1.jpg",
                'tom_tat' => 'Chào đón mùa hè rực rỡ, BALAB Store mang đến chương trình siêu sale đồng giá, mã giảm giá độc quyền cùng cơ hội nhận hàng ngàn quà tặng hấp dẫn.',
                'noi_dung' => '
                    <p>Mùa hè đã gõ cửa! Để tri ân khách hàng đã đồng hành cùng thương hiệu thời gian qua, BALAB Store chính thức khởi động chiến dịch siêu khuyến mãi lớn nhất năm <b>"HÈ RỰC RỠ - SĂN SALE THẢ GA"</b> với ưu đãi giảm giá sâu toàn bộ gian hàng.</p>

                    <h3>Nội dung chương trình khuyến mãi:</h3>
                    <ul>
                        <li><b>Giảm giá trực tiếp lên đến 50%:</b> Áp dụng cho các nhóm sản phẩm bán chạy nhất trong danh mục.</li>
                        <li><b>Mã voucher miễn phí vận chuyển:</b> Tặng ngay code freeship toàn quốc cho các đơn hàng trị giá từ 500.000đ trở lên.</li>
                        <li><b>Quà tặng đi kèm:</b> Nhận ngay phần quà giới hạn xinh xắn khi hóa đơn đạt mốc quy định.</li>
                    </ul>

                    <h3>Thời gian diễn ra sự kiện:</h3>
                    <p>Chương trình bắt đầu từ ngày 01/06/2026 đến hết ngày 15/06/2026 trên tất cả kênh bán hàng online lẫn offline của BALAB Store. Số lượng quà tặng có hạn, hãy nhanh tay săn đón ngay hôm nay!</p>
                ',
                'luot_xem' => 210,
                'loai' => 'tin_tuc',
                'trang_thai' => true,
                'id_nguoi_dang' => $adminId,
            ]
        ];

        foreach ($posts as $post) {
            BaiViet::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }
    }
}
