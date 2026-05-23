# 🧸 Hệ Thống Thương Mại Điện Tử Shop BALAB

> [!IMPORTANT]
> ### 🌐 TRẢI NGHIỆM LIVE DEMO TRỰC TUYẾN
> Hệ thống cửa hàng BALAB đã được deploy và vận hành chính thức trên Cloud Server tại địa chỉ:
> ## 👉 **[balab.studio](https://balab.studio/)** 👈
>
> ---
> **🔑 TÀI KHOẢN DÙNG THỬ HỆ THỐNG:**
> * **Giao diện Client (Khách hàng)**: 
>   * Bạn có thể tự đăng ký tài khoản mới hoặc đăng nhập nhanh bằng **Google OAuth 2.0**.
>   * Hoặc dùng tài khoản sẵn có: `nguyenqviet3885@gmail.com` | Mật khẩu: `123456`
> * **Giao diện Admin (Quản trị)**: 
>   * Truy cập: **[balab.studio/admin](https://balab.studio/admin)** (Hoặc đăng nhập admin rồi click nút "Quản trị" trên header)
>   * Email: `admin@gmail.com` | Mật khẩu: `123456`

[![Website](https://img.shields.io/badge/Website-balab.studio-brightgreen.svg?style=for-the-badge&logo=google-chrome)](https://balab.studio/)
[![Laravel Version](https://img.shields.io/badge/Laravel-v12.0-red.svg?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue Version](https://img.shields.io/badge/Vue.js-v3.3-green.svg?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Vite](https://img.shields.io/badge/Vite-v4.4-blue.svg?style=for-the-badge&logo=vite)](https://vitejs.dev)
[![Docker](https://img.shields.io/badge/Docker-Enabled-blue.svg?style=for-the-badge&logo=docker)](https://www.docker.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

Hệ thống bán hàng trực tuyến chuyên nghiệp chuyên cung cấp các sản phẩm Shop nhân vật (Anime Figures) và Shop 3D, được phát triển với kiến trúc tách biệt hoàn toàn giữa **Backend API** (Laravel 12) và **Frontend SPA** (Vue 3, Vite, Pinia).

Dự án tích hợp đầy đủ quy trình bán hàng khép kín, quản trị bán hàng thông minh (Admin Dashboard), đồng bộ dữ liệu thời gian thực (Pusher), hệ thống cảnh báo lỗi tự động qua Telegram (Telegram Alert), và các tiêu chuẩn bảo mật nghiêm ngặt nhất.

---

## 🏗️ Cấu Trúc Thư Mục Hệ Thống

Dự án được phân chia rõ ràng thành các phân hệ riêng biệt để dễ dàng phát triển và triển khai:

```text
web-mohinh/
├── be-3d/                            # PHÂN HỆ BACKEND (Laravel 12 API)
│   ├── app/                          # Core Logic (Models, Controllers, Requests, Middlewares)
│   │   ├── Http/
│   │   │   ├── Controllers/Api/      # API Controllers (Auth, Product, Review, Order...)
│   │   │   ├── Middleware/           # Xác thực & phân quyền (RoleMiddleware, Sanctum...)
│   │   │   └── Requests/             # Các lớp Form Request dùng Validate dữ liệu
│   │   └── Models/                   # Danh sách Eloquent Models (SanPham, NguoiDung, DanhGia...)
│   ├── bootstrap/                    # Khởi chạy Framework & cấu hình Exception Handler (Telegram Bot)
│   ├── config/                       # Toàn bộ cấu hình hệ thống (Auth, Broadcasting, Database...)
│   ├── database/                     # Migrations & Seeders dữ liệu mẫu ban đầu
│   ├── routes/                       # Khai báo các API routes (api.php)
│   └── tests/                        # Hệ thống Unit Test & Feature Test tự động
├── fe/                               # PHÂN HỆ FRONTEND (Vue 3 Single Page Application)
│   ├── public/                       # Assets tĩnh (Images, CSS, JS dùng chung cho trang admin)
│   │   └── style_admin/              # CSS layouts & dashboard của Admin
│   ├── src/
│   │   ├── components/               # Components giao diện Vue
│   │   │   ├── Admin/                # Bảng điều khiển quản trị (Analytics, Orders, Reviews, Staff...)
│   │   │   ├── Auth/                 # Giao diện Đăng nhập, Đăng ký, Đổi mật khẩu
│   │   │   └── Client/               # Giao diện Khách hàng (Cart, Checkout, Home, Products, Detail...)
│   │   ├── layout/                   # Layout wrappers (Header_Client, Footer_Client, Sidebar_Admin...)
│   │   ├── router/                   # Cấu hình Vue Router, Route Guards (checkAdmin, checkClient)
│   │   ├── store/                    # Quản lý trạng thái bằng Pinia (authStore, wishlistStore)
│   │   ├── index.css                 # Hệ thống CSS dùng chung cho giao diện Client
│   │   ├── App.vue                   # Component gốc của ứng dụng
│   │   └── main.js                   # Điểm khởi tạo và mount ứng dụng Vue 3
│   └── package.json                  # Quản lý thư viện phụ thuộc Frontend (Vite, Axios, Pinia...)
├── docker-compose.yml                # Cấu hình khởi chạy nhanh bằng Docker Container
├── DEVELOPER_GUIDE.md                # Tài liệu quy chuẩn phát triển (Coding Convention)
└── SECURITY_OPERATIONS_GUIDE.md      # Hướng dẫn vận hành & cấu hình các tính năng bảo mật
```

---

## 🚀 Công Nghệ Sử Dụng (Tech Stack)

### 🖥️ Backend (be-3d)
* **Framework**: Laravel 12.x & PHP 8.2+
* **Authentication**: Laravel Sanctum (Xác thực bằng Token-based bảo mật cao)
* **Realtime Server**: Pusher Channels (Bắn sự kiện realtime phục vụ hệ thống log và thông báo Admin)
* **Error Monitoring**: Telegram Bot API (Tự động gửi stack trace lỗi HTTP 500 về nhóm Telegram)
* **Database & Caching**: MySQL 8.0 (Lưu trữ quan hệ) + Redis (Cache & Session)
* **Testing**: PHPUnit / Feature Testing

### 🎨 Frontend (fe)
* **Framework**: Vue 3 (Options API dễ bảo trì)
* **Build Tool**: Vite (Tốc độ khởi động và HMR cực nhanh)
* **State Management**: Pinia (Quản lý trạng thái Auth, Cart, Wishlist tập trung)
* **HTTP Client**: Axios (Hỗ trợ cấu hình Interceptor gửi kèm Bearer Token)
* **Styling**: Vanilla CSS (Tối ưu hóa layout tùy biến) + Tailwind CSS 4.0
* **Notification**: `vue-toastification` & `@meforma/vue-toaster`
* **Google Integration**: `vue3-google-login` (Tích hợp Đăng nhập bằng Google)
* **Utility Libraries**: `html2canvas` (Chụp giao diện), `jspdf` (Xuất PDF), `xlsx` (Xuất báo cáo Excel)

---

## ✨ Các Tính Năng Nổi Bật Của Hệ Thống

Hệ thống BALAB được thiết kế đầy đủ tính năng của một trang thương mại điện tử chuyên nghiệp:

### 👤 1. Phân Quyền & Xác Thực (Authentication & Authorization)
- **Hệ thống Đa Vai Trò (Multi-Role)**: Phân quyền rõ rệt thành **Admin**, **Nhân viên (Staff)** và **Khách hàng (Customer)**.
- **Xác thực Google OAuth 2.0**: Cho phép đăng nhập nhanh bằng tài khoản Google. Hỗ trợ liên kết/hủy liên kết tài khoản Google trực tiếp trong trang quản lý tài khoản cá nhân.
- **Bảo Vệ Router Giao Diện**: Route Guards (`checkAdmin`, `checkClient`) kiểm tra quyền truy cập cục bộ trên Frontend trước khi load component, tích hợp hiển thị cảnh báo đẹp mắt qua Toaster nếu truy cập trái phép.
- **Giới Hạn Phiên Hoạt Động (Session Limit)**: Giới hạn tối đa **2 thiết bị đăng nhập đồng thời** trên một tài khoản. Đăng nhập thiết bị thứ 3 sẽ tự động hủy kích hoạt token của thiết bị cũ nhất.

### 📦 2. Quản Lý Shop & Sản Phẩm
- **Thuộc Tính Đa Dạng (Attributes)**: Định nghĩa động các thuộc tính sản phẩm (`ThuocTinh` - như Kích thước, Màu sắc, Chất liệu).
- **Biến Thể Sản Phẩm (Product Variants)**: Mỗi sản phẩm hỗ trợ nhiều biến thể (`BienTheSanPham`) với hình ảnh riêng, giá gốc (cost price), giá bán lẻ, và số lượng tồn kho (`so_luong_ton_kho`) độc lập.
- **Đánh Giá & Phản Hồi (Reviews & Ratings)**: Khách hàng đánh giá sản phẩm (từ 1 đến 5 sao) kèm hình ảnh thực tế sau khi nhận hàng. Admin quản lý đánh giá trực quan và duyệt nhanh chóng.

### 🛒 3. Quy Trình Bán Hàng Khép Kín (E-Commerce Workflow)
- **Giỏ Hàng & Yêu Thích**: Quản lý giỏ hàng thông minh và danh sách sản phẩm yêu thích (`wishlistStore`) lưu trữ đồng bộ.
- **Quy Trình Đơn Hàng Chặt Chẽ**: Trạng thái đơn hàng chuyển đổi logic:
  $$\text{Chờ xử lý (cho\_xu\_ly)} \rightarrow \text{Đang chuẩn bị (dang\_chuan\_bi)} \rightarrow \text{Đang giao (dang\_giao)} \rightarrow \text{Đã giao (da\_giao) / Đã hủy (da\_huy)}$$
- **Bộ Lọc Đơn Hàng Admin**: Màn hình quản lý đơn hàng của Admin nhóm trạng thái "Đang chuẩn bị" và "Đang giao" giúp nhân viên dễ dàng vận hành và chuyển trạng thái vận chuyển nhanh chóng, tránh bỏ sót đơn.
- **Đồng Bộ Đơn Hàng Real-time**: Khi khách hàng đặt hàng thành công trên trang Client, hệ thống tự động bắn sự kiện qua Pusher. Màn hình quản lý đơn hàng của Admin ngay lập tức nhận diện, phát âm thanh cảnh báo bằng Web Audio API, hiển thị thông báo Toaster chi tiết mã đơn hàng + tổng số tiền, và cập nhật danh sách đơn hàng theo thời gian thực mà không cần tải lại trang.

### 📊 4. Báo Cáo & Quản Trị Hệ Thống
- **Bảng Điều Khiển Quản Trị (Admin Dashboard)**: Biểu đồ thống kê doanh thu, số lượng đơn hàng, sản phẩm bán chạy, khách hàng mới theo thời gian.
- **Xuất Báo Cáo**: Hỗ trợ xuất dữ liệu hóa đơn, sản phẩm và khách hàng ra file Excel (`xlsx`) hoặc hóa đơn PDF (`jspdf`) chuyên nghiệp.

---

## 🔒 Các Cơ Chế Bảo Mật & Giám Sát Lỗi

Dự án được cấu hình tuân thủ các quy tắc an toàn bảo mật thông tin:
1. **API Rate Limiting**:
   - Giới hạn **5 requests/phút** cho các API nhạy cảm (Đăng nhập, Đăng ký, Đổi mật khẩu, API Google Login) để chống tấn công brute-force.
   - Giới hạn **60 requests/phút** cho các API đọc dữ liệu sản phẩm, trang chủ.
2. **Chống Tấn Công XSS**: Sử dụng thư viện `DOMPurify` thông qua directive tùy chỉnh `v-safe-html` trên Frontend nhằm lọc sạch mã HTML động từ mô tả sản phẩm hoặc nội dung nhập từ người dùng.
3. **Cảnh Báo Lỗi Qua Telegram (Telegram Exception Handler)**: Bất kỳ lỗi hệ thống HTTP 500 nào xảy ra trên server sẽ được bắt lại ở tầng Exception Handler và lập tức gửi thông điệp chi tiết (Tên lỗi, File, Line, IP, URL, User-Agent) về nhóm Telegram.
4. **Cô Lập Mạng Docker (Docker Isolation)**: Database MySQL và Redis cache không public port ra môi trường bên ngoài, chỉ giao tiếp nội bộ trong mạng ảo (`app-network`) với container Laravel.

---

## 🛠️ Hướng Dẫn Cài Đặt & Khởi Chạy Dự Án

### Yêu cầu tiên quyết (Prerequisites)
* **PHP** từ `>= 8.2` trở lên
* **Composer** dùng quản lý thư viện PHP
* **Node.js** `>= 18.x` & **npm** `>= 9.x`
* **MySQL Server** `>= 8.0`
* *Tùy chọn*: **Docker & Docker Compose** nếu muốn chạy môi trường container.

---

### Môi trường phát triển cục bộ (Local Development)

#### 1. Cấu hình Backend (`be-3d`)

1. Di chuyển vào thư mục backend:
   ```bash
   cd be-3d
   ```
2. Cài đặt các gói thư viện phụ thuộc:
   ```bash
   composer install
   ```
3. Tạo file cấu hình môi trường `.env` từ file mẫu:
   ```bash
   cp .env.example .env
   ```
4. Mở file `.env` vừa tạo và cập nhật các thông số:
   - Kết nối database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Cấu hình Pusher (`PUSHER_APP_ID`, `PUSHER_APP_KEY`...)
   - Cấu hình Telegram Bot (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`)
5. Tạo khóa bí mật cho Laravel:
   ```bash
   php artisan key:generate
   ```
6. Chạy migrations để khởi tạo database và tạo dữ liệu mẫu seed:
   ```bash
   php artisan migrate --seed
   ```
7. Tạo liên kết thư mục chứa file tải lên (hình ảnh sản phẩm, avatar):
   ```bash
   php artisan storage:link
   ```
8. Khởi chạy máy chủ phát triển cục bộ:
   ```bash
   php artisan serve
   ```
   *Mặc định backend API sẽ chạy tại: `http://127.0.0.1:8000`*

---

#### 2. Cấu hình Frontend (`fe`)

1. Di chuyển vào thư mục frontend (từ thư mục gốc):
   ```bash
   cd fe
   ```
2. Cài đặt các thư viện Node.js phụ thuộc:
   ```bash
   npm install
   ```
3. Khởi chạy Vite Dev Server:
   ```bash
   npm run dev
   ```
   *Mặc định frontend sẽ chạy tại địa chỉ: `http://localhost:5173`*
4. Đóng gói biên dịch ứng dụng khi đưa lên môi trường sản xuất (Production Build):
   ```bash
   npm run build
   ```

---

### Khởi chạy dự án bằng Docker (Khuyên Dùng)

Dự án đã được cấu hình sẵn môi trường Docker hóa toàn diện thông qua tệp `docker-compose.yml` ở thư mục gốc.

1. Đảm bảo máy tính của bạn đã bật **Docker Desktop**.
2. Tại thư mục gốc của dự án, khởi chạy lệnh sau để build và chạy các container ngầm:
   ```bash
   docker-compose up -d
   ```
3. Hệ thống sẽ tự động cấu hình và khởi chạy các service sau:
   - **shop-nginx**: Cổng `80` (HTTP) và `443` (HTTPS) làm Web Server điều hướng.
   - **shop-app**: Container PHP 8.2-FPM phục vụ xử lý mã nguồn Laravel.
   - **shop-mysql**: Cơ sở dữ liệu MySQL chạy độc lập trong mạng ảo.
   - **shop-redis**: Môi trường cache & session tốc độ cao.
4. Kiểm tra trạng thái các container:
   - `docker-compose ps`
5. Để dừng hệ thống:
   ```bash
   docker-compose down
   ```

---

## 📖 Tài Liệu Tham Khảo Thêm

* Để xem chi tiết các tiêu chuẩn code, định dạng viết Route, Controller, Request và Component Vue, vui lòng đọc [DEVELOPER_GUIDE.md](file:///d:/Code%20Dao/web-mohinh/DEVELOPER_GUIDE.md).
* Để cấu hình Telegram Exception Alert, Pusher Realtime, thiết lập an toàn GitHub Branch Protection và bảo mật Docker, vui lòng đọc [SECURITY_OPERATIONS_GUIDE.md](file:///d:/Code%20Dao/web-mohinh/SECURITY_OPERATIONS_GUIDE.md).

---

## ✉️ Thông Tin Liên Hệ & Tác Giả

Nếu bạn có bất kỳ câu hỏi nào về dự án, cần hỗ trợ kỹ thuật hoặc muốn trao đổi cơ hội hợp tác, vui lòng liên hệ:

* **Tác giả / Phát triển**: **Nguyễn Quốc Việt**
* **Email**: [nguyenqviet3885@gmail.com](mailto:nguyenqviet3885@gmail.com)

---
*Chúc bạn có trải nghiệm lập trình tuyệt vời cùng hệ thống BALAB!* 🚀
