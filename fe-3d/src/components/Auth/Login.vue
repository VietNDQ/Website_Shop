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
            <input type="text" id="email" v-model="email" placeholder="Nhập Email tại đây" required
              class="glass-input" />
          </div>
        </div>

        <!-- Password Field -->
        <div class="input-group">
          <div class="password-label-row">
            <label for="password" class="input-label">Mật khẩu</label>
            <router-link to="/forgot-password" class="forgot-link">Quên mật khẩu?</router-link>
          </div>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock field-icon"></i>
            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="password" placeholder="••••••••"
              required class="glass-input" />
            <button type="button" @click="showPassword = !showPassword" class="eye-btn">
              <i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" v-model="rememberMe" class="glass-checkbox" />
            <span class="custom-checkbox"></span>
            Ghi nhớ tài khoản
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang xử
            lý...</span>
          <span v-else>
            Đăng Nhập
            <i class="fa-solid fa-arrow-right"></i>
          </span>
        </button>
      </form>

      <!-- Social Logins -->
      <div class="social-login-section google-login-wrapper">
        <GoogleLogin :callback="LoginGoogle" :button-config="{ theme: 'outline', size: 'large', width: googleButtonWidth }" />
      </div>

      <!-- Card Footer -->
      <div class="card-footer">
        <p class="footer-link-text">
          Chưa có tài khoản?
          <router-link to="/register" class="signup-link">Đăng ký ngay</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../store/cartStore";
import { useAuthStore } from "../../store/authStore";
import { useWishlistStore } from "../../store/wishlistStore";

export default {
  name: "Login",
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      email: "",
      password: "",
      rememberMe: false,
      showPassword: false,
      loading: false,
      googleButtonWidth: 376,
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
          "/api/dang-nhap",
          {
            email: this.email,
            mat_khau: this.password, // Backend expects mat_khau
          },
        );

        const user = response.data.user;
        const token = response.data.token;

        // Phân quyền theo vai_tro (1: Super Admin, 2: Quản lý, 4: Nhân viên kho, 5: Nhân viên bán hàng)
        if ([1, 2, 4, 5].includes(user.vai_tro)) {
          // Lưu data admin qua Pinia store
          this.authStore.setAdminData(user, token);
          this.showToast(
            "Đăng nhập Hệ thống Quản trị viên thành công!",
            "success",
          );
          
          // Chuyển hướng theo vai trò
          if (user.vai_tro === 4) {
            this.$router.push("/nhan-vien/products");
          } else if (user.vai_tro === 5) {
            this.$router.push("/nhan-vien/orders");
          } else {
            this.$router.push("/nhan-vien/dashboard");
          }
        } else {
          // Lưu token cho khách hàng
          localStorage.setItem("token_client", token);
          localStorage.setItem("ho_ten_client", user.ho_ten);

          // Đồng bộ giỏ hàng với máy chủ
          const cartStore = useCartStore();
          await cartStore.syncCartWithBackend();

          // Đồng bộ sản phẩm yêu thích & đã xem
          const wishlistStore = useWishlistStore();
          await wishlistStore.syncWithBackend();

          window.dispatchEvent(new Event("clientLoginUpdated"));
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
    LoginGoogle(response) {
      console.log("Google Login Raw Response:", response);

      let idToken = null;
      if (response) {
        if (typeof response === "string") {
          idToken = response;
        } else if (response.credential) {
          idToken = response.credential;
        } else if (response.id_token) {
          idToken = response.id_token;
        } else if (response.tokenId) {
          idToken = response.tokenId;
        }
      }

      if (!idToken) {
        console.error("Failed to extract Google ID token from response:", response);
        this.showToast("Không tìm thấy ID Token từ Google. Vui lòng kiểm tra Console log!", "error");
        return;
      }

      const payload = {
        'id_token': idToken,
      };
      axios
        .post("/api/khach-hang/login-google", payload)
        .then((res) => {
          if (res.data.status) {
            const token = res.data.token;
            const vai_tro = res.data.vai_tro;
            const ho_ten = res.data.ho_ten;
            const user_id = res.data.id;

            if ([1, 2, 4, 5].includes(vai_tro)) {
              const userObj = {
                id: user_id,
                ho_ten: ho_ten,
                email: res.data.email || "",
                vai_tro: vai_tro,
                anh_dai_dien: res.data.anh_dai_dien || ""
              };
              this.authStore.setAdminData(userObj, token);
              localStorage.removeItem("token_client");
              localStorage.removeItem("ho_ten_client");

              this.showToast("Đăng nhập Hệ thống Quản trị viên thành công!", "success");
              
              if (vai_tro === 4) {
                this.$router.push("/nhan-vien/products");
              } else if (vai_tro === 5) {
                this.$router.push("/nhan-vien/orders");
              } else {
                this.$router.push("/nhan-vien/dashboard");
              }
            } else {
              // Lưu token cho khách hàng
              localStorage.setItem("token_client", token);
              localStorage.setItem("ho_ten_client", ho_ten);
              localStorage.removeItem("token_admin");

              // Đồng bộ giỏ hàng & sản phẩm yêu thích với máy chủ
              const cartStore = useCartStore();
              const wishlistStore = useWishlistStore();
              Promise.all([
                cartStore.syncCartWithBackend(),
                wishlistStore.syncWithBackend()
              ]).then(() => {
                window.dispatchEvent(new Event("clientLoginUpdated"));
              });

              this.showToast(`Chào mừng quay trở lại, ${ho_ten}!`, "success");
              this.$router.push("/");
            }
          } else {
            this.showToast(res.data.message, "error");
          }
        })
        .catch((err) => {
          let errorMessage = "Đã có lỗi xảy ra, vui lòng thử lại.";
          if (err.response && err.response.data) {
            if (err.response.data.errors) {
              errorMessage = Object.values(err.response.data.errors).map(v => v[0]).join("\n");
            } else if (err.response.data.message) {
              errorMessage = err.response.data.message;
            }
          }
          this.showToast(errorMessage, "error");
        });
    },
    async kiemTraDangNhap() {
      // 1. Kiểm tra token admin
      const adminToken = localStorage.getItem("token_admin");
      if (adminToken) {
        try {
          const res = await axios.get("/api/check-token", {
            headers: {
              Authorization: "Bearer " + adminToken
            }
          });
          if (res.data.status && [1, 2, 4, 5].includes(res.data.vai_tro)) {
            const userObj = {
              id: res.data.id,
              ho_ten: res.data.ho_ten,
              email: res.data.email,
              vai_tro: res.data.vai_tro,
              anh_dai_dien: res.data.anh_dai_dien || ''
            };
            this.authStore.setAdminData(userObj, adminToken);

            if (res.data.vai_tro === 4) {
              this.$router.push("/nhan-vien/products");
            } else if (res.data.vai_tro === 5) {
              this.$router.push("/nhan-vien/orders");
            } else {
              this.$router.push("/nhan-vien/dashboard");
            }
            return;
          }
        } catch (error) {
          this.authStore.clearAdminData();
        }
      }

      // 2. Kiểm tra token khách hàng
      const customerToken = localStorage.getItem("token_client");
      if (customerToken) {
        try {
          const res = await axios.get("/api/check-token", {
            headers: {
              Authorization: 'Bearer ' + customerToken
            }
          });
          if (res.data.status) {
            this.$router.push("/");
            return;
          }
        } catch (error) {
          localStorage.removeItem("token_client");
          localStorage.removeItem("ho_ten_client");
        }
      }
    },
    updateGoogleButtonWidth() {
      const card = document.querySelector(".glass-login-card");
      if (card) {
        const style = window.getComputedStyle(card);
        const paddingLeft = parseFloat(style.paddingLeft);
        const paddingRight = parseFloat(style.paddingRight);
        const cardWidth = card.clientWidth;
        this.googleButtonWidth = Math.min(400, Math.max(200, cardWidth - paddingLeft - paddingRight));
      } else {
        this.googleButtonWidth = window.innerWidth < 480 ? 320 : 376;
      }
    }
  },
  mounted() {
    this.kiemTraDangNhap();
    this.updateGoogleButtonWidth();
    window.addEventListener("resize", this.updateGoogleButtonWidth);
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.updateGoogleButtonWidth);
  },
};
</script>

<style scoped>
@import "/style_auth/style_auth.css";
</style>
