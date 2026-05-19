<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Cài đặt hệ thống</h1>
        <p class="page-sub">
          Cấu hình thông tin cửa hàng, vận chuyển và thanh toán
        </p>
      </div>
      <button class="btn-primary" @click="saveStoreInfo">
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <path
            d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"
          />
          <polyline points="17 21 17 13 7 13" />
          <polyline points="7 3 7 8 15 8" />
        </svg>
        Lưu cài đặt
      </button>
    </div>

    <!-- Settings tabs -->
    <div class="settings-layout">
      <div class="settings-nav card card-body">
        <button
          v-for="n in navItems"
          :key="n.key"
          class="settings-nav-item"
          :class="{ active: activeTab === n.key }"
          @click="activeTab = n.key"
        >
          <span v-html="n.icon"></span>
          {{ n.label }}
        </button>
      </div>

      <div class="settings-content">
        <!-- Thông tin cửa hàng -->
        <div v-if="activeTab === 'store'" class="card card-body">
          <h2 class="section-heading">Thông tin cửa hàng</h2>
          <div class="form-grid">
            <div class="form-group">
              <label>Tên thương hiệu <span class="req">*</span></label>
              <input type="text" v-model="store.ten_thuong_hieu" />
            </div>
            <div class="form-group">
              <label>Hotline</label>
              <input type="text" v-model="store.hotline" />
            </div>
            <div class="form-group">
              <label>Email hỗ trợ</label>
              <input type="email" v-model="store.email_ho_tro" />
            </div>
            <div class="form-group">
              <label>Website</label>
              <input type="text" v-model="store.website" />
            </div>
            <div class="form-group span-2">
              <label>Địa chỉ kho hàng</label>
              <input type="text" v-model="store.dia_chi_kho" />
            </div>
            <div class="form-group span-2">
              <label>Mô tả cửa hàng</label>
              <textarea v-model="store.mo_ta" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Facebook</label>
              <input
                type="text"
                v-model="store.facebook"
                placeholder="https://facebook.com/..."
              />
            </div>
            <div class="form-group">
              <label>Instagram</label>
              <input
                type="text"
                v-model="store.instagram"
                placeholder="https://instagram.com/..."
              />
            </div>
          </div>
        </div>

        <!-- Vận chuyển -->
        <div v-if="activeTab === 'shipping'" class="card card-body">
          <h2 class="section-heading">Cấu hình vận chuyển</h2>

          <div class="shipping-list">
            <div
              class="shipping-item"
              v-for="s in shippingMethods"
              :key="s.key"
            >
              <div class="shipping-header">
                <div class="shipping-left">
                  <button
                    class="toggle"
                    :class="{ on: s.active }"
                    @click="s.active = !s.active"
                  ></button>
                  <div>
                    <p class="shipping-name">{{ s.name }}</p>
                    <p class="shipping-desc">{{ s.desc }}</p>
                  </div>
                </div>
                <button
                  class="btn-ghost"
                  style="padding: 6px 14px; font-size: 12px"
                >
                  Cấu hình
                </button>
              </div>
              <div class="shipping-config" v-if="s.active">
                <div class="form-grid" style="margin-top: 16px">
                  <div class="form-group">
                    <label>Phí ship mặc định</label>
                    <input type="number" :value="s.fee" :placeholder="s.fee" />
                  </div>
                  <div class="form-group">
                    <label>Miễn phí từ đơn</label>
                    <input
                      type="number"
                      :value="s.freeFrom"
                      placeholder="0 = không áp dụng"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Thanh toán -->
        <div v-if="activeTab === 'payment'" class="card card-body">
          <h2 class="section-heading">Phương thức thanh toán</h2>
          <div class="payment-methods-list">
            <div
              class="payment-method-item"
              v-for="pm in paymentMethods"
              :key="pm.key"
            >
              <div class="pm-header">
                <div class="pm-left">
                  <div class="pm-logo" :style="{ background: pm.bg }">
                    <span v-html="pm.icon"></span>
                  </div>
                  <div>
                    <p class="pm-name">{{ pm.name }}</p>
                    <p class="pm-desc">{{ pm.desc }}</p>
                  </div>
                </div>
                <button
                  class="toggle"
                  :class="{ on: pm.active }"
                  @click="pm.active = !pm.active"
                ></button>
              </div>
              <div class="pm-config" v-if="pm.active && pm.hasConfig">
                <div class="form-grid" style="margin-top: 14px">
                  <div class="form-group">
                    <label>Merchant ID</label>
                    <input
                      type="text"
                      :placeholder="'Nhập ' + pm.name + ' Merchant ID'"
                    />
                  </div>
                  <div class="form-group">
                    <label>Secret Key</label>
                    <input type="password" placeholder="••••••••••••" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "AdminSettings",
  data() {
    return {
      activeTab: "store",
      navItems: [
        {
          key: "store",
          label: "Thông tin cửa hàng",
          icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
        },
        {
          key: "shipping",
          label: "Vận chuyển",
          icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
        },
        {
          key: "payment",
          label: "Thanh toán",
          icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>`,
        },
      ],
      store: {
        ten_thuong_hieu: "Mô Hình BALAB",
        hotline: "1800 2097",
        email_ho_tro: "support@BALAB.vn",
        website: "https://BALAB.vn",
        dia_chi_kho: "123 Nguyễn Văn Linh, Q.7, TP.HCM",
        mo_ta: "Chuyên phân phối mô hình chính hãng, chất lượng cao.",
        facebook: "https://facebook.com/mohinhBALAB",
        instagram: "https://instagram.com/mohinhBALAB",
      },
      shippingMethods: [
        {
          key: "standard",
          name: "Giao hàng tiêu chuẩn",
          desc: "Giao trong 3-5 ngày làm việc",
          active: true,
          fee: 30000,
          freeFrom: 300000,
        },
        {
          key: "express",
          name: "Giao hàng nhanh",
          desc: "Giao trong 1-2 ngày làm việc",
          active: true,
          fee: 60000,
          freeFrom: 0,
        },
        {
          key: "same_day",
          name: "Giao trong ngày",
          desc: "Áp dụng nội thành TP.HCM",
          active: false,
          fee: 80000,
          freeFrom: 0,
        },
      ],
      paymentMethods: [
        {
          key: "cod",
          name: "COD - Thanh toán khi nhận hàng",
          desc: "Khách hàng trả tiền khi nhận được hàng",
          active: true,
          hasConfig: false,
          bg: "rgba(245,158,11,0.12)",
          icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
        },
        {
          key: "bank",
          name: "Chuyển khoản ngân hàng (QR)",
          desc: "Thanh toán qua mã QR VietQR",
          active: true,
          hasConfig: true,
          bg: "rgba(99,102,241,0.12)",
          icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>`,
        },
        {
          key: "vnpay",
          name: "VNPAY",
          desc: "Cổng thanh toán VNPAY",
          active: false,
          hasConfig: true,
          bg: "rgba(215,0,24,0.1)",
          icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>`,
        },
        {
          key: "momo",
          name: "MoMo",
          desc: "Ví điện tử MoMo",
          active: false,
          hasConfig: true,
          bg: "rgba(180,0,130,0.1)",
          icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b4007a" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v8"/></svg>`,
        },
      ],
    };
  },
  mounted() {
    this.fetchStoreInfo();
  },
  methods: {
    getConfig() {
      return {
        headers: {
          Authorization: "Bearer " + localStorage.getItem("token_admin"),
        },
      };
    },
    async fetchStoreInfo() {
      try {
        const res = await axios.get(
          "http://127.0.0.1:8000/api/thong-tin-cua-hang",
        );
        if (res.data) {
          this.store = {
            ten_thuong_hieu: res.data.ten_thuong_hieu || "",
            hotline: res.data.hotline || "",
            email_ho_tro: res.data.email_ho_tro || "",
            website: res.data.website || "",
            dia_chi_kho: res.data.dia_chi_kho || "",
            mo_ta: res.data.mo_ta || "",
            facebook: res.data.facebook || "",
            instagram: res.data.instagram || "",
          };
        }
      } catch (err) {
        console.error("Không thể lấy thông tin cửa hàng:", err);
      }
    },
    async saveStoreInfo() {
      if (!this.store.ten_thuong_hieu) {
        this.showToast("Tên thương hiệu là bắt buộc!", "error");
        return;
      }
      try {
        const res = await axios.post(
          "http://127.0.0.1:8000/api/thong-tin-cua-hang",
          this.store,
          this.getConfig(),
        );
        if (res.data.status) {
          this.showToast("Cập nhật thông tin cửa hàng thành công!", "success");
          // Phát sự kiện để cập nhật tên web hiển thị real-time nếu có
          window.dispatchEvent(
            new CustomEvent("storeInfoUpdated", { detail: this.store }),
          );
        } else {
          this.showToast(
            res.data.message || "Lỗi cập nhật thông tin!",
            "error",
          );
        }
      } catch (err) {
        const errMsg =
          err.response?.data?.message || "Có lỗi xảy ra khi lưu cài đặt!";
        this.showToast(errMsg, "error");
      }
    },
    showToast(message, type = "success") {
      if (type === "success") {
        this.$toast.success(message);
      } else if (type === "danger" || type === "error") {
        this.$toast.error(message);
      } else if (type === "warning") {
        this.$toast.warning(message);
      } else {
        this.$toast.info(message);
      }
    },
  },
};
</script>

<style scoped>
@import "/style_admin/settings.css";
</style>
