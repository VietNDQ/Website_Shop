<template>
  <div class="dashboard-page">

    <!-- Stats Row -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label" :class="stat.type">
        <div class="stat-icon">
          <span v-html="stat.icon"></span>
        </div>
        <div class="stat-content">
          <p class="stat-value">{{ stat.value }}</p>
          <p class="stat-label">{{ stat.label }}</p>
        </div>
        <div class="stat-trend" :class="stat.trendUp ? 'up' : 'down'">
          <svg v-if="stat.trendUp" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          {{ stat.trend }}
        </div>
      </div>
    </div>

    <!-- Middle Row -->
    <div class="dash-mid">
      <!-- Recent Orders -->
      <div class="dash-card dash-orders">
        <div class="card-header">
          <h2 class="card-title">Đơn hàng gần đây</h2>
          <a href="#" class="card-link">Xem tất cả</a>
        </div>
        <table class="orders-table">
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Khách hàng</th>
              <th>Sản phẩm</th>
              <th>Tổng tiền</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in recentOrders" :key="order.id">
              <td class="order-id">{{ order.id }}</td>
              <td>{{ order.customer }}</td>
              <td class="order-product">{{ order.product }}</td>
              <td class="order-price">{{ order.price }}</td>
              <td>
                <span class="status-badge" :class="'status-' + order.status">
                  {{ statusLabel(order.status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Top Products -->
      <div class="dash-card dash-top-products">
        <div class="card-header">
          <h2 class="card-title">Sản phẩm bán chạy</h2>
          <a href="#" class="card-link">Chi tiết</a>
        </div>
        <div class="top-products-list">
          <div class="top-product-item" v-for="(p, i) in topProducts" :key="p.name">
            <div class="tp-rank">{{ i + 1 }}</div>
            <div class="tp-emoji">{{ p.emoji }}</div>
            <div class="tp-info">
              <p class="tp-name">{{ p.name }}</p>
              <div class="tp-bar-wrap">
                <div class="tp-bar" :style="{ width: p.pct + '%', background: p.color }"></div>
              </div>
            </div>
            <div class="tp-sold">{{ p.sold }} đã bán</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row -->
    <div class="dash-bottom">
      <!-- Activity feed -->
      <div class="dash-card dash-activity">
        <div class="card-header">
          <h2 class="card-title">Hoạt động gần đây</h2>
        </div>
        <div class="activity-list">
          <div class="activity-item" v-for="act in activities" :key="act.id">
            <div class="act-dot" :style="{ background: act.color }"></div>
            <div class="act-body">
              <p class="act-text">{{ act.text }}</p>
              <p class="act-time">{{ act.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="dash-card dash-quick">
        <div class="card-header">
          <h2 class="card-title">Thao tác nhanh</h2>
        </div>
        <div class="quick-grid">
          <button class="quick-btn" v-for="q in quickActions" :key="q.label">
            <div class="quick-icon" :style="{ background: q.bg }">
              <span v-html="q.icon"></span>
            </div>
            <span>{{ q.label }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminDashboard",
  data() {
    return {
      stats: [
        {
          label: "Doanh thu tháng",
          value: "128.4M ₫",
          trend: "+12.5%",
          trendUp: true,
          type: "revenue",
          bg: "linear-gradient(135deg,#D70018,#ff4d5e)",
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>`,
        },
        {
          label: "Đơn hàng",
          value: "1,284",
          trend: "+8.1%",
          trendUp: true,
          type: "orders",
          bg: "linear-gradient(135deg,#6366f1,#8b5cf6)",
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>`,
        },
        {
          label: "Sản phẩm",
          value: "342",
          trend: "+3 mới",
          trendUp: true,
          type: "products",
          bg: "linear-gradient(135deg,#0ea5e9,#38bdf8)",
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>`,
        },
        {
          label: "Khách hàng",
          value: "8,741",
          trend: "-2.3%",
          trendUp: false,
          type: "customers",
          bg: "linear-gradient(135deg,#f59e0b,#fbbf24)",
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>`,
        },
      ],
      recentOrders: [
        { id: "#DH8821", customer: "Nguyễn Văn A", product: "Gundam RX-78", price: "1.250.000 ₫", status: "delivered" },
        { id: "#DH8820", customer: "Trần Thị B", product: "Mô hình ô tô F1", price: "890.000 ₫", status: "shipping" },
        { id: "#DH8819", customer: "Lê Quốc C", product: "Dragon Ball Z Set", price: "2.100.000 ₫", status: "pending" },
        { id: "#DH8818", customer: "Phạm Thu D", product: "Iron Man MK50", price: "3.500.000 ₫", status: "delivered" },
        { id: "#DH8817", customer: "Hoàng Minh E", product: "One Piece Luffy", price: "650.000 ₫", status: "cancelled" },
      ],
      topProducts: [
        { name: "Gundam RX-78-2", emoji: "🤖", sold: 248, pct: 90, color: "#D70018" },
        { name: "Iron Man MK50", emoji: "🦾", sold: 187, pct: 70, color: "#6366f1" },
        { name: "Dragon Ball Z", emoji: "🐉", sold: 163, pct: 60, color: "#f59e0b" },
        { name: "Mô hình F1 2024", emoji: "🏎️", sold: 142, pct: 52, color: "#0ea5e9" },
        { name: "One Piece Set", emoji: "⚓", sold: 98, pct: 36, color: "#22c55e" },
      ],
      activities: [
        { id: 1, text: "Đơn hàng #DH8821 đã giao thành công", time: "5 phút trước", color: "#22c55e" },
        { id: 2, text: "Khách hàng mới đăng ký: Trần Văn F", time: "18 phút trước", color: "#6366f1" },
        { id: 3, text: "Sản phẩm 'Gundam SEED' vừa được thêm", time: "1 giờ trước", color: "#0ea5e9" },
        { id: 4, text: "Đơn hàng #DH8817 bị huỷ bởi khách", time: "2 giờ trước", color: "#D70018" },
        { id: 5, text: "Báo cáo tháng 5 đã được xuất", time: "3 giờ trước", color: "#f59e0b" },
        { id: 6, text: "Cập nhật giá 12 sản phẩm thành công", time: "5 giờ trước", color: "#94a3b8" },
      ],
      quickActions: [
        { label: "Thêm SP", bg: "rgba(215,0,24,0.1)", icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>` },
        { label: "Tạo đơn", bg: "rgba(99,102,241,0.1)", icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>` },
        { label: "Xuất BC", bg: "rgba(14,165,233,0.1)", icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>` },
        { label: "Thông báo", bg: "rgba(245,158,11,0.1)", icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>` },
      ],
    };
  },
  methods: {
    statusLabel(s) {
      const map = { delivered: "Đã giao", shipping: "Đang giao", pending: "Chờ xử lý", cancelled: "Đã huỷ" };
      return map[s] || s;
    },
  },
};
</script>

<style scoped>
@import "/style_admin/dashboard.css";
</style>
