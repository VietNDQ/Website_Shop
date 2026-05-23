<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài liệu API Hệ thống Thương mại Điện tử</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeIn 1s ease-out;
        }

        h1 {
            font-size: 3.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        .section {
            margin-bottom: 3rem;
            animation: slideUp 0.8s ease-out forwards;
            opacity: 0;
        }

        .section h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }

        .api-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .api-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .api-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .method {
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .get {
            background: #10b981;
        }

        .post {
            background: #3b82f6;
        }

        .put,
        .patch {
            background: #f59e0b;
        }

        .delete {
            background: #ef4444;
        }

        .path {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
            color: #e2e8f0;
        }

        .desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .details {
            font-size: 0.85rem;
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .label {
            color: var(--primary);
            font-weight: 600;
            margin-right: 0.5rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .sidebar {
            position: fixed;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-dot:hover,
        .nav-dot.active {
            background: var(--primary);
            transform: scale(1.3);
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <a href="#xac-thuc" class="nav-dot" title="Xác thực"></a>
        <a href="#khach-hang" class="nav-dot" title="Khách hàng"></a>
        <a href="#quan-ly" class="nav-dot" title="Quản lý"></a>
        <a href="#quan-tri" class="nav-dot" title="Quản trị"></a>
    </div>

    <div class="container">
        <header>
            <h1>Tài liệu API</h1>
            <p class="subtitle">Hệ thống quản lý thương mại điện tử 3D - Backend v1.0</p>
        </header>

        <!-- Auth Section -->
        <div id="xac-thuc" class="section" style="animation-delay: 0.1s">
            <h2>1. Xác thực (Xác thực)</h2>

            <div class="api-card">
                <div class="api-header">
                    <span class="method post">POST</span>
                    <span class="path">/api/dang-ky</span>
                </div>
                <p class="desc">Đăng ký tài khoản khách hàng mới.</p>
                <div class="details">
                    <p><span class="label">Dữ liệu gửi lên:</span> { ho_ten, email, mat_khau }</p>
                    <p><span class="label">Phản hồi:</span> { user, token }</p>
                </div>
            </div>

            <div class="api-card">
                <div class="api-header">
                    <span class="method post">POST</span>
                    <span class="path">/api/dang-nhap</span>
                </div>
                <p class="desc">Đăng nhập vào hệ thống.</p>
                <div class="details">
                    <p><span class="label">Dữ liệu gửi lên:</span> { email, mat_khau }</p>
                    <p><span class="label">Phản hồi:</span> { user, token }</p>
                </div>
            </div>

            <div class="api-card">
                <div class="api-header">
                    <span class="method get">GET</span>
                    <span class="path">/api/thong-tin-ca-nhan</span>
                </div>
                <p class="desc">Lấy thông tin người dùng hiện tại (Yêu cầu Token).</p>
            </div>
        </div>

        <!-- Customer Section -->
        <div id="khach-hang" class="section" style="animation-delay: 0.3s">
            <h2>2. Khách Hàng (Khách hàng)</h2>

            <div class="api-card">
                <div class="api-header">
                    <span class="method get">GET</span>
                    <span class="path">/api/san-pham</span>
                </div>
                <p class="desc">Lấy danh sách sản phẩm (có phân trang).</p>
                <div class="details">
                    <p><span class="label">Tham số query:</span> id_danh_muc, page</p>
                </div>
            </div>

            <div class="api-card">
                <div class="api-header">
                    <span class="method post">POST</span>
                    <span class="path">/api/khach-hang/dat-hang</span>
                </div>
                <p class="desc">Tạo đơn hàng mới.</p>
                <div class="details">
                    <p><span class="label">Header:</span> Authorization: Bearer {token}</p>
                    <p><span class="label">Dữ liệu gửi lên:</span> { items: [{id_bien_the, so_luong}], id_dia_chi, phuong_thuc_thanh_toan, ma_giam_gia }</p>
                </div>
            </div>

            <div class="api-card">
                <div class="api-header">
                    <span class="method get">GET</span>
                    <span class="path">/api/khach-hang/don-hang</span>
                </div>
                <p class="desc">Xem lịch sử mua hàng.</p>
            </div>
        </div>

        <!-- Manager Section -->
        <div id="quan-ly" class="section" style="animation-delay: 0.5s">
            <h2>3. Quản Lý (Quản lý)</h2>

            <div class="api-card">
                <div class="api-header">
                    <span class="method patch">PATCH</span>
                    <span class="path">/api/quan-ly/don-hang/{id}/trang-thai</span>
                </div>
                <p class="desc">Cập nhật trạng thái đơn hàng và lưu lịch sử.</p>
                <div class="details">
                    <p><span class="label">Dữ liệu gửi lên:</span> { trang_thai, ghi_chu }</p>
                </div>
            </div>

            <div class="api-card">
                <div class="api-header">
                    <span class="method post">POST</span>
                    <span class="path">/api/quan-ly/kho/dieu-chinh</span>
                </div>
                <p class="desc">Điều chỉnh số lượng tồn kho và lưu nhật ký.</p>
                <div class="details">
                    <p><span class="label">Dữ liệu gửi lên:</span> { id_bien_the, so_luong_thay_doi, loai_giao_dich, ghi_chu }</p>
                </div>
            </div>
        </div>

        <!-- Admin Section -->
        <div id="quan-tri" class="section" style="animation-delay: 0.7s">
            <h2>4. Quản Trị (Quản trị)</h2>

            <div class="api-card">
                <div class="api-header">
                    <span class="method get">GET</span>
                    <span class="path">/api/quan-tri/thong-ke</span>
                </div>
                <p class="desc">Xem báo cáo doanh thu, đơn hàng và cảnh báo tồn kho.</p>
                <div class="details">
                    <p><span class="label">Kết quả trả về:</span> { summary, low_stock, monthly_revenue }</p>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.querySelectorAll('.nav-dot').forEach(dot => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = dot.getAttribute('href');
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });

                document.querySelectorAll('.nav-dot').forEach(d => d.classList.remove('active'));
                dot.classList.add('active');
            });
        });
    </script>
</body>

</html>