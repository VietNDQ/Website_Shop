# TÀI LIỆU YÊU CẦU CHỨC NĂNG HỆ THỐNG QUẢN TRỊ (ADMIN/SELLER DASHBOARD)

Tài liệu này tổng hợp toàn bộ các chức năng cốt lõi cần có cho phía người bán (Seller/Admin) khi xây dựng một website bán hàng (E-commerce). Hệ thống được phân chia theo các module chức năng để thuận tiện cho việc thiết kế cơ sở dữ liệu, xây dựng API (Backend) và phát triển giao diện (Frontend).

## 1. Module Quản Lý Sản Phẩm (Product Management)
Kiểm soát toàn bộ vòng đời và thông tin hiển thị của hàng hóa trên website.

* **Thêm mới sản phẩm (Create):** Nhập tên, mô tả chi tiết (WYSIWYG editor), giá bán, giá khuyến mãi, mã SKU, danh mục, thương hiệu.
* **Quản lý biến thể (Product Variants):** Cấu hình các thuộc tính sản phẩm linh hoạt như kích cỡ (S, M, L), màu sắc, chất liệu. Mỗi tổ hợp biến thể có SKU, giá và số lượng kho riêng.
* **Danh mục sản phẩm (Categories):** Tạo cấu trúc danh mục đa cấp (Danh mục cha - Danh mục con) để phân loại hàng hóa.
* **Quản lý tồn kho (Inventory):** Cập nhật số lượng sản phẩm trong kho, tự động trừ kho khi có đơn hàng mới, thiết lập ngưỡng cảnh báo khi sản phẩm sắp hết hàng.
* **Quản lý thư viện ảnh (Media Gallery):** Tải lên, sắp xếp thứ tự hiển thị ảnh đại diện và ảnh chi tiết sản phẩm (hỗ trợ kéo thả, tối ưu hóa dung lượng).

## 2. Module Quản Lý Đơn Hàng (Order Management)
Theo dõi, xử lý và vận hành quy trình từ lúc khách đặt hàng cho đến khi giao thành công.

* **Danh sách đơn hàng:** Bộ lọc đơn hàng nâng cao theo mã đơn, thời gian, trạng thái thanh toán, phương thức vận chuyển và trạng thái đơn hàng.
* **Cập nhật trạng thái đơn hàng (Workflow):** Thay đổi trạng thái theo quy trình chuẩn:
    * *Chờ xác nhận ➔ Đang đóng gói ➔ Đang giao hàng ➔ Đã giao / Hoàn thành ➔ Đã hủy.*
* **Xem chi tiết đơn hàng:** Hiển thị thông tin khách hàng, địa chỉ nhận hàng, số điện thoại, danh sách sản phẩm, ghi chú của khách, lịch sử thay đổi trạng thái và phương thức thanh toán (COD, thẻ tín dụng, ví điện tử).
* **Xử lý hoàn trả & hủy đơn:** Ghi nhận lý do hủy đơn, quản lý các yêu cầu đổi trả hàng hoặc hoàn tiền từ phía khách hàng.
* **In hóa đơn (Invoice):** Xuất phiếu đóng gói và hóa đơn dưới dạng PDF để in ấn vật lý phục vụ đóng gói và giao nhận.

## 3. Module Quản Lý Khách Hàng (Customer Management)
Lưu trữ và tối ưu hóa thông tin người mua nhằm phục vụ chăm sóc khách hàng.

* **Hồ sơ khách hàng:** Quản lý danh sách khách hàng, thông tin liên hệ (Email, SĐT), danh sách địa chỉ nhận hàng đã lưu.
* **Lịch sử mua hàng:** Xem tổng số đơn hàng đã mua, tổng số tiền đã chi tiêu, tần suất mua hàng để phân loại nhóm khách hàng (Khách mới, Khách thân thiết, Khách VIP).
* **Quản lý đánh giá & bình luận (Reviews & Ratings):** Kiểm duyệt các đánh giá (số sao) và bình luận của khách hàng dưới mỗi sản phẩm; cho phép phản hồi trực tiếp hoặc ẩn các bình luận vi phạm chính sách.

## 4. Module Khuyến Mãi & Marketing (Promotions)
Tạo động lực mua hàng và tăng trưởng doanh số thông qua các chiến dịch ưu đãi.

* **Quản lý mã giảm giá (Coupons/Vouchers):** Tạo mã giảm giá theo tỷ lệ phần trăm (%) hoặc số tiền cố định. Thiết lập điều kiện áp dụng (giá trị đơn hàng tối thiểu, danh mục áp dụng, giới hạn số lần sử dụng, thời gian hiệu lực).
* **Chương trình giảm giá trực tiếp (Flash Sale):** Cấu hình giảm giá trực tiếp trên giá bán lẻ của sản phẩm hoặc toàn bộ danh mục trong một khoảng thời gian nhất định.

## 5. Module Thống Kê & Báo Cáo (Analytics & Dashboard)
Cung cấp số liệu trực quan giúp người bán đánh giá hiệu quả kinh doanh và đưa ra quyết định.

* **Tổng quan doanh thu:** Thống kê doanh thu, lợi nhuận, số lượng đơn hàng theo các mốc thời gian (Hôm nay, tuần này, tháng này, năm nay hoặc khoảng thời gian tùy chỉnh).
* **Hiệu suất sản phẩm:** Biểu đồ/Danh sách top các sản phẩm bán chạy nhất (theo số lượng hoặc theo doanh thu) và sản phẩm có lượng tồn kho cao nhất.
* **Thống kê đơn hàng:** Tỷ lệ chuyển đổi, tỷ lệ hủy đơn, phân tích phương thức thanh toán/vận chuyển được sử dụng nhiều nhất.

## 6. Module Quản Lý Tài Khoản & Phân Quyền (Account & Role Management)
Đảm bảo tính bảo mật và phân chia trách nhiệm rõ ràng khi hệ thống có nhiều nhân viên cùng vận hành.

* **Quản lý nhân sự:** Thêm mới, chỉnh sửa thông tin hoặc khóa/tạm dừng tài khoản của nhân viên trong hệ thống.
* **Phân quyền dựa trên vai trò (RBAC - Role-Based Access Control):** Cấp quyền hạn truy cập cụ thể cho từng tài khoản:
    * *Super Admin (Chủ cửa hàng):* Toàn quyền quyết định trên mọi module, xem báo cáo tài chính, thiết lập hệ thống.
    * *Nhân viên kho (Inventory Staff):* Chỉ có quyền xem và cập nhật module Sản phẩm/Kho hàng.
    * *Nhân viên bán hàng (Sales Staff):* Chỉ có quyền xử lý Đơn hàng, chăm sóc Khách hàng, không xem được doanh thu tổng.
* **Nhật ký hoạt động (Activity Logs):** Ghi lại lịch sử thao tác của từng tài khoản (ví dụ: Tài khoản A thay đổi giá sản phẩm X, Tài khoản B hủy đơn hàng Y) để đối soát khi cần thiết.

## 7. Module Thông Tin Cá Nhân (My Profile)
Khu vực tự quản lý dành riêng cho cá nhân tài khoản đang đăng nhập.

* **Cập nhật hồ sơ cá nhân:** Thay đổi ảnh đại diện (avatar), họ và tên, số điện thoại, email liên kết cá nhân.
* **Quản lý bảo mật:** Chức năng đổi mật khẩu (yêu cầu mật khẩu cũ) và tích hợp xác thực 2 lớp (2FA) nếu cần bảo mật cao.
* **Cấu hình thông báo cá nhân:** Lựa chọn nhận thông báo qua Email hoặc hệ thống (In-app Notification) khi có các sự kiện phát sinh (đơn hàng mới, sản phẩm sắp hết hàng, v.v.).

## 8. Cài Đặt Hệ Thống & Cửa Hàng (System Settings)
Cấu hình các thông số nền tảng cho toàn bộ website.

* **Thông tin cửa hàng:** Tên thương hiệu, logo, địa chỉ kho hàng, số hotline, email hỗ trợ, các liên kết mạng xã hội và chính sách mua hàng/đổi trả.
* **Cấu hình vận chuyển:** Thiết lập các đơn vị vận chuyển đối tác, cấu hình phí ship (đồng giá, theo khoảng cách, theo trọng lượng hoặc thiết lập miễn phí vận chuyển cho đơn hàng đạt giá trị tối thiểu).
* **Cấu hình thanh toán:** Bật/tắt và tích hợp thông số cho các phương thức thanh toán (Thanh toán khi nhận hàng COD, Chuyển khoản ngân hàng qua mã QR, tích hợp cổng thanh toán như VNPAY, MoMo, Zalopay).