<template>
  <div class="voucher-store-page animate-fade-in">
    <div class="voucher-store-container">
      
      <!-- Store Header Banner -->
      <div class="store-banner-card">
        <div class="banner-overlay"></div>
        <div class="banner-content">
          <span class="banner-badge">🏷️ KHUYẾN MÃI ĐẶC BIỆT</span>
          <h1 class="banner-title">Kho Voucher BALAB</h1>
          <p class="banner-desc">Thu thập các mã giảm giá hấp dẫn để nhận ưu đãi cực hời khi mua sắm mô hình!</p>
        </div>
      </div>

      <!-- Main Content -->
      <div class="store-main-wrap">
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <i class="fa-solid fa-spinner fa-spin spinner-icon"></i>
          <span>Đang tìm kiếm voucher khả dụng...</span>
        </div>

        <!-- Empty State -->
        <div v-else-if="vouchers.length === 0" class="empty-state">
          <span class="empty-emoji">🎟️</span>
          <h3>Hiện tại không có voucher nào khả dụng</h3>
          <p>Hãy quay lại sau để săn các chương trình khuyến mãi mới nhé!</p>
          <router-link to="/san-pham" class="btn-shop-now">Khám phá sản phẩm</router-link>
        </div>

        <!-- Voucher Grid -->
        <div v-else class="voucher-grid-new">
          <div 
            v-for="v in vouchers" 
            :key="v.id" 
            class="voucher-ticket-card"
            :class="{ 'already-claimed': v.is_claimed }"
          >
            <!-- Ticket Left side: Icon and Discount Value -->
            <div class="ticket-left" :style="{ background: getVoucherGradient(v) }">
              <span class="ticket-badge-top">{{ v.distributeMethod === 'claimable' ? 'Thu thập' : 'Ưu đãi' }}</span>
              <div class="discount-value">
                <span class="num">{{ v.value }}</span>
                <span class="unit">{{ v.type === 'Phần trăm' ? '%' : 'đ' }}</span>
              </div>
              <span class="ticket-type-label">{{ v.type === 'Phần trăm' ? 'Giảm Giá' : 'Tiền Mặt' }}</span>
              <!-- Punch hole top & bottom -->
              <div class="punch-hole top"></div>
              <div class="punch-hole bottom"></div>
            </div>

            <!-- Ticket Right side: Voucher Conditions & Claim Button -->
            <div class="ticket-right">
              <div class="ticket-details">
                <div class="voucher-code-row">
                  <code class="sku-code">{{ v.code }}</code>
                  <span v-if="v.is_claimed" class="status-claimed-badge">✓ Đã trong ví</span>
                </div>
                <h3 class="voucher-title">
                  {{ v.type === 'Phần trăm' ? `Mã giảm ${v.value}%` : `Giảm ngay ${formatPrice(v.value)}` }}
                </h3>
                <ul class="voucher-conditions">
                  <li>
                    <i class="fa-solid fa-circle-check info-icon"></i>
                    Đơn tối thiểu: <strong>{{ formatPrice(v.minOrder) }}</strong>
                  </li>
                  <li v-if="v.maxDiscount">
                    <i class="fa-solid fa-circle-check info-icon"></i>
                    Giảm tối đa: <strong>{{ formatPrice(v.maxDiscount) }}</strong>
                  </li>
                  <li v-if="v.expiry">
                    <i class="fa-solid fa-circle-exclamation info-icon expiry-icon"></i>
                    Hạn sử dụng: <strong>{{ formatDate(v.expiry) }}</strong>
                  </li>
                </ul>
              </div>

              <!-- Action Button -->
              <div class="ticket-action-footer">
                <button 
                  v-if="!isLoggedIn" 
                  class="btn-claim login-to-claim"
                  @click="redirectToLogin"
                >
                  Đăng nhập để lấy
                </button>
                <button 
                  v-else-if="v.is_claimed" 
                  class="btn-claim claimed"
                  disabled
                >
                  <i class="fa-solid fa-wallet"></i> Đã thu thập
                </button>
                <button 
                  v-else 
                  class="btn-claim active-claim"
                  :disabled="claimingId === v.id"
                  @click="claimVoucher(v)"
                >
                  <i v-if="claimingId === v.id" class="fa-solid fa-spinner fa-spin"></i>
                  <span v-else>Thu thập ví</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'VoucherStore',
  data() {
    return {
      loading: true,
      isLoggedIn: false,
      vouchers: [],
      claimingId: null,
    };
  },
  created() {
    this.isLoggedIn = !!localStorage.getItem('token_client');
    this.fetchClaimableVouchers();
  },
  methods: {
    getToken() {
      return localStorage.getItem('token_client');
    },
    async fetchClaimableVouchers() {
      this.loading = true;
      try {
        const token = this.getToken();
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        const res = await axios.get('/api/ma-giam-gia/claimable', config);
        if (res.data.status) {
          this.vouchers = res.data.data;
        }
      } catch (error) {
        console.error('Lỗi khi tải kho voucher:', error);
        this.showToast('Không thể tải danh sách voucher!', 'error');
      } finally {
        this.loading = false;
      }
    },
    async claimVoucher(v) {
      if (!this.isLoggedIn) {
        this.redirectToLogin();
        return;
      }
      this.claimingId = v.id;
      try {
        const token = this.getToken();
        const res = await axios.post(`/api/khach-hang/ma-giam-gia/claim/${v.code}`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (res.data.status) {
          this.showToast('Thu thập voucher thành công! Đã lưu vào ví.', 'success');
          v.is_claimed = true;
        } else {
          this.showToast(res.data.message || 'Lỗi khi thu thập voucher!', 'error');
        }
      } catch (error) {
        console.error('Lỗi khi claim voucher:', error);
        const errMsg = error.response?.data?.message || 'Có lỗi xảy ra!';
        this.showToast(errMsg, 'error');
      } finally {
        this.claimingId = null;
      }
    },
    redirectToLogin() {
      this.showToast('Vui lòng đăng nhập trước khi thu thập voucher!', 'warning');
      this.$router.push('/login');
    },
    getVoucherGradient(v) {
      if (v.is_claimed) {
        return 'linear-gradient(135deg, #94a3b8, #64748b)';
      }
      if (v.type === 'Phần trăm') {
        return 'linear-gradient(135deg, hsl(351, 100%, 55%), hsl(265, 89%, 60%))';
      }
      return 'linear-gradient(135deg, #10b981, #059669)';
    },
    formatPrice(val) {
      if (val === null || val === undefined) return '0đ';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split('-');
      return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
    },
    showToast(message, type = 'success') {
      if (this.$toast) {
        this.$toast[type](message);
      } else {
        alert(message);
      }
    }
  }
};
</script>

