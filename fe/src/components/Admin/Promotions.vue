<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Khuyến mãi & Marketing</h1>
        <p class="page-sub">Quản lý mã giảm giá và chương trình Flash Sale</p>
      </div>
      
      <!-- Dynamic header button based on active tab -->
      <button v-if="tab === 'coupon'" class="btn-primary" @click="openCouponModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tạo mã mới
      </button>
      <button v-else-if="tab === 'flash'" class="btn-primary" @click="openFlashModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tạo Flash Sale mới
      </button>
    </div>

    <!-- Tabs -->
    <div class="cus-tabs">
      <button class="cus-tab" :class="{ active: tab === 'coupon' }" @click="tab = 'coupon'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        Mã giảm giá
      </button>
      <button class="cus-tab" :class="{ active: tab === 'flash' }" @click="tab = 'flash'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Flash Sale
      </button>
      <button class="cus-tab" :class="{ active: tab === 'history' }" @click="switchToHistory">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Lịch sử
      </button>
    </div>

    <!-- Coupon tab -->
    <div v-if="tab === 'coupon'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" v-model="couponSearch" placeholder="Tìm mã voucher..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="couponFilterStatus">
            <option value="">Trạng thái</option>
            <option value="active">Đang hoạt động</option>
            <option value="expired">Hết hạn</option>
            <option value="inactive">Tắt</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Mã voucher</th>
            <th>Loại giảm</th>
            <th>Giá trị</th>
            <th>Đơn tối thiểu</th>
            <th>Đã dùng / Giới hạn</th>
            <th>Hết hạn</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredCoupons" :key="c.code">
            <td><code class="sku">{{ c.code }}</code></td>
            <td>{{ c.type }}</td>
            <td class="price-red">{{ c.type === 'Phần trăm' ? c.value + '%' : formatPrice(c.value) }}</td>
            <td>{{ formatPrice(c.minOrder) }}</td>
            <td>
              <div class="usage-wrap">
                <span>{{ c.used }} / {{ c.limit }}</span>
                <div class="usage-bar"><div class="usage-fill" :style="{ width: Math.min((c.used / c.limit * 100), 100) + '%' }"></div></div>
              </div>
            </td>
            <td>{{ formatDate(c.expiry) }}</td>
            <td>
              <span class="status-pill" :class="isCouponExpired(c) ? 's-cancelled' : (c.active ? 's-delivered' : 's-cancelled')">
                {{ isCouponExpired(c) ? 'Hết hạn' : (c.active ? 'Hoạt động' : 'Tắt') }}
              </span>
            </td>
            <td>
              <div class="action-btns">
                <button class="act-btn edit" @click="openCouponModal('edit', c)" title="Sửa & Chi tiết"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="act-btn del" @click="confirmDeleteCoupon(c)" title="Xoá"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg></button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredCoupons.length === 0">
            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 30px;">Không tìm thấy mã giảm giá nào phù hợp.</td>
          </tr>
        </tbody>
      </table>
      <div class="table-footer">
        <span class="table-count">{{ filteredCoupons.length }} mã giảm giá</span>
      </div>
    </div>

    <!-- History tab -->
    <div v-if="tab === 'history'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" v-model="historySearch" placeholder="Tìm theo tên thao tác, người dùng..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="historyFilter">
            <option value="">Tất cả</option>
            <option value="coupon">Mã giảm giá</option>
            <option value="flash">Flash Sale</option>
          </select>
          <button class="btn-ghost" style="padding:7px 14px;font-size:12.5px" @click="fetchHistory">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:4px"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 101.85-4.28L1 10"/></svg>
            Làm mới
          </button>
        </div>
      </div>

      <div v-if="historyLoading" style="text-align:center;padding:40px;color:#94a3b8">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.22-8.56"/></svg>
        <p style="margin-top:10px;font-size:13px">Đang tải lịch sử...</p>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th style="width:46px">#</th>
            <th>Thao tác</th>
            <th>Loại</th>
            <th>Người thực hiện</th>
            <th>Thời gian</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(log, idx) in filteredHistory" :key="log.id">
            <td style="color:#94a3b8;font-size:12px">{{ idx + 1 }}</td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <span class="history-dot" :style="{ background: log.color }"></span>
                <span>{{ log.action }}</span>
              </div>
            </td>
            <td>
              <span class="status-pill" :class="log.kind === 'flash' ? 's-pending' : 's-delivered'" style="font-size:11px;padding:2px 10px">
                {{ log.kind === 'flash' ? '⚡ Flash Sale' : '🏷️ Mã giảm giá' }}
              </span>
            </td>
            <td style="font-size:13px">{{ log.user }}</td>
            <td style="font-size:12px;color:#64748b">{{ log.time }}</td>
          </tr>
          <tr v-if="filteredHistory.length === 0">
            <td colspan="5" style="text-align:center;color:#94a3b8;padding:36px">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:10px;opacity:.4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <p style="margin:0;font-size:13px">Chưa có lịch sử hoạt động nào.</p>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="table-footer">
        <span class="table-count">{{ filteredHistory.length }} bản ghi</span>
      </div>
    </div>

    <!-- Flash Sale tab -->
    <div v-if="tab === 'flash'" class="card card-body">
      <div class="flash-header">
        <h3 class="card-title-sm">Flash Sale đang chạy</h3>
        <button class="btn-primary" style="padding:8px 16px;font-size:12.5px" @click="openFlashModal('add')">+ Tạo Flash Sale</button>
      </div>

      <div class="flash-grid">
        <div class="flash-card" v-for="f in flashSales" :key="f.id">
          <div class="flash-badge">-{{ f.discount }}%</div>
          <div class="flash-emoji">{{ f.emoji }}</div>
          <h4 class="flash-name">{{ f.name }}</h4>
          <div class="flash-price">
            <span class="flash-new">{{ formatPrice(f.newPrice) }}</span>
            <span class="flash-old" v-if="f.oldPrice">{{ formatPrice(f.oldPrice) }}</span>
          </div>
          <div class="flash-timer">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Còn {{ f.timeLeft }}
          </div>
          <div class="flash-actions">
            <button class="btn-ghost" style="font-size:12px;padding:6px 12px" @click="openFlashModal('edit', f)">Sửa</button>
            <button class="btn-ghost" style="font-size:12px;padding:6px 12px;color:#dc2626;border-color:#fca5a5" @click="stopFlashSale(f)">Dừng</button>
          </div>
        </div>
        <div class="flash-add-card" @click="openFlashModal('add')">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <p>Thêm Flash Sale mới</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Coupon Modal -->
    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2>{{ modalMode === 'add' ? 'Tạo mã giảm giá mới' : 'Chi tiết & Cập nhật mã giảm giá' }}</h2>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group span-2">
              <label>Mã voucher <span class="req">*</span></label>
              <input type="text" v-model="couponForm.code" :disabled="modalMode === 'edit'" placeholder="VD: SALE50, GIAM20K..." style="text-transform:uppercase" />
            </div>
            <div class="form-group">
              <label>Loại giảm giá</label>
              <select v-model="couponForm.type">
                <option value="Phần trăm">Phần trăm (%)</option>
                <option value="Số tiền cố định">Số tiền cố định (₫)</option>
              </select>
            </div>
            <div class="form-group">
              <label>Giá trị giảm <span class="req">*</span></label>
              <input type="number" v-model.number="couponForm.value" placeholder="VD: 20 hoặc 50000" />
            </div>
            <div class="form-group">
              <label>Đơn tối thiểu (₫)</label>
              <input type="number" v-model.number="couponForm.minOrder" placeholder="VD: 200000" />
            </div>
            <div class="form-group">
              <label>Giới hạn sử dụng</label>
              <input type="number" v-model.number="couponForm.limit" placeholder="100" />
            </div>
            <div class="form-group span-2">
              <label>Ngày hết hạn</label>
              <input type="date" v-model="couponForm.expiry" />
            </div>
            <div class="form-group span-2">
              <label>Trạng thái</label>
              <div class="radio-group" style="display: flex; gap: 20px; margin-top: 8px;">
                <label class="radio-opt" style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                  <input type="radio" v-model="couponForm.active" :value="true" /> Hoạt động
                </label>
                <label class="radio-opt" style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                  <input type="radio" v-model="couponForm.active" :value="false" /> Tắt
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showModal = false">Hủy</button>
          <button class="btn-primary" @click="saveCoupon">{{ modalMode === 'add' ? 'Tạo mã' : 'Lưu thay đổi' }}</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Flash Sale Modal -->
    <div class="modal-overlay" v-if="showFlashModal" @click.self="showFlashModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2>{{ flashModalMode === 'add' ? 'Tạo Flash Sale mới' : 'Cập nhật Flash Sale' }}</h2>
          <button class="modal-close" @click="showFlashModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group span-2">
              <label>Tên sản phẩm Flash Sale <span class="req">*</span></label>
              <input type="text" v-model="flashForm.name" placeholder="VD: Gundam RX-78-2 MG, Xe Lamborghini..." required />
            </div>
            <div class="form-group">
              <label>Biểu tượng Emoji <span class="req">*</span></label>
              <select v-model="flashForm.emoji" required>
                <option value="🤖">🤖 Robot / Gundam</option>
                <option value="🦾">🦾 Iron Man / Siêu anh hùng</option>
                <option value="🏎️">🏎️ Xe đua / Siêu xe</option>
                <option value="⚡">⚡ Sét / Flash Sale</option>
                <option value="🔥">🔥 Hot / Lửa</option>
                <option value="🧸">🧸 Đồ chơi / Gấu</option>
                <option value="🎒">🎒 Phụ kiện</option>
              </select>
            </div>
            <div class="form-group">
              <label>Thời gian chạy (còn lại)</label>
              <input type="text" v-model="flashForm.timeLeft" placeholder="VD: 2g 30p, 5g 10p, 1 ngày..." />
            </div>
            <div class="form-group">
              <label>Giá gốc (₫) <span class="req">*</span></label>
              <input type="number" v-model.number="flashForm.oldPrice" @input="calcFlashNewPrice" placeholder="VD: 1250000" required />
            </div>
            <div class="form-group">
              <label>Giảm giá (%) <span class="req">*</span></label>
              <input type="number" v-model.number="flashForm.discount" @input="calcFlashNewPrice" placeholder="VD: 20" required />
            </div>
            <div class="form-group span-2">
              <label>Giá sau giảm (Giá Flash Sale)</label>
              <input type="text" :value="formatPrice(flashForm.newPrice)" readonly style="background: #f1f5f9; font-weight: 700; color: #D70018;" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showFlashModal = false">Hủy</button>
          <button class="btn-primary" @click="saveFlashSale">{{ flashModalMode === 'add' ? 'Tạo chương trình' : 'Lưu thay đổi' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

const BASE = 'http://127.0.0.1:8000/api/quan-ly';

export default {
  name: 'AdminPromotions',
  data() {
    return {
      tab: 'coupon',
      // ── Coupon ──────────────────────────────
      showModal: false,
      modalMode: 'add',
      couponSearch: '',
      couponFilterStatus: '',
      couponLoading: false,
      couponForm: {
        code: '', type: 'Phần trăm', value: '',
        minOrder: '', limit: 100, expiry: '', active: true,
      },
      coupons: [],
      // ── Flash Sale ───────────────────────────
      showFlashModal: false,
      flashModalMode: 'add',
      flashLoading: false,
      flashForm: {
        id: null, name: '', emoji: '⚡',
        discount: 10, oldPrice: '', newPrice: 0, timeLeft: '2g 00p',
      },
      flashSales: [],
      navItems: [
        { key: 'store', label: 'Thông tin cửa hàng', icon: '' },
        { key: 'shipping', label: 'Vận chuyển', icon: '' },
        { key: 'payment', label: 'Thanh toán', icon: '' },
      ],
      // ── Lịch sử ────────────────────────
      historySearch: '',
      historyFilter: '',
      historyLoading: false,
      historyLogs: [],
    };
  },
  computed: {
    filteredCoupons() {
      return this.coupons.filter(c => {
        const matchSearch = !this.couponSearch ||
          c.code.toLowerCase().includes(this.couponSearch.toLowerCase());
        const now = new Date(); now.setHours(0, 0, 0, 0);
        const isExpired = c.expiry ? new Date(c.expiry) < now : false;
        let matchStatus = true;
        if (this.couponFilterStatus === 'active')   matchStatus = c.active && !isExpired;
        if (this.couponFilterStatus === 'expired')  matchStatus = isExpired;
        if (this.couponFilterStatus === 'inactive') matchStatus = !c.active;
        return matchSearch && matchStatus;
      });
    },
    filteredHistory() {
      return this.historyLogs.filter(log => {
        const q = this.historySearch.toLowerCase();
        const matchSearch = !q ||
          log.action.toLowerCase().includes(q) ||
          (log.user || '').toLowerCase().includes(q);
        const matchKind = !this.historyFilter || log.kind === this.historyFilter;
        return matchSearch && matchKind;
      });
    },
  },
  mounted() {
    this.fetchCoupons();
    this.fetchFlashSales();
  },
  methods: {
    /* ── Auth header ─────────────────────────── */
    getConfig() {
      return { headers: { Authorization: 'Bearer ' + localStorage.getItem('token_admin') } };
    },

    /* ── Lịch sử API ─────────────────────────── */
    switchToHistory() {
      this.tab = 'history';
      if (this.historyLogs.length === 0) this.fetchHistory();
    },
    async fetchHistory() {
      this.historyLoading = true;
      try {
        const res = await axios.get(`${BASE}/khuyen-mai/lich-su`, this.getConfig());
        if (res.data.status) this.historyLogs = res.data.data;
      } catch (e) { console.error('fetchHistory:', e); }
      finally { this.historyLoading = false; }
    },

    /* ── Coupon API ──────────────────────────── */
    async fetchCoupons() {
      try {
        const res = await axios.get(`${BASE}/khuyen-mai/coupon`, this.getConfig());
        if (res.data.status) this.coupons = res.data.data;
      } catch (e) { console.error('fetchCoupons:', e); }
    },

    openCouponModal(mode, coupon = null) {
      this.modalMode = mode;
      if (coupon) {
        this.couponForm = {
          code: coupon.code, type: coupon.type, value: coupon.value,
          minOrder: coupon.minOrder, limit: coupon.limit,
          expiry: coupon.expiry || '', active: coupon.active,
        };
      } else {
        this.couponForm = {
          code: '', type: 'Phần trăm', value: '', minOrder: '', limit: 100,
          expiry: new Date(Date.now() + 30 * 864e5).toISOString().split('T')[0],
          active: true,
        };
      }
      this.showModal = true;
    },

    async saveCoupon() {
      if (!this.couponForm.code || !this.couponForm.value) {
        this.showToast('Vui lòng nhập đầy đủ mã voucher và giá trị giảm!', 'error'); return;
      }
      this.couponLoading = true;
      try {
        let res;
        if (this.modalMode === 'add') {
          res = await axios.post(`${BASE}/khuyen-mai/coupon/create`, this.couponForm, this.getConfig());
        } else {
          res = await axios.post(
            `${BASE}/khuyen-mai/coupon/${this.couponForm.code}/update`,
            this.couponForm, this.getConfig()
          );
        }
        if (res.data.status) {
          this.showToast(res.data.message, this.modalMode === 'add' ? 'success' : 'info');
          await this.fetchCoupons();
          this.showModal = false;
        } else {
          this.showToast(res.data.message || 'Lỗi lưu mã giảm giá!', 'error');
        }
      } catch (e) {
        const msg = e.response?.data?.message || 'Có lỗi xảy ra!';
        this.showToast(msg, 'error');
      } finally { this.couponLoading = false; }
    },

    confirmDeleteCoupon(coupon) {
      window.Swal.fire({
        title: 'XÓA MÃ GIẢM GIÁ NÀY?',
        html: `Bạn có chắc chắn muốn xóa mã <b style="color:#D70018">"${coupon.code}"</b>?<br><span style="font-size:13px;color:#64748b">Hành động này không thể hoàn tác.</span>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#D70018', cancelButtonColor: '#f1f5f9',
        confirmButtonText: 'Xác nhận xóa', cancelButtonText: 'Hủy bỏ',
        background: '#ffffff', color: '#0f172a',
        customClass: { popup: 'swal-premium-popup', confirmButton: 'btn-swal-confirm', cancelButton: 'btn-swal-cancel' },
        buttonsStyling: false,
      }).then(async result => {
        if (!result.isConfirmed) return;
        try {
          await axios.delete(`${BASE}/khuyen-mai/coupon/${coupon.code}`, this.getConfig());
          this.showToast('Xóa mã giảm giá thành công!', 'danger');
          await this.fetchCoupons();
        } catch (e) { this.showToast('Lỗi khi xóa mã giảm giá!', 'error'); }
      });
    },

    /* ── Flash Sale API ──────────────────────── */
    async fetchFlashSales() {
      try {
        const res = await axios.get(`${BASE}/khuyen-mai/flash-sale`, this.getConfig());
        if (res.data.status) this.flashSales = res.data.data;
      } catch (e) { console.error('fetchFlashSales:', e); }
    },

    openFlashModal(mode, flash = null) {
      this.flashModalMode = mode;
      if (flash) {
        this.flashForm = {
          id: flash.id, name: flash.name, emoji: flash.emoji,
          discount: flash.discount, oldPrice: flash.oldPrice,
          newPrice: flash.newPrice, timeLeft: flash.timeLeft,
        };
      } else {
        this.flashForm = { id: null, name: '', emoji: '⚡', discount: 10, oldPrice: '', newPrice: 0, timeLeft: '2g 00p' };
      }
      this.showFlashModal = true;
    },

    calcFlashNewPrice() {
      if (this.flashForm.oldPrice && this.flashForm.discount) {
        this.flashForm.newPrice = Math.round(this.flashForm.oldPrice * (1 - this.flashForm.discount / 100));
      } else {
        this.flashForm.newPrice = this.flashForm.oldPrice || 0;
      }
    },

    async saveFlashSale() {
      if (!this.flashForm.name || !this.flashForm.oldPrice || !this.flashForm.discount) {
        this.showToast('Vui lòng điền đầy đủ thông tin bắt buộc!', 'error'); return;
      }
      this.calcFlashNewPrice();
      this.flashLoading = true;
      try {
        let res;
        if (this.flashModalMode === 'add') {
          res = await axios.post(`${BASE}/khuyen-mai/flash-sale/create`, this.flashForm, this.getConfig());
        } else {
          res = await axios.post(
            `${BASE}/khuyen-mai/flash-sale/${this.flashForm.id}/update`,
            this.flashForm, this.getConfig()
          );
        }
        if (res.data.status) {
          this.showToast(res.data.message, this.flashModalMode === 'add' ? 'success' : 'info');
          await this.fetchFlashSales();
          this.showFlashModal = false;
        } else {
          this.showToast(res.data.message || 'Lỗi lưu Flash Sale!', 'error');
        }
      } catch (e) {
        this.showToast(e.response?.data?.message || 'Có lỗi xảy ra!', 'error');
      } finally { this.flashLoading = false; }
    },

    stopFlashSale(flash) {
      window.Swal.fire({
        title: 'DỪNG FLASH SALE?',
        html: `Bạn có muốn dừng Flash Sale của <b style="color:#D70018">"${flash.name}"</b>?<br><span style="font-size:13px;color:#64748b">Hành động này sẽ gỡ sản phẩm khỏi danh sách đang chạy.</span>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#D70018', cancelButtonColor: '#f1f5f9',
        confirmButtonText: 'Xác nhận dừng', cancelButtonText: 'Hủy bỏ',
        background: '#ffffff', color: '#0f172a',
        customClass: { popup: 'swal-premium-popup', confirmButton: 'btn-swal-confirm', cancelButton: 'btn-swal-cancel' },
        buttonsStyling: false,
      }).then(async result => {
        if (!result.isConfirmed) return;
        try {
          await axios.delete(`${BASE}/khuyen-mai/flash-sale/${flash.id}`, this.getConfig());
          this.showToast('Đã dừng Flash Sale thành công!', 'danger');
          await this.fetchFlashSales();
        } catch (e) { this.showToast('Lỗi khi dừng Flash Sale!', 'error'); }
      });
    },

    /* ── Helpers ─────────────────────────────── */
    isCouponExpired(coupon) {
      if (!coupon.expiry) return false;
      const now = new Date(); now.setHours(0, 0, 0, 0);
      return new Date(coupon.expiry) < now;
    },
    formatPrice(value) {
      if (value === null || value === undefined || value === '') return '0 ₫';
      if (typeof value === 'string') return value;
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      if (dateStr.includes('/')) return dateStr;
      const parts = dateStr.split('-');
      return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
    },
    showToast(message, type = 'success') {
      if (type === 'success')            this.$toast.success(message);
      else if (type === 'danger' || type === 'error') this.$toast.error(message);
      else if (type === 'warning')       this.$toast.warning(message);
      else                               this.$toast.info(message);
    },
  },
};
</script>

<style scoped>
@import "/style_admin/promotions.css";
</style>
