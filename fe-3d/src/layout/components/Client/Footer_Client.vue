<template>
  <!-- ─── FOOTER ─── -->
  <footer>
    <div class="footer-main">
      <div class="footer-brand">
        <a href="#" class="footer-logo">Cửa Hàng<span></span>BALAB</a>
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
          <li><a href="#">Phụ kiện</a></li>
          <li><a href="#">Đồ trang trí</a></li>
          <li><a href="#">Khuyến mãi</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-col-title">Thông Tin</div>
        <ul class="footer-links">
          <li><a href="#">Về chúng tôi</a></li>
          <li><router-link to="/blog">Blog & Tin tức</router-link></li>
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
          <li><router-link to="/lien-he">Liên hệ</router-link></li>
        </ul>
      </div>

      <div>
        <div class="footer-col-title">Đăng ký nhận tin</div>
        <div class="footer-newsletter">
          <p class="footer-newsletter-desc">
            Nhận thông tin sớm nhất về các đợt pre-order và ưu đãi độc quyền.
          </p>
          <form @submit.prevent="subscribeNewsletter" class="newsletter-input-wrap">
            <input
              class="newsletter-input"
              type="email"
              placeholder="Địa chỉ email của bạn"
              v-model.trim="newsletterEmail"
              :disabled="submitting"
              required
            />
            <button type="submit" class="newsletter-btn" :disabled="submitting">
              <span v-if="submitting">
                <i class="fa-solid fa-spinner fa-spin"></i>
              </span>
              <span v-else><b>Đăng ký</b></span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-bottom-inner">
        <span class="footer-copy">
          © 2026 Của Hàng BALAB. Bảo lưu mọi quyền.
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
import axios from 'axios';

export default {
  name: 'FooterComponent',
  data() {
    return {
      newsletterEmail: '',
      submitting: false
    };
  },
  methods: {
    async subscribeNewsletter() {
      const email = this.newsletterEmail.trim();
      if (!email) return;

      // Email regex validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        if (this.$toast) {
          this.$toast.error("Vui lòng nhập địa chỉ email hợp lệ!");
        } else {
          alert("Vui lòng nhập địa chỉ email hợp lệ!");
        }
        return;
      }

      this.submitting = true;
      try {
        const token = localStorage.getItem("token_client");
        const headers = {};
        if (token) {
          headers.Authorization = 'Bearer ' + token;
        }

        const res = await axios.post('/api/lien-he', {
          ho_ten: 'Khách hàng đăng ký nhận tin',
          email: email,
          so_dien_thoai: '',
          tieu_de: 'Đăng ký nhận tin bản tin',
          noi_dung: 'Khách hàng đăng ký nhận thông tin pre-order và ưu đãi độc quyền từ Footer.'
        }, { headers });

        if (res.data.status) {
          if (this.$toast) {
            this.$toast.success(res.data.message || "Đăng ký nhận tin thành công! Cảm ơn bạn.");
          } else {
            alert(res.data.message || "Đăng ký nhận tin thành công! Cảm ơn bạn.");
          }
          this.newsletterEmail = '';
        } else {
          throw new Error(res.data.message || "Gửi đăng ký nhận tin thất bại.");
        }
      } catch (err) {
        console.error(err);
        const errorMsg = err.response?.data?.message || "Đăng ký nhận tin thất bại. Vui lòng thử lại!";
        if (this.$toast) {
          this.$toast.error(errorMsg);
        } else {
          alert(errorMsg);
        }
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>