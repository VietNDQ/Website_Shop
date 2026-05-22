# Hướng Dẫn Vận Hành & Cấu Cấu Hình Bảo Mật

Tài liệu này hướng dẫn cách cấu hình các dịch vụ giám sát lỗi (Telegram Alert), đồng bộ thời gian thực (Pusher), bảo vệ nhánh Git (GitHub Branch Protection) và vận hành hệ thống container hóa Docker an toàn.

---

## 1. Cấu hình Biến Môi Trường (.env)

Hệ thống đã tích hợp sẵn cơ chế cảnh báo lỗi tự động và đồng bộ hoạt động nhạy cảm thời gian thực. Để kích hoạt các tính năng này trên môi trường phát triển (Local) hoặc Sản xuất (Production), hãy cấu hình các biến sau trong tệp `.env` của Backend (`be-3d/.env`).

### 1.1 Cấu hình Cảnh báo Lỗi Telegram Bot (Telegram Alert)
Khi hệ thống xảy ra lỗi 500 (Internal Server Error) nghiêm trọng, một tin nhắn chứa chi tiết lỗi, vị trí dòng code, URL, IP và User Agent của Client sẽ lập tức được gửi đến Telegram Group của bạn.

1. **Tạo Telegram Bot mới:**
   - Mở ứng dụng Telegram, tìm kiếm `@BotFather`.
   - Gửi lệnh `/newbot` và đặt tên cho Bot của bạn.
   - Sao chép chuỗi mã Token nhận được (Ví dụ: `123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ`).
