<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi Phục Mật Khẩu - BALAB</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            width: 100%;
            padding: 40px 0;
            background-color: #f1f5f9;
        }
        .email-container {
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0;
        }
        .email-header {
            background: linear-gradient(135deg, #f43f5e, #8b5cf6);
            padding: 30px 20px;
            text-align: center;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            margin: 0;
            font-family: 'Barlow Condensed', 'Segoe UI', sans-serif;
        }
        .logo-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin: 5px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .email-body {
            padding: 40px 35px;
            text-align: center;
        }
        .welcome-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .description-text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 30px;
        }
        .btn-wrapper {
            margin: 30px 0;
        }
        .btn-reset {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #f43f5e, #8b5cf6);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(244, 63, 94, 0.3);
            transition: all 0.2s ease;
        }
        .warning-box {
            background-color: #f8fafc;
            border-left: 4px solid #8b5cf6;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            text-align: left;
            margin-top: 30px;
        }
        .warning-text {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 25px 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            font-size: 12px;
            color: #94a3b8;
            margin: 5px 0;
            line-height: 1.5;
        }
        .footer-link {
            color: #8b5cf6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1 class="logo-text">BALAB</h1>
                <p class="logo-sub">Premium Models & Collectibles</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <h2 class="welcome-title">Xin chào {{ $ho_ten }},</h2>
                <p class="description-text">
                    Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn tại cửa hàng BALAB.
                    Vui lòng bấm vào nút bên dưới để tiến hành thiết lập mật khẩu mới:
                </p>

                <div class="btn-wrapper">
                    <a href="{{ $reset_url }}" class="btn-reset" target="_blank">Khôi Phục Mật Khẩu</a>
                </div>

                <p class="description-text" style="font-size: 13px; color: #94a3b8; margin-top: 15px;">
                    Nếu nút trên không hoạt động, bạn có thể copy link sau và dán vào trình duyệt: <br>
                    <a href="{{ $reset_url }}" style="color: #8b5cf6; word-break: break-all;">{{ $reset_url }}</a>
                </p>

                <div class="warning-box">
                    <p class="warning-text">
                        <strong>Lưu ý:</strong> Liên kết khôi phục mật khẩu này sẽ hết hiệu lực sau <strong>60 phút</strong>.
                        Nếu bạn không yêu cầu thay đổi này, hãy bỏ qua email này một cách an toàn.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p class="footer-text">Email này được gửi tự động, vui lòng không trả lời trực tiếp.</p>
                <p class="footer-text">© 2026 BALAB Store. Mọi quyền được bảo lưu.</p>
                <p class="footer-text">Cần hỗ trợ? Liên hệ <a href="mailto:support@balab.vn" class="footer-link">support@balab.vn</a></p>
            </div>
        </div>
    </div>
</body>
</html>
