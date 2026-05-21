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
          <i class="fa-solid fa-key logo-icon"></i>
        </div>
        <h1 class="brand-name">BALAB</h1>
        <p class="brand-tagline">Đặt Lại Mật Khẩu Mới</p>
      </div>

      <!-- Reset Password Form -->
      <form
        v-if="!isSuccess"
        @submit.prevent="handleResetPassword"
        class="login-form"
      >
        <p class="role-welcome-text" style="margin-bottom: 12px;">
          Vui lòng nhập mật khẩu mới cho tài khoản: <br>
          <strong style="color: #0f172a; word-break: break-all;">{{ email }}</strong>
        </p>

        <!-- New Password Field -->
        <div class="input-group" style="margin-bottom: 12px;">
          <label for="password" class="input-label">Mật khẩu mới</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock field-icon"></i>
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="password"
              placeholder="Tối thiểu 8 ký tự"
              required
              class="glass-input"
              style="padding-right: 40px;"
            />
            <button
              type="button"
              class="eye-btn"
              @click="showPassword = !showPassword"
              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b;"
            >
              <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div class="input-group" style="margin-bottom: 16px;">
          <label for="password_confirmation" class="input-label">Xác nhận mật khẩu</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-shield-halved field-icon"></i>
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              id="password_confirmation"
              v-model="passwordConfirmation"
              placeholder="Nhập lại mật khẩu mới"
              required
              class="glass-input"
              style="padding-right: 40px;"
            />
            <button
              type="button"
              class="eye-btn"
              @click="showConfirmPassword = !showConfirmPassword"
              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b;"
            >
              <i :class="showConfirmPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="submit-btn"
          :disabled="loading"
          style="margin-top: 12px"
        >
          <span v-if="loading"
            ><i class="fa-solid fa-circle-notch fa-spin"></i> Đang cập nhật...</span
          >
          <span v-else>
            Cập Nhật Mật Khẩu
            <i class="fa-solid fa-circle-check"></i>
          </span>
        </button>
      </form>

      <!-- Success State -->
      <div
        v-else
        class="login-form"
        style="text-align: center; padding: 20px 0"
      >
        <div style="font-size: 48px; color: #10b981; margin-bottom: 16px">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2
          style="
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            font-family: 'DM Sans', sans-serif;
          "
        >
          Cập Nhật Thành Công!
        </h2>
        <p class="role-welcome-text">
          Mật khẩu của bạn đã được thay đổi. Hệ thống sẽ tự động chuyển về trang Đăng nhập sau giây lát...
        </p>
      </div>

      <!-- Card Footer -->
      <div class="card-footer" style="margin-top: 20px;">
        <p class="footer-link-text">
          Quay lại
          <router-link to="/login" class="signup-link"
            >Đăng nhập</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ResetPassword",
  data() {
    return {
      email: "",
      token: "",
      password: "",
      passwordConfirmation: "",
      loading: false,
      isSuccess: false,
      showPassword: false,
      showConfirmPassword: false,
    };
  },
  created() {
    // Get query parameters from route
    this.email = this.$route.query.email || "";
    this.token = this.$route.query.token || "";

    if (!this.token || !this.email) {
      this.showToast("Liên kết khôi phục không hợp lệ hoặc thiếu thông tin!", "error");
      this.$router.push("/login");
    }
  },
  methods: {
    async handleResetPassword() {
      if (!this.password || !this.passwordConfirmation) {
        this.showToast("Vui lòng nhập đầy đủ mật khẩu!", "warning");
        return;
      }

      if (this.password.length < 8) {
        this.showToast("Mật khẩu mới phải có tối thiểu 8 ký tự!", "warning");
        return;
      }

      if (this.password !== this.passwordConfirmation) {
        this.showToast("Mật khẩu xác nhận không trùng khớp!", "warning");
        return;
      }

      this.loading = true;

      try {
        const response = await axios.post("http://127.0.0.1:8000/api/reset-password", {
          email: this.email,
          token: this.token,
          mat_khau: this.password,
          mat_khau_confirmation: this.passwordConfirmation,
        });

        this.loading = false;
        this.isSuccess = true;
        this.showToast(response.data.message || "Đặt lại mật khẩu thành công!", "success");

        // Redirect to login after 3 seconds
        setTimeout(() => {
          this.$router.push("/login");
        }, 3000);
      } catch (error) {
        this.loading = false;
        let errMsg = "Có lỗi xảy ra, vui lòng thử lại sau.";
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errMsg = error.response.data.message;
          } else if (error.response.data.errors) {
            const firstErrorKey = Object.keys(error.response.data.errors)[0];
            errMsg = error.response.data.errors[firstErrorKey][0];
          }
        }
        this.showToast(errMsg, "error");
      }
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

.eye-btn:hover {
  color: #0f172a !important;
}
</style>