2. **Lấy Chat ID của nhóm/kênh nhận tin:**
   - Tạo một nhóm Telegram mới và thêm Bot vừa tạo vào nhóm đó.
   - Tìm kiếm `@userinfobot` hoặc `@MissRose_bot` trên Telegram để lấy Chat ID của nhóm (hoặc truy cập URL: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates` sau khi gửi một tin nhắn bất kỳ vào nhóm có gắn tag bot).
   - ID của nhóm thường bắt đầu bằng dấu trừ (Ví dụ: `-1001234567890`).
3. **Cấu hình vào `.env`:**
   ```env
   TELEGRAM_BOT_TOKEN=123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ
   TELEGRAM_CHAT_ID=-1001234567890
   ```

### 1.2 Cấu hình Đồng bộ Realtime Hoạt động Nhạy cảm (Pusher)
Mỗi khi xảy ra sự kiện nhạy cảm như Đăng nhập, Đăng ký, Đặt hàng hoặc Đổi mật khẩu, hệ thống sẽ ghi log vào DB, File log và đồng thời bắn sự kiện realtime qua Pusher về màn hình quản trị Admin.

1. Đăng ký tài khoản trên [Pusher Channels](https://pusher.com/).
2. Tạo một App mới và chọn Cluster là `ap1` (Asia Pacific - Singapore) để tối ưu độ trễ.
3. Điền thông tin kết nối vào `.env`:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=2156596
   PUSHER_APP_KEY=794a0b225fca675fc9a7
   PUSHER_APP_SECRET=cee10ba3a6fe8c8db26f
   PUSHER_APP_CLUSTER=ap1
   ```

---

## 2. Hướng dẫn thiết lập GitHub Branch Protection

Để tránh việc vô tình ghi đè code, xóa lịch sử commit (force push), hoặc đẩy code chưa qua kiểm duyệt trực tiếp lên nhánh chạy sản xuất (`master` hoặc `main`), bạn cần thiết lập quy tắc bảo vệ nhánh (Branch Protection Rule) trên kho lưu trữ GitHub của mình.

### Các bước thiết lập chi tiết:
1. **Truy cập GitHub Repository:**
   - Đi đến trang GitHub chứa dự án của bạn.
   - Nhấp vào tab **Settings** (Cài đặt) ở thanh công cụ phía trên.
2. **Vào mục quản lý Nhánh:**
   - Ở menu bên trái, dưới phần **Code and automation**, nhấp vào **Branches** (Các nhánh).
3. **Thêm quy tắc bảo vệ nhánh mới:**
   - Trong phần **Branch protection rules**, nhấp vào nút **Add branch ruleset** hoặc **Add rule**.
4. **Cấu hình các thông số bảo vệ:**
   - **Branch pattern name:** Nhập tên nhánh cần bảo vệ (Ví dụ: `master` hoặc `main`).
   - **Require a pull request before merging:**
     - Tích chọn mục này để bắt buộc mọi thay đổi phải thông qua Pull Request chứ không được push trực tiếp.
     - **Required approvals:** Chọn `1` hoặc `2` để yêu cầu phải có sự phê duyệt (approve) của thành viên khác trước khi được phép gộp (merge) code.
   - **Require status checks to pass before merging:**
     - Tích chọn mục này và thêm các luồng kiểm tra tự động (như GitHub Actions CI/CD chạy unit test, build test) để đảm bảo code không làm hỏng dự án trước khi gộp.
   - **Restrict who can push to matching branches:**
     - Chỉ định những thành viên/đội nhóm cụ thể có quyền gộp pull request.
   - **Block force pushes:**
     - *Bắt buộc bật* để ngăn chặn bất kỳ ai chạy lệnh `git push --force` gây mất mát dữ liệu lịch sử commit trên nhánh chính.
   - **Require signed commits:**
     - Yêu cầu mọi commit phải được ký số GPG để xác minh danh tính lập trình viên thực sự (tùy chọn nâng cao).
5. **Lưu cấu hình:**
   - Cuộn xuống cuối trang và nhấp **Create** (hoặc **Save changes**). Hệ thống có thể yêu cầu bạn nhập mật khẩu tài khoản GitHub để xác nhận.

---

## 3. Tổng Quan các Cơ Chế Bảo Mật đã Thiết Lập

### 3.1 Giới hạn 2 Thiết bị Hoạt động Đồng thời (Active Session)
- **Cơ chế:** Kế thừa từ Sanctum. Khi người dùng thực hiện yêu cầu Token mới (Ví dụ: đăng nhập từ thiết bị thứ 3), hệ thống tự động kiểm tra số lượng token hiện có. Nếu đạt giới hạn 2, token cũ nhất sẽ bị thu hồi và xóa khỏi cơ sở dữ liệu.
- **Trải nghiệm:** Thiết bị thứ 1 sẽ lập tức bị logout ở request API tiếp theo do token của nó không còn hiệu lực.

### 3.2 Rate Limiting (Giới hạn Tần suất Request)
- **API Auth nhạy cảm:** Giới hạn **5 requests / phút** trên mỗi địa chỉ IP áp dụng cho các API: Đăng nhập, Đăng ký, Đổi mật khẩu cá nhân, Khôi phục mật khẩu, Đăng nhập qua Google. Giúp ngăn chặn brute-force và spam tài khoản.
- **API chung:** Giới hạn **60 requests / phút** trên mỗi User (hoặc IP nếu chưa đăng nhập) áp dụng cho toàn bộ các API lấy dữ liệu sản phẩm, giỏ hàng, thông tin cửa hàng,... để tránh tấn công từ chối dịch vụ (DDoS) nhẹ và quét dữ liệu (scraping).

### 3.3 Chống Tấn công XSS (Cross-Site Scripting)
- **Frontend (Vue 3):** Định nghĩa directive toàn cục `v-safe-html` sử dụng thư viện `DOMPurify` để lọc sạch mọi thẻ script, thẻ style độc hại trước khi chèn HTML thô động vào trang chi tiết sản phẩm hoặc các nội dung động từ người dùng.

### 3.4 Quản lý Môi Trường An Toàn
- Cấu hình loại trừ file `.env` và các file cấu hình môi trường `.env.*` khỏi Git tracking trên cả frontend và backend, tránh rò rỉ API key, thông tin kết nối DB, hoặc mật khẩu quan trọng khi chia sẻ kho mã nguồn.

### 3.5 Docker Cô Lập & Non-Root User
- **Dockerfile:** Chạy ứng dụng Laravel FPM và Worker bằng một tài khoản non-root chuyên biệt (`laravel` thuộc nhóm `laravel`), hạn chế tối đa nguy cơ kẻ tấn công leo thang đặc quyền (root privilege escalation) nếu container bị tấn công.
- **Docker Compose:** Đóng kín MySQL và Redis bên trong mạng nội bộ (`app-network`). Không mở port `3306` hay `6379` ra ngoài máy host, chỉ có container Nginx (cổng `80/443`) và Laravel được quyền giao tiếp với cơ sở dữ liệu.
