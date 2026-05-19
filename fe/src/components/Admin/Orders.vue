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
            <td><span class="order-id-text">{{ o.id }}</span></td>
            <td>
              <div class="customer-cell">
                <div class="cus-avatar">{{ o.customer[0] }}</div>
                <div>
                  <p class="cus-name">{{ o.customer }}</p>
                  <p class="cus-phone">{{ o.phone }}</p>
                </div>
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
                <button class="act-btn edit" title="Cập nhật">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
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
          <h2>Chi tiết đơn hàng {{ selectedOrder.id }}</h2>
          <button class="modal-close" @click="showDetail = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="order-detail-grid">
            <div class="detail-section">
              <h3 class="detail-sec-title">Thông tin khách hàng</h3>
              <div class="detail-rows">
                <div class="detail-row"><span>Họ tên:</span><strong>{{ selectedOrder.customer }}</strong></div>
                <div class="detail-row"><span>SĐT:</span><strong>{{ selectedOrder.phone }}</strong></div>
                <div class="detail-row"><span>Địa chỉ:</span><strong>123 Lê Lợi, Q.1, TP.HCM</strong></div>
                <div class="detail-row"><span>Thanh toán:</span><strong>{{ selectedOrder.payment }}</strong></div>
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
              <div class="workflow-actions">
                <button class="btn-primary" style="font-size:12px;padding:8px 14px">Chuyển bước tiếp</button>
                <button class="btn-ghost" style="font-size:12px;padding:8px 14px;color:#dc2626;border-color:#fca5a5">Huỷ đơn</button>
              </div>
            </div>
          </div>
          <div class="detail-section" style="margin-top:16px">
            <h3 class="detail-sec-title">Sản phẩm trong đơn</h3>
            <table class="data-table" style="margin-top:10px">
              <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
              <tbody>
                <tr><td>{{ selectedOrder.product }}</td><td>1.250.000 ₫</td><td>1</td><td>{{ selectedOrder.total }}</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-outline">In hóa đơn</button>
          <button class="btn-ghost" @click="showDetail = false">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminOrders',
  data() {
    return {
      search: '', filterPayment: '', activeTab: 'all',
      showDetail: false, selectedOrder: null,
      tabs: [
        { key: 'all', label: 'Tất cả', count: 1284 },
        { key: 'pending', label: 'Chờ xác nhận', count: 42 },
        { key: 'shipping', label: 'Đang giao', count: 128 },
        { key: 'delivered', label: 'Đã giao', count: 1098 },
        { key: 'cancelled', label: 'Đã huỷ', count: 16 },
      ],
      statusMap: { delivered: 'Đã giao', shipping: 'Đang giao', pending: 'Chờ xác nhận', cancelled: 'Đã huỷ' },
      workflow: [
        { key: 'pending', label: 'Chờ xác nhận' },
        { key: 'packing', label: 'Đang đóng gói' },
        { key: 'shipping', label: 'Đang giao hàng' },
        { key: 'delivered', label: 'Đã giao' },
      ],
      orders: [
        { id: '#DH8821', customer: 'Nguyễn Văn A', phone: '0912 345 678', product: 'Gundam RX-78', total: '1.250.000 ₫', payment: 'COD', date: '18/05/2026', status: 'delivered' },
        { id: '#DH8820', customer: 'Trần Thị B', phone: '0987 654 321', product: 'Mô hình ô tô F1', total: '890.000 ₫', payment: 'VNPAY', date: '18/05/2026', status: 'shipping' },
        { id: '#DH8819', customer: 'Lê Quốc C', phone: '0901 222 333', product: 'Dragon Ball Z Set', total: '2.100.000 ₫', payment: 'Chuyển khoản', date: '17/05/2026', status: 'pending' },
        { id: '#DH8818', customer: 'Phạm Thu D', phone: '0933 111 444', product: 'Iron Man MK50', total: '3.500.000 ₫', payment: 'COD', date: '17/05/2026', status: 'delivered' },
        { id: '#DH8817', customer: 'Hoàng Minh E', phone: '0944 555 666', product: 'One Piece Luffy', total: '650.000 ₫', payment: 'VNPAY', date: '16/05/2026', status: 'cancelled' },
      ],
    };
  },
  computed: {
    filteredOrders() {
      return this.orders.filter(o => {
        const ms = !this.search || o.id.includes(this.search) || o.customer.toLowerCase().includes(this.search.toLowerCase());
        const mt = this.activeTab === 'all' || o.status === this.activeTab;
        const mp = !this.filterPayment || o.payment === this.filterPayment;
        return ms && mt && mp;
      });
    },
    currentWfIdx() {
      const wfMap = { pending: 0, packing: 1, shipping: 2, delivered: 3 };
      return wfMap[this.selectedOrder?.status] ?? 0;
    },
  },
  methods: {
    openDetail(order) { this.selectedOrder = order; this.showDetail = true; },
  },
};
</script>

<style scoped>
@import "/style_admin/orders.css";
</style>
