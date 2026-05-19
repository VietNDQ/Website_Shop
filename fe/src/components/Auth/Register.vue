<template>
  <div class="login-container">
    <!-- Animated colorful background blobs for ultimate glassmorphism context -->
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>

    <div class="glass-login-card">
      <!-- Logo and Brand -->
      <div class="brand-section">
        <div class="logo-wrapper">
          <i class="fa-solid fa-cube logo-icon"></i>
        </div>
        <h1 class="brand-name">SKYLINE</h1>
        <p class="brand-tagline">Premium Models & Collectibles</p>
      </div>

      <!-- Register Form -->
      <form @submit.prevent="handleRegister" class="login-form">
        <!-- Welcoming text -->
        <p class="role-welcome-text">
          Tạo tài khoản mới để bắt đầu bộ sưu tập của bạn!
        </p>

        <!-- Full Name Field -->
        <div class="input-group">
          <label for="fullname" class="input-label">Họ và tên</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-user field-icon"></i>
            <input 
              type="text" 
              id="fullname" 
              v-model="fullname" 
              placeholder="Nhập họ tên của bạn" 
              required
              class="glass-input"
            />
          </div>
        </div>

        <!-- Email Field -->
        <div class="input-group">
          <label for="email" class="input-label">Địa chỉ Email</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-envelope field-icon"></i>
            <input 
              type="email" 
              id="email" 
              v-model="email" 
              placeholder="nhap_email@vi-du.com" 
              required
              class="glass-input"
            />
          </div>
        </div>

        <!-- Password Field -->
        <div class="input-group">
          <label for="password" class="input-label">Mật khẩu</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock field-icon"></i>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              id="password" 
              v-model="password" 
              placeholder="••••••••" 
              required
              class="glass-input"
            />
            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              class="eye-btn"
            >
              <i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div class="input-group">
          <label for="confirmPassword" class="input-label">Xác nhận Mật khẩu</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-shield-halved field-icon"></i>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              id="confirmPassword" 
              v-model="confirmPassword" 
              placeholder="••••••••" 
              required
              class="glass-input"
            />
          </div>
        </div>

        <!-- Terms Checkbox -->
        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" v-model="acceptTerms" required class="glass-checkbox" />
            <span class="custom-checkbox"></span>
            Tôi đồng ý với <a href="#" class="forgot-link" style="margin-left: 4px;">Điều khoản & Dịch vụ</a>
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang xử lý...</span>
          <span v-else>
            Đăng Ký Tài Khoản
            <i class="fa-solid fa-user-plus"></i>
          </span>
        </button>
      </form>

      <!-- Social Logins -->
      <div class="social-login-section">
        <div class="divider">
          <span class="divider-text">Hoặc đăng ký bằng</span>
        </div>
        <div class="social-buttons">
          <button @click="loginWithSocial('Google')" class="social-btn google-btn" type="button">
            <i class="fa-brands fa-google social-icon"></i> Google
          </button>
          <button @click="loginWithSocial('Facebook')" class="social-btn facebook-btn" type="button">
            <i class="fa-brands fa-facebook-f social-icon"></i> Facebook
          </button>
        </div>
      </div>

      <!-- Card Footer -->
      <div class="card-footer">
        <p class="footer-link-text">
          Đã có tài khoản? <router-link to="/login" class="signup-link">Đăng nhập ngay</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Register",
  data() {
    return {
      fullname: '',
      email: '',
      password: '',
      confirmPassword: '',
      acceptTerms: false,
      showPassword: false,
      loading: false
    };
  },
  methods: {
    handleRegister() {
      if (!this.fullname || !this.email || !this.password || !this.confirmPassword) {
        this.showToast("Vui lòng điền đầy đủ thông tin!", "warning");
        return;
      }
      
      if (this.password !== this.confirmPassword) {
        this.showToast("Mật khẩu xác nhận không khớp!", "error");
        return;
      }
      
      if (!this.acceptTerms) {
        this.showToast("Bạn cần đồng ý với các điều khoản dịch vụ!", "warning");
        return;
      }

      this.loading = true;

      // Simulate API call
      setTimeout(() => {
        this.loading = false;
        this.showToast(`Đăng ký thành công tài khoản cho ${this.fullname}!`, "success");
        this.$router.push('/login');
      }, 1500);
    },
    loginWithSocial(platform) {
      this.showToast(`Tính năng liên kết qua ${platform} đang được phát triển!`, "info");
    },
    showToast(message, type = "success") {
      if (this.$toast) {
        if (type === "success") this.$toast.success(message);
        else if (type === "error" || type === "danger") this.$toast.error(message);
        else if (type === "warning") this.$toast.warning(message);
        else this.$toast.info(message);
      } else {
        alert(message);
      }
    }
  }
};
</script>

<style scoped>
@import '/style_auth/style_auth.css';
</style>
