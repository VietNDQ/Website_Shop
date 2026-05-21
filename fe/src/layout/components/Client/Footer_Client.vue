<template>
  <!-- ─── FOOTER ─── -->
  <footer>
    <div class="footer-main">
      <div class="footer-brand">
        <a href="#" class="footer-logo">MÔ HÌNH<span></span>BALAB</a>
       <p class="footer-tagline">
          Điểm đến hàng đầu cho các mô hình thu nhỏ có độ chính xác cao. Đam mê và phục vụ giới sưu tầm từ năm 2024.
        </p>
        <div class="footer-socials">
          <a href="#" class="social-btn" title="Facebook">FB</a>
          <a href="#" class="social-btn" title="Instagram">IG</a>
          <a href="#" class="social-btn" title="TikTok">TK</a>
          <a href="#" class="social-btn" title="YouTube">YT</a>
        </div>
      </div>

      <div>
        <div class="footer-col-title">Sản Phẩm</div>
        <ul class="footer-links">
          <li><a href="#">Mô hình</a></li>
          <li><a href="#">Khuyến mãi</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-col-title">Thông Tin</div>
        <ul class="footer-links">
          <li><a href="#">Về chúng tôi</a></li>
          <li><a href="#">Blog & Đánh giá</a></li>
          <li><a href="#">Hướng dẫn sưu tầm</a></li>
          <li><a href="#">Bảng tỷ lệ mô hình</a></li>
          <li><a href="#">Thương hiệu</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-col-title">Hỗ Trợ</div>
        <ul class="footer-links">
          <li><a href="#">Câu hỏi thường gặp (FAQ)</a></li>
          <li><a href="#">Chính sách giao hàng</a></li>
          <li><a href="#">Đổi trả & Hoàn tiền</a></li>
          <li><a href="#">Theo dõi đơn hàng</a></li>
          <li><a href="#">Liên hệ</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-col-title">Đăng ký nhận tin</div>
        <div class="footer-newsletter">
          <p class="footer-newsletter-desc">
            Nhận thông tin sớm nhất về các đợt pre-order và ưu đãi độc quyền.
          </p>
          <div class="newsletter-input-wrap">
            <input
              class="newsletter-input"
              type="email"
              placeholder="Địa chỉ email của bạn"
            />
            <router-link to="/login" class="newsletter-btn"><b>Đăng ký</b></router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-bottom-inner">
        <span class="footer-copy">
          © 2026 Mô Hình BALAB. Bảo lưu mọi quyền.
        </span>
        <div class="footer-payments">
          <div class="payment-tag">COD</div>
          <div class="payment-tag">MOMO</div>
          <div class="payment-tag">VNPAY</div>
          <div class="payment-tag">ZALOPAY</div>
          <div class="payment-tag">VISA</div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'FooterComponent'
};
</script>