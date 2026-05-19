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
        <h1 class="brand-name">BALAB</h1>
        <p class="brand-tagline">Premium Models & Collectibles</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <!-- Welcoming text -->
        <p class="role-welcome-text">
          Đăng nhập để khám phá thế giới mô hình cao cấp của bạn!
        </p>

        <!-- Email Field -->
        <div class="input-group">
          <label for="email" class="input-label">Tài khoản / Email</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-envelope field-icon"></i>
            <input
              type="text"
              id="email"
              v-model="email"
              placeholder="Nhập Email tại đây"
              required
              class="glass-input"
            />
          </div>
        </div>

        <!-- Password Field -->
        <div class="input-group">
          <div class="password-label-row">
            <label for="password" class="input-label">Mật khẩu</label>
            <router-link to="/forgot-password" class="forgot-link"
              >Quên mật khẩu?</router-link
            >
          </div>
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
              <i
                class="fa-regular"
                :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
              ></i>
            </button>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="remember-row">
          <label class="remember-label">
            <input
              type="checkbox"
              v-model="rememberMe"
              class="glass-checkbox"
            />
            <span class="custom-checkbox"></span>
            Ghi nhớ tài khoản
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading"
            ><i class="fa-solid fa-circle-notch fa-spin"></i> Đang xử
            lý...</span
          >
          <span v-else>
            Đăng Nhập
            <i class="fa-solid fa-arrow-right"></i>
          </span>
        </button>
      </form>

      <!-- Social Logins -->
      <div class="social-login-section">
        <div class="divider">
          <span class="divider-text">Hoặc đăng nhập bằng</span>
        </div>
        <div class="social-buttons">
          <button
            @click="loginWithSocial('Google')"
            class="social-btn google-btn"
            type="button"
          >
            <i class="fa-brands fa-google social-icon"></i> Google
          </button>
          <button
            @click="loginWithSocial('Facebook')"
            class="social-btn facebook-btn"
            type="button"
          >
            <i class="fa-brands fa-facebook-f social-icon"></i> Facebook
          </button>
        </div>
      </div>

      <!-- Card Footer -->
      <div class="card-footer">
        <p class="footer-link-text">
          Chưa có tài khoản?
          <router-link to="/register" class="signup-link"
            >Đăng ký ngay</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Login",
  data() {
    return {
      email: "",
      password: "",
      rememberMe: false,
      showPassword: false,
      loading: false,
    };
  },
  methods: {
    async handleLogin() {
      if (!this.email || !this.password) {
        this.showToast("Vui lòng nhập đầy đủ thông tin đăng nhập!", "warning");
        return;
      }

      this.loading = true;

      try {
        const response = await axios.post(
          "http://127.0.0.1:8000/api/dang-nhap",
          {
            email: this.email,
            mat_khau: this.password, // Backend expects mat_khau
          },
        );

        const user = response.data.user;
        const token = response.data.token;

        // Phân quyền theo vai_tro (1: quản trị, 2: quản lý, 3: khách hàng)
        if (user.vai_tro === 1 || user.vai_tro === 2) {
          // Lưu token cho admin/nhân viên
          localStorage.setItem("token_admin", token);
          this.showToast(
            "Đăng nhập Hệ thống Quản trị viên thành công!",
            "success",
          );
          this.$router.push("/nhan-vien/dashboard");
        } else {
          // Lưu token cho khách hàng
          localStorage.setItem("token_client", token);
          this.showToast(`Chào mừng quay trở lại, ${user.ho_ten}!`, "success");
          this.$router.push("/");
        }
      } catch (error) {
        let errorMessage = "Đăng nhập thất bại. Vui lòng thử lại!";
        if (error.response && error.response.data) {
          if (error.response.data.errors && error.response.data.errors.email) {
            errorMessage = error.response.data.errors.email[0];
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }
        this.showToast(errorMessage, "error");
      } finally {
        this.loading = false;
      }
    },
    loginWithSocial(platform) {
      this.showToast(
        `Kết nối đăng nhập qua ${platform} thành công!`,
        "success",
      );
    },
    showToast(message, type = "success") {
      if (this.$toast) {
        if (type === "success") this.$toast.success(message);
        else if (type === "error" || type === "danger")
          this.$toast.error(message);
        else if (type === "warning") this.$toast.warning(message);
        else this.$toast.info(message);
      } else {
        alert(message);
      }
    },
  },
};
</script>

<style scoped>
@import "/style_auth/style_auth.css";
</style>
