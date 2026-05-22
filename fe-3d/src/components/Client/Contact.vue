<template>
  <div class="contact-page-container">
    <div class="contact-hero">
      <div class="contact-hero-overlay"></div>
      <div class="contact-hero-content">
        <h1>LIÊN HỆ VỚI BALAB</h1>
        <p>Gửi thư hỗ trợ, yêu cầu in 3D hoặc đóng góp ý kiến để chúng tôi phục vụ bạn tốt hơn.</p>
      </div>
    </div>

    <div class="contact-content-wrap">
      <div class="contact-grid">
        <!-- Contact Info Info Card -->
        <div class="contact-info-card">
          <h2>Thông tin liên hệ</h2>
          <p class="info-desc">Bạn có câu hỏi hoặc cần báo giá in 3D nhanh? Hãy liên hệ trực tiếp với chúng tôi qua các kênh dưới đây.</p>

          <div class="info-details">
            <div class="info-item">
              <div class="info-icon">
                <i class="fa-solid fa-location-dot"></i>
              </div>
              <div class="info-text">
                <h3>Địa chỉ cửa hàng</h3>
                <p>Số 123 Đường Mô Hình, Quận Cầu Giấy, Hà Nội</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fa-solid fa-phone"></i>
              </div>
              <div class="info-text">
                <h3>Hotline hỗ trợ</h3>
                <p>1800 2097 (Miễn phí cuộc gọi)</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div class="info-text">
                <h3>Email liên hệ</h3>
                <p>mohinhbalab@gmail.com</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fa-solid fa-clock"></i>
              </div>
              <div class="info-text">
                <h3>Thời gian làm việc</h3>
                <p>Thứ 2 - Chủ nhật: 08:00 - 22:00</p>
              </div>
            </div>
          </div>

          <div class="social-links-wrap">
            <a href="#" class="social-circle"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-circle"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-circle"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" class="social-circle"><i class="fa-brands fa-youtube"></i></a>
          </div>
        </div>

        <!-- Contact Form Card -->
        <div class="contact-form-card">
          <h2>Gửi thư liên hệ</h2>
          <p class="form-desc">Vui lòng điền thông tin vào mẫu dưới đây, chúng tôi sẽ phản hồi lại bạn trong vòng 24 giờ.</p>

          <form @submit.prevent="submitContactForm" class="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label for="ho_ten">Họ tên của bạn <span class="required">*</span></label>
                <input
                  type="text"
                  id="ho_ten"
                  v-model.trim="form.ho_ten"
                  placeholder="Nguyễn Văn A"
                  required
                />
              </div>

              <div class="form-group">
                <label for="email">Địa chỉ Email <span class="required">*</span></label>
                <input
                  type="email"
                  id="email"
                  v-model.trim="form.email"
                  placeholder="example@gmail.com"
                  required
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="so_dien_thoai">Số điện thoại</label>
                <input
                  type="tel"
                  id="so_dien_thoai"
                  v-model.trim="form.so_dien_thoai"
                  placeholder="0987654321"
                />
              </div>

              <div class="form-group">
                <label for="tieu_de">Tiêu đề thư <span class="required">*</span></label>
                <input
                  type="text"
                  id="tieu_de"
                  v-model.trim="form.tieu_de"
                  placeholder="Yêu cầu báo giá in 3D / Hỗ trợ đơn hàng..."
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="noi_dung">Nội dung thư <span class="required">*</span></label>
              <textarea
                id="noi_dung"
                v-model.trim="form.noi_dung"
                rows="6"
                placeholder="Nhập nội dung tin nhắn hoặc thông số yêu cầu chi tiết tại đây..."
                required
              ></textarea>
            </div>

            <button type="submit" class="btn-submit" :disabled="submitting">
              <span v-if="submitting"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang gửi...</span>
              <span v-else><i class="fa-solid fa-paper-plane"></i> Gửi tin nhắn</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ContactPage',
  data() {
    return {
      form: {
        ho_ten: '',
        email: '',
        so_dien_thoai: '',
        tieu_de: '',
        noi_dung: ''
      },
      submitting: false
    };
  },
  mounted() {
    this.prefillUserInfo();
  },
  methods: {
    prefillUserInfo() {
      const token = localStorage.getItem("token_client");
      const name = localStorage.getItem("ho_ten_client");
      if (token) {
        this.form.ho_ten = name || '';
        
        // Fetch fresh profile data to get email/phone if logged in
        axios.get('/api/thong-tin-ca-nhan/profile', {
          headers: { Authorization: 'Bearer ' + token }
        }).then(res => {
          if (res.data.status && res.data.data) {
            const u = res.data.data;
            this.form.ho_ten = u.name || this.form.ho_ten;
            this.form.email = u.email || '';
            this.form.so_dien_thoai = u.phone || '';
          }
        }).catch(() => {
          // Ignore, fallback to empty
        });
      }
    },
    async submitContactForm() {
      this.submitting = true;
      try {
        const token = localStorage.getItem("token_client");
        const headers = {};
        if (token) {
          headers.Authorization = 'Bearer ' + token;
        }

        const res = await axios.post('/api/lien-he', this.form, { headers });
        if (res.data.status) {
          if (this.$toast) {
            this.$toast.success(res.data.message || 'Gửi thư liên hệ thành công!');
          } else {
            alert(res.data.message || 'Gửi thư liên hệ thành công!');
          }
          // Clear form except user info
          this.form.tieu_de = '';
          this.form.noi_dung = '';
        } else {
          throw new Error(res.data.message || 'Gửi thư liên hệ thất bại.');
        }
      } catch (err) {
        console.error(err);
        const errorMsg = err.response?.data?.message || 'Có lỗi xảy ra khi gửi thư liên hệ. Vui lòng thử lại!';
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

<style scoped>
.contact-page-container {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: 'DM Sans', sans-serif;
  color: #1e293b;
}

.contact-hero {
  position: relative;
  background: linear-gradient(135deg, #1e1b4b 0%, #311018 100%);
  padding: 80px 20px;
  text-align: center;
  color: #ffffff;
  overflow: hidden;
}

.contact-hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: radial-gradient(circle at 30% 30%, rgba(215, 0, 24, 0.15), transparent 60%);
  z-index: 1;
}

.contact-hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.contact-hero-content h1 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 42px;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
  text-transform: uppercase;
}

.contact-hero-content p {
  font-size: 16px;
  color: #cbd5e1;
  max-width: 600px;
  margin: 0 auto;
}

.contact-content-wrap {
  max-width: 1200px;
  margin: -40px auto 60px;
  padding: 0 20px;
  position: relative;
  z-index: 10;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1.6fr;
  gap: 30px;
}

@media (max-width: 768px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
  .contact-hero-content h1 {
    font-size: 32px;
  }
}

.contact-info-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.contact-info-card h2 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  margin-bottom: 15px;
}

.info-desc {
  color: #64748b;
  font-size: 14.5px;
  line-height: 1.6;
  margin-bottom: 30px;
}

.info-details {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-bottom: 40px;
}

.info-item {
  display: flex;
  gap: 16px;
}

.info-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background-color: rgba(215, 0, 24, 0.08);
  color: #d70018;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.info-text h3 {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.info-text p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.social-links-wrap {
  display: flex;
  gap: 12px;
}

.social-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-color: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 15px;
  text-decoration: none;
}

.social-circle:hover {
  background-color: #d70018;
  color: #ffffff;
  transform: translateY(-3px);
}

.contact-form-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.contact-form-card h2 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  margin-bottom: 15px;
}

.form-desc {
  color: #64748b;
  font-size: 14.5px;
  line-height: 1.6;
  margin-bottom: 30px;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 576px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
}

.required {
  color: #d70018;
}

.form-group input,
.form-group textarea {
  padding: 12px 16px;
  border: 1.5px solid #cbd5e1;
  border-radius: 10px;
  background-color: #ffffff;
  font-size: 14px;
  transition: all 0.2s ease;
  font-family: 'DM Sans', sans-serif;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #d70018;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.08);
}

.btn-submit {
  align-self: flex-start;
  padding: 12px 28px;
  background-color: #d70018;
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-submit:hover {
  background-color: #b00013;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}

.btn-submit:disabled {
  background-color: #fda4af;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
</style>
