<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý đơn hàng</h1>
        <p class="page-sub">Theo dõi và xử lý toàn bộ quy trình đặt hàng</p>
      </div>
      <button class="btn-outline">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Xuất PDF
      </button>
    </div>

    <div class="status-tabs">
      <button v-for="tab in tabs" :key="tab.key"
        class="status-tab" :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key">
        {{ tab.label }}
        <span class="tab-count">{{ tab.count }}</span>
      </button>
    </div>

    <div class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="search" type="text" placeholder="Tìm theo mã đơn, tên khách..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="filterPayment">
            <option value="">Thanh toán</option>
            <option>COD</option>
            <option>Chuyển khoản</option>
            <option>VNPAY</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Sản phẩm</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
            <th>Ngày đặt</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in filteredOrders" :key="o.id">
            <td><span class="order-id-text">{{ o.code }}</span></td>
            <td>
              <div class="customer-cell" style="display: block;">
                <p class="cus-name" style="font-weight: 500; margin-bottom: 2px;">{{ o.customer }}</p>
                <p class="cus-phone" style="font-size: 12px; color: #6b7280;" v-if="o.phone && o.phone !== 'N/A'">{{ o.phone }}</p>
              </div>
            </td>
            <td>{{ o.product }}</td>
            <td><span class="price-red">{{ o.total }}</span></td>
            <td><span class="payment-badge">{{ o.payment }}</span></td>
            <td>{{ o.date }}</td>
            <td><span class="status-pill" :class="'s-' + o.status">{{ statusMap[o.status] }}</span></td>
            <td>
              <div class="action-btns">
                <button class="act-btn view" @click="openDetail(o)" title="Chi tiết">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-count">{{ filteredOrders.length }} đơn hàng</span>
        <div class="pagination">
          <button class="pg-btn">&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn">2</button>
          <button class="pg-btn">&gt;</button>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal-overlay" v-if="showDetail" @click.self="showDetail = false">
      <div class="modal-box modal-lg" v-if="selectedOrder">
        <div class="modal-header">
          <h2>Chi tiết đơn hàng {{ selectedOrder.code }}</h2>
          <button class="modal-close" @click="showDetail = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="order-detail-grid">
            <div class="detail-section">
              <h3 class="detail-sec-title">Thông tin khách hàng</h3>
              <div class="detail-rows">
                <div class="detail-row"><span>Họ tên:</span><strong>{{ selectedOrder.customer }}</strong></div>
                <div class="detail-row"><span>SĐT:</span><strong>{{ selectedOrder.phone }}</strong></div>
                <div class="detail-row"><span>Địa chỉ:</span><strong>{{ selectedOrder.address }}</strong></div>
                <div class="detail-row"><span>Thanh toán:</span><strong>{{ selectedOrder.payment }} ({{ selectedOrder.payment_status }})</strong></div>
              </div>
            </div>
            <div class="detail-section">
              <h3 class="detail-sec-title">Quy trình đơn hàng</h3>
              <div class="status-workflow">
                <div v-for="(s, i) in workflow" :key="s.key"
                  class="wf-step" :class="{ done: i < currentWfIdx, current: i === currentWfIdx }">
                  <div class="wf-dot"></div>
                  <span>{{ s.label }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="detail-section" style="margin-top:16px">
              <h3 class="detail-sec-title">Sản phẩm trong đơn</h3>
            <table class="data-table" style="margin-top:10px">
              <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
              <tbody>
                <tr v-for="(sp, idx) in selectedOrder.chi_tiets" :key="idx">
                  <td>{{ sp.ten }}</td>
                  <td>{{ sp.gia }}</td>
                  <td>{{ sp.sl }}</td>
                  <td>{{ sp.thanh_tien }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-outline">In hóa đơn</button>
          <button class="btn-primary" style="background-color: #3b82f6; border-color: #3b82f6;" v-if="selectedOrder.status === 'cho_xu_ly'" @click="updateOrderStatus('dang_chuan_bi')">Xác nhận đơn</button>
          <button class="btn-primary" style="background-color: #f59e0b; border-color: #f59e0b;" v-if="selectedOrder.status === 'dang_chuan_bi'" @click="updateOrderStatus('dang_giao')">Giao hàng</button>
          <button class="btn-primary" style="background-color: #10b981; border-color: #10b981;" v-if="selectedOrder.status === 'dang_giao'" @click="updateOrderStatus('da_giao')">Hoàn tất</button>
          <button class="btn-outline" style="color: #ef4444; border-color: #ef4444; background-color: #fef2f2;" v-if="['cho_xu_ly', 'dang_chuan_bi'].includes(selectedOrder.status)" @click="updateOrderStatus('da_huy')">Huỷ đơn</button>
          <button class="btn-ghost" @click="showDetail = false">Đóng</button>
        </div>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal-overlay" v-if="showConfirmModal" @click.self="showConfirmModal = false; pendingStatus = null">
      <div class="modal-box" style="max-width: 420px; text-align: center; z-index: 1000; border-radius: 16px; padding: 32px 24px;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h3 style="margin-bottom: 12px; font-size: 20px; font-weight: 700; color: #111827;">Xác nhận thay đổi</h3>
        <p style="color: #4b5563; line-height: 1.5; font-size: 15px; margin-bottom: 16px;">
          Bạn có chắc chắn muốn chuyển trạng thái đơn hàng này thành:
        </p>
        <div style="margin-bottom: 28px;">
          <span class="status-pill" :class="'s-' + pendingStatus" style="font-size: 15px !important; padding: 8px 24px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            {{ statusMap[pendingStatus] }}
          </span>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center;">
          <button class="btn-ghost" style="padding: 10px 24px; border-radius: 8px;" @click="showConfirmModal = false; pendingStatus = null">Huỷ bỏ</button>
          <button class="btn-primary" style="background-color: #3b82f6; border-color: #3b82f6; padding: 10px 24px; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(59,130,246,0.5);" @click="executeStatusUpdate">Đồng ý</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
  name: 'AdminOrders',
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      search: '', filterPayment: '', activeTab: 'all',
      showDetail: false, selectedOrder: null,
      showConfirmModal: false, pendingStatus: null,
      orders: [],
      statusMap: { 
        cho_xu_ly: 'Chờ xác nhận', 
        dang_chuan_bi: 'Đang chuẩn bị', 
        dang_giao: 'Đang giao', 
        da_giao: 'Đã giao', 
        da_huy: 'Đã huỷ' 
      },
      workflow: [
        { key: 'cho_xu_ly', label: 'Chờ xác nhận' },
        { key: 'dang_chuan_bi', label: 'Đang chuẩn bị' },
        { key: 'dang_giao', label: 'Đang giao hàng' },
        { key: 'da_giao', label: 'Đã giao' },
      ],
    };
  },
  computed: {
    tabs() {
      return [
        { key: 'all', label: 'Tất cả', count: this.orders.length },
        { key: 'cho_xu_ly', label: 'Chờ xác nhận', count: this.orders.filter(o => o.status === 'cho_xu_ly').length },
        { key: 'dang_giao', label: 'Đang giao', count: this.orders.filter(o => o.status === 'dang_giao').length },
        { key: 'da_giao', label: 'Đã giao', count: this.orders.filter(o => o.status === 'da_giao').length },
        { key: 'da_huy', label: 'Đã huỷ', count: this.orders.filter(o => o.status === 'da_huy').length },
      ];
    },
    filteredOrders() {
      return this.orders.filter(o => {
        const ms = !this.search || o.code.toLowerCase().includes(this.search.toLowerCase()) || o.customer.toLowerCase().includes(this.search.toLowerCase());
        const mt = this.activeTab === 'all' || o.status === this.activeTab;
        const mp = !this.filterPayment || o.payment === this.filterPayment;
        return ms && mt && mp;
      });
    },
    currentWfIdx() {
      const wfMap = { cho_xu_ly: 0, dang_chuan_bi: 1, dang_giao: 2, da_giao: 3 };
      return wfMap[this.selectedOrder?.status] ?? 0;
    },
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/quan-ly/don-hang', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token_admin')}`
          }
        });
        if (response.data.status === 1) {
          this.orders = response.data.data;
        }
      } catch (error) {
        console.error('Lỗi lấy đơn hàng:', error);
        this.toast.error('Không thể tải danh sách đơn hàng');
      }
    },
    openDetail(order) { 
      this.selectedOrder = order; 
      this.showDetail = true; 
    },
    updateOrderStatus(newStatus) {
      this.pendingStatus = newStatus;
      this.showConfirmModal = true;
    },
    async executeStatusUpdate() {
      if (!this.pendingStatus) return;
      const newStatus = this.pendingStatus;
      this.showConfirmModal = false;
      this.pendingStatus = null;
      
      try {
        const response = await axios.patch(`http://127.0.0.1:8000/api/quan-ly/don-hang/${this.selectedOrder.id}/trang-thai`, {
          trang_thai: newStatus
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token_admin')}`
          }
        });

        if (response.data.status === 1) {
          this.toast.success(response.data.message);
          this.selectedOrder.status = newStatus;
          // Refresh list
          this.fetchOrders();
          window.dispatchEvent(new CustomEvent("orderStatusUpdated"));
          if (newStatus === 'da_huy') {
              this.showDetail = false;
          }
        } else {
          this.toast.error(response.data.message);
        }
      } catch (error) {
        console.error(error);
        this.toast.error(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật đơn hàng');
      }
    }
  },
};
</script>

<style scoped>
@import "/style_admin/orders.css";

.status-pill {
    white-space: nowrap !important;
    padding: 6px 12px !important;
    border-radius: 20px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    display: inline-block !important;
    min-width: 120px !important;
    text-align: center !important;
    vertical-align: middle !important;
}

.status-pill.s-cho_xu_ly { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid #fde68a !important; }
.status-pill.s-dang_chuan_bi { background-color: #eff6ff !important; color: #2563eb !important; border: 1px solid #bfdbfe !important; }
.status-pill.s-dang_giao { background-color: #fefce8 !important; color: #ca8a04 !important; border: 1px solid #fef08a !important; }
.status-pill.s-da_giao { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid #a7f3d0 !important; }
.status-pill.s-da_huy { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
</style>