<style scoped>
.voucher-store-page {
  background: #f8fafc;
  min-height: 85vh;
  padding: 40px 20px;
}

.voucher-store-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Banner Styling */
.store-banner-card {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  padding: 50px;
  margin-bottom: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.banner-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.15) 0%, transparent 60%);
  z-index: 1;
}

.banner-content {
  position: relative;
  z-index: 2;
  max-width: 600px;
}

.banner-badge {
  display: inline-block;
  background: rgba(255, 215, 0, 0.15);
  color: #ffd700;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 16px;
  letter-spacing: 0.5px;
}

.banner-title {
  color: #fff;
  font-size: 34px;
  font-weight: 800;
  margin-bottom: 12px;
  letter-spacing: -0.5px;
}

.banner-desc {
  color: #94a3b8;
  font-size: 15.5px;
  line-height: 1.6;
  margin: 0;
}

/* Main Loading/Empty States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  color: #64748b;
  gap: 14px;
  font-size: 15px;
}

.spinner-icon {
  font-size: 32px;
  color: #e30019;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
}

.empty-emoji {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.empty-state p {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 24px;
}

.btn-shop-now {
  display: inline-block;
  background: #e30019;
  color: #fff;
  font-weight: 700;
  padding: 10px 24px;
  border-radius: 8px;
  text-decoration: none;
  transition: transform 0.2s, background-color 0.2s;
}

.btn-shop-now:hover {
  background: #c20014;
  transform: translateY(-2px);
}

/* Voucher Grid Styling */
.voucher-grid-new {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
}

.voucher-ticket-card {
  display: flex;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  transition: transform 0.25s, box-shadow 0.25s;
  height: 180px;
}

.voucher-ticket-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

/* Ticket Left Portion */
.ticket-left {
  position: relative;
  width: 120px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  padding: 15px;
  flex-shrink: 0;
  text-align: center;
}

.ticket-badge-top {
  position: absolute;
  top: 8px;
  font-size: 9px;
  font-weight: 700;
  background: rgba(255,255,255,0.2);
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
}

.discount-value {
  display: flex;
  align-items: baseline;
  justify-content: center;
  margin-bottom: 4px;
}

.discount-value .num {
  font-size: 34px;
  font-weight: 900;
  line-height: 1;
}

.discount-value .unit {
  font-size: 16px;
  font-weight: 700;
  margin-left: 2px;
}

.ticket-type-label {
  font-size: 11px;
  font-weight: 600;
  opacity: 0.9;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Scalloped edges (holes) */
.punch-hole {
  position: absolute;
  width: 14px;
  height: 14px;
  background: #f8fafc; /* match parent background */
  border-radius: 50%;
  right: -7px;
  z-index: 10;
}

.punch-hole.top {
  top: -7px;
}

.punch-hole.bottom {
  bottom: -7px;
}

/* Ticket Right Portion */
.ticket-right {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 16px 20px;
  flex: 1;
  border-left: 1px dashed #e2e8f0;
}

.ticket-details {
  display: flex;
  flex-direction: column;
}

.voucher-code-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}

.sku-code {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
  font-family: monospace;
}

.status-claimed-badge {
  font-size: 11px;
  color: #10b981;
  font-weight: 700;
}

.voucher-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 8px 0;
  line-height: 1.3;
}

.voucher-conditions {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.voucher-conditions li {
  font-size: 12px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.info-icon {
  font-size: 10px;
  color: #10b981;
}

.info-icon.expiry-icon {
  color: #f59e0b;
}

.ticket-action-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}

/* Claim Buttons */
.btn-claim {
  border: none;
  font-size: 12.5px;
  font-weight: 700;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.active-claim {
  background: #e30019;
  color: #fff;
  box-shadow: 0 4px 12px rgba(227, 0, 25, 0.15);
}

.active-claim:hover {
  background: #c20014;
  transform: translateY(-1px);
}

.claimed {
  background: #f1f5f9;
  color: #94a3b8;
  cursor: not-allowed;
}

.login-to-claim {
  background: #e2e8f0;
  color: #475569;
}

.login-to-claim:hover {
  background: #cbd5e1;
}

/* claimed ticket styling */
.already-claimed {
  border-color: #cbd5e1;
  opacity: 0.95;
}

.already-claimed:hover {
  transform: none;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
}

/* Fade animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@media (max-width: 768px) {
  .voucher-grid-new {
    grid-template-columns: 1fr;
  }
}
</style>
