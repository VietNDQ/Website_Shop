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

      <!-- Forgot Password Form -->
      <form
        v-if="!isSent"
        @submit.prevent="handleForgotPassword"
        class="login-form"
      >
        <!-- Welcoming text -->
        <p class="role-welcome-text">
          Đừng lo lắng! Nhập email của bạn và chúng tôi sẽ gửi liên kết khôi
          phục mật khẩu.
        </p>

        <!-- Email Field -->
        <div class="input-group">
          <label for="email" class="input-label">Địa chỉ Email</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-envelope field-icon"></i>
            <input
              type="email"
              id="email"
              v-model="email"
              placeholder="Nhập email@gmail.com"
              required
              class="glass-input"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="submit-btn"
          :disabled="loading"
          style="margin-top: 24px"
        >
          <span v-if="loading"
            ><i class="fa-solid fa-circle-notch fa-spin"></i> Đang gửi...</span
          >
          <span v-else>
            Gửi Liên Kết Khôi Phục
            <i class="fa-solid fa-paper-plane"></i>
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
          <i class="fa-solid fa-envelope-circle-check"></i>
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
          Đã Gửi Thành Công!
        </h2>
        <p class="role-welcome-text">
          Chúng tôi đã gửi một liên kết khôi phục mật khẩu tới địa chỉ
          <strong>{{ email }}</strong
          >. Vui lòng kiểm tra hộp thư đến của bạn.
        </p>
      </div>

      <!-- Card Footer -->
      <div class="card-footer">
        <p class="footer-link-text">
          Nhớ mật khẩu rồi?
          <router-link to="/login" class="signup-link"
            >Quay lại đăng nhập</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ForgotPassword",
  data() {
    return {
      email: "",
      loading: false,
      isSent: false,
    };
  },
  methods: {
    async handleForgotPassword() {
      if (!this.email) {
        this.showToast("Vui lòng nhập địa chỉ email của bạn!", "warning");
        return;
      }

      this.loading = true;

      try {
        const response = await axios.post("/api/forgot-password", {
          email: this.email,
        });

        this.loading = false;
        this.isSent = true;
        this.showToast(response.data.message || "Email khôi phục đã được gửi thành công!", "success");
      } catch (error) {
        this.loading = false;
        let errMsg = "Có lỗi xảy ra, vui lòng thử lại sau.";
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errMsg = error.response.data.message;
          } else if (error.response.data.errors && error.response.data.errors.email) {
            errMsg = error.response.data.errors.email[0];
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
</style>
