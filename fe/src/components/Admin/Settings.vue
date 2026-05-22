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
                <!-- Cấu hình cho Chuyển khoản ngân hàng -->
                <div v-if="pm.key === 'bank'" class="bank-accounts-section" style="margin-top: 14px;">
                  
                  <!-- Nút thêm tài khoản và tiêu đề -->
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 13px; font-weight: 700; color: #475569;">Danh sách tài khoản ngân hàng nhận tiền:</span>
                    <button 
                      v-if="!showAddForm"
                      type="button" 
                      class="btn-primary" 
                      style="padding: 6px 12px; font-size: 12px; height: 32px;"
                      @click="showAddForm = true"
                    >
                      Thêm số tài khoản
                    </button>
                  </div>

                  <!-- Form thêm tài khoản ngân hàng -->
                  <div v-if="showAddForm" class="card" style="border: 1px solid #cbd5e0; padding: 16px; border-radius: 8px; margin-bottom: 16px; background: #fff; box-shadow: none;">
                    <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Thêm tài khoản ngân hàng mới</div>
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 12px;">
                      <div class="form-group">
                        <label>Ngân hàng <span class="req">*</span></label>
                        <select v-model="newAccount.bank_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 8px; font-weight: 600; outline: none; background: #fff;">
                          <option value="">-- Chọn ngân hàng --</option>
                          <option v-for="b in banks" :key="b.id" :value="b.id">
                            {{ b.name }}
                          </option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Số tài khoản <span class="req">*</span></label>
                        <input
                          type="text"
                          v-model="newAccount.bank_account_no"
                          placeholder="Nhập số tài khoản ngân hàng"
                        />
                      </div>
                      <div class="form-group span-2">
                        <label>Tên chủ tài khoản <span class="req">*</span></label>
                        <input
                          type="text"
                          v-model="newAccount.bank_account_name"
                          placeholder="Nhập tên chủ tài khoản (viết hoa không dấu)"
                        />
                      </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                      <button type="button" class="btn-ghost" style="padding: 6px 16px; border-radius: 8px;" @click="cancelAddAccount">Hủy</button>
                      <button type="button" class="btn-primary" style="padding: 6px 16px; border-radius: 8px;" @click="addBankAccount">Lưu tài khoản</button>
                    </div>
                  </div>

                  <!-- Danh sách tài khoản ngân hàng -->
                  <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                      <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700;">
                          <th style="padding: 12px 16px;">Ngân hàng</th>
                          <th style="padding: 12px 16px;">Số tài khoản</th>
                          <th style="padding: 12px 16px;">Chủ tài khoản</th>
                          <th style="padding: 12px 16px; text-align: center;">Trạng thái</th>
                          <th style="padding: 12px 16px; text-align: right;">Hành động</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="bankAccounts.length === 0">
                          <td colspan="5" style="padding: 20px; text-align: center; color: #64748b;">
                            Chưa có tài khoản ngân hàng nào. Vui lòng bấm "Thêm số tài khoản" để cấu hình.
                          </td>
                        </tr>
                        <tr 
                          v-for="acc in bankAccounts" 
                          :key="acc.id" 
                          style="border-bottom: 1px solid #f1f5f9; color: #0f172a; font-weight: 500;"
                          :style="{ background: acc.is_active ? '#f0fdf4' : 'transparent' }"
                        >
                          <td style="padding: 12px 16px; text-transform: uppercase; font-weight: 700; color: #1e3a8a;">
                            {{ acc.bank_id }}
                          </td>
                          <td style="padding: 12px 16px; font-family: monospace; font-size: 14px; font-weight: 600;">
                            {{ acc.bank_account_no }}
                          </td>
                          <td style="padding: 12px 16px; text-transform: uppercase; font-weight: 600;">
                            {{ acc.bank_account_name }}
                          </td>
                          <td style="padding: 12px 16px; text-align: center;">
                            <span 
                              v-if="acc.is_active" 
                              style="display: inline-block; background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; border: 1px solid #bbf7d0;"
                            >
                              Đang sử dụng
                            </span>
                            <span 
                              v-else 
                              style="display: inline-block; background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; border: 1px solid #fef08a;"
                            >
                              Không sử dụng
                            </span>
                          </td>
                          <td style="padding: 12px 16px; text-align: right; display: flex; gap: 8px; justify-content: flex-end; align-items: center; min-height: 48px;">
                            <button 
                              v-if="!acc.is_active"
                              type="button"
                              class="btn-primary"
                              style="padding: 4px 10px; font-size: 11px; height: 26px; background: #3b82f6; border-color: #3b82f6; border-radius: 6px;"
                              @click="activateBankAccount(acc.id)"
                            >
                              Sử dụng
                            </button>
                            <button
                              v-if="bankAccounts.length > 1 || !acc.is_active"
                              type="button"
                              style="padding: 4px 8px; font-size: 11px; height: 26px; background: #ef4444; border: 1px solid #ef4444; color: #fff; border-radius: 6px; cursor: pointer; font-weight: 600;"
                              @click="deleteBankAccount(acc.id)"
                            >
                              Xóa
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Cấu hình cho các phương thức khác (VNPAY, MoMo) -->
                <div v-else class="form-grid" style="margin-top: 14px">
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
        ten_thuong_hieu: "Cửa Hàng BALAB",
        hotline: "1800 2097",
        email_ho_tro: "support@BALAB.vn",
        website: "https://BALAB.vn",
        dia_chi_kho: "123 Nguyễn Văn Linh, Q.7, TP.HCM",
        mo_ta: "Chuyên phân phối mô hình chính hãng, chất lượng cao.",
        facebook: "https://facebook.com/ShopBALAB",
        instagram: "https://instagram.com/ShopBALAB",
      },
      bankAccounts: [],
      showAddForm: false,
      newAccount: {
        bank_id: "",
        bank_account_no: "",
        bank_account_name: "",
      },
      banks: [
        { id: "vietinbank", name: "VietinBank (ICB)" },
        { id: "vietcombank", name: "Vietcombank (VCB)" },
        { id: "techcombank", name: "Techcombank (TCB)" },
        { id: "bidv", name: "BIDV" },
        { id: "agribank", name: "Agribank (VBA)" },
        { id: "mbbank", name: "MBBank (MB)" },
        { id: "acb", name: "ACB" },
        { id: "vpbank", name: "VPBank (VPB)" },
        { id: "tpbank", name: "TPBank (TPB)" },
        { id: "sacombank", name: "Sacombank (STB)" },
        { id: "vib", name: "VIB" },
        { id: "shb", name: "SHB" },
        { id: "hdbank", name: "HDBank" },
        { id: "msb", name: "MSB" },
        { id: "ocb", name: "OCB" },
      ],
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
    this.fetchBankAccounts();
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
          "/api/thong-tin-cua-hang",
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
    async fetchBankAccounts() {
      try {
        const res = await axios.get(
          "/api/quan-ly/tai-khoan-ngan-hang",
          this.getConfig()
        );
        this.bankAccounts = res.data;
      } catch (err) {
        console.error("Không thể lấy danh sách tài khoản ngân hàng:", err);
      }
    },
    async addBankAccount() {
      if (!this.newAccount.bank_id || !this.newAccount.bank_account_no || !this.newAccount.bank_account_name) {
        this.showToast("Vui lòng điền đầy đủ các thông tin bắt buộc!", "error");
        return;
      }
      try {
        const res = await axios.post(
          "/api/quan-ly/tai-khoan-ngan-hang",
          this.newAccount,
          this.getConfig()
        );
        this.showToast(res.data.message || "Thêm tài khoản thành công!", "success");
        this.newAccount = { bank_id: "", bank_account_no: "", bank_account_name: "" };
        this.showAddForm = false;
        await this.fetchBankAccounts();
      } catch (err) {
        const errMsg = err.response?.data?.message || "Có lỗi xảy ra khi lưu tài khoản ngân hàng!";
        this.showToast(errMsg, "error");
      }
    },
    async activateBankAccount(id) {
      try {
        const res = await axios.post(
          `/api/quan-ly/tai-khoan-ngan-hang/${id}/kich-hoat`,
          {},
          this.getConfig()
        );
        this.showToast(res.data.message || "Kích hoạt tài khoản thành công!", "success");
        await this.fetchBankAccounts();
      } catch (err) {
        const errMsg = err.response?.data?.message || "Không thể kích hoạt tài khoản!";
        this.showToast(errMsg, "error");
      }
    },
    async deleteBankAccount(id) {
      if (!confirm("Bạn có chắc chắn muốn xóa tài khoản ngân hàng này?")) {
        return;
      }
      try {
        const res = await axios.delete(
          `/api/quan-ly/tai-khoan-ngan-hang/${id}`,
          this.getConfig()
        );
        this.showToast(res.data.message || "Xóa tài khoản thành công!", "success");
        await this.fetchBankAccounts();
      } catch (err) {
        const errMsg = err.response?.data?.message || "Không thể xóa tài khoản!";
        this.showToast(errMsg, "error");
      }
    },
    cancelAddAccount() {
      this.newAccount = { bank_id: "", bank_account_no: "", bank_account_name: "" };
      this.showAddForm = false;
    },
    async saveStoreInfo() {
      if (!this.store.ten_thuong_hieu) {
        this.showToast("Tên thương hiệu là bắt buộc!", "error");
        return;
      }
      try {
        const res = await axios.post(
          "/api/thong-tin-cua-hang",
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
@import "../../../public/style_admin/settings.css";
</style>
