<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Thống kê & Báo cáo</h1>
        <p class="page-sub">Tổng quan doanh thu và hiệu suất kinh doanh</p>
      </div>
      <div class="toolbar-right">
        <button v-for="p in periods" :key="p.key"
          class="period-btn" :class="{ active: activePeriod === p.key }"
          @click="activePeriod = p.key">{{ p.label }}</button>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card" v-for="k in kpis" :key="k.label" :style="{ '--accent': k.color }">
        <div class="kpi-icon" :style="{ background: k.iconBg }"><span v-html="k.icon"></span></div>
        <div class="kpi-body">
          <p class="kpi-val">{{ k.val }}</p>
          <p class="kpi-label">{{ k.label }}</p>
          <div class="kpi-trend" :class="k.trendUp ? 'up' : 'down'">
            <svg v-if="k.trendUp" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            {{ k.trend }} so với tháng trước
          </div>
        </div>
        <div class="kpi-accent-bar" :style="{ background: k.color }"></div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
      <!-- Doanh thu theo tháng (giả lập bar chart) -->
      <div class="card card-body chart-card">
        <h3 class="card-title-sm" style="margin-bottom:20px">Doanh thu theo tháng</h3>
        <div class="bar-chart">
          <div class="bar-item" v-for="b in barData" :key="b.month">
            <div class="bar-fill-wrap">
              <div class="bar-fill" :style="{ height: b.pct + '%', background: b.color }"></div>
            </div>
            <div class="bar-label">{{ b.month }}</div>
            <div class="bar-val">{{ b.val }}</div>
          </div>
        </div>
      </div>

      <!-- Phân tích đơn hàng -->
      <div class="card card-body chart-card-sm">
        <h3 class="card-title-sm" style="margin-bottom:16px">Tỷ lệ trạng thái đơn</h3>
        <div class="donut-wrap">
          <svg viewBox="0 0 120 120" class="donut-svg">
            <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="16"/>
            <circle cx="60" cy="60" r="50" fill="none" stroke="#22c55e" stroke-width="16"
              stroke-dasharray="196 118" stroke-dashoffset="25" />
            <circle cx="60" cy="60" r="50" fill="none" stroke="#6366f1" stroke-width="16"
              stroke-dasharray="90 224" stroke-dashoffset="-171" />
            <circle cx="60" cy="60" r="50" fill="none" stroke="#f59e0b" stroke-width="16"
              stroke-dasharray="28 286" stroke-dashoffset="-261" />
            <circle cx="60" cy="60" r="50" fill="none" stroke="#D70018" stroke-width="16"
              stroke-dasharray="10 304" stroke-dashoffset="-289" />
          </svg>
          <div class="donut-center">
            <p class="donut-val">1,284</p>
            <p class="donut-lbl">Tổng đơn</p>
          </div>
        </div>
        <div class="legend-list">
          <div class="legend-item" v-for="l in legendData" :key="l.label">
            <div class="legend-dot" :style="{ background: l.color }"></div>
            <span class="legend-label">{{ l.label }}</span>
            <span class="legend-pct">{{ l.pct }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Top products & Payment methods -->
    <div class="bottom-row">
      <!-- Top sản phẩm -->
      <div class="card card-body">
        <h3 class="card-title-sm" style="margin-bottom:16px">Top sản phẩm bán chạy</h3>
        <div class="top-prod-list">
          <div class="tp-item" v-for="(p, i) in topProducts" :key="p.name">
            <div class="tp-rank" :class="i < 3 ? 'top' : ''">{{ i + 1 }}</div>
            <div class="tp-emoji">{{ p.emoji }}</div>
            <div class="tp-info">
              <p class="tp-name">{{ p.name }}</p>
              <div class="tp-bar-wrap">
                <div class="tp-bar" :style="{ width: p.pct + '%', background: p.color }"></div>
              </div>
            </div>
            <div>
              <p class="tp-sold">{{ p.sold }} đã bán</p>
              <p class="tp-revenue">{{ p.revenue }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Phương thức thanh toán -->
      <div class="card card-body">
        <h3 class="card-title-sm" style="margin-bottom:16px">Phương thức thanh toán</h3>
        <div class="payment-list">
          <div class="payment-item" v-for="pm in paymentMethods" :key="pm.name">
            <div class="pm-icon" :style="{ background: pm.bg }"><span v-html="pm.icon"></span></div>
            <div class="pm-info">
              <p class="pm-name">{{ pm.name }}</p>
              <div class="tp-bar-wrap">
                <div class="tp-bar" :style="{ width: pm.pct + '%', background: pm.color }"></div>
              </div>
            </div>
            <div class="pm-stats">
              <p class="pm-pct">{{ pm.pct }}%</p>
              <p class="pm-amt">{{ pm.amount }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminAnalytics',
  data() {
    return {
      activePeriod: 'month',
      periods: [
        { key: 'today', label: 'Hôm nay' },
        { key: 'week', label: 'Tuần này' },
        { key: 'month', label: 'Tháng này' },
        { key: 'year', label: 'Năm nay' },
      ],
      kpis: [
        { label: 'Doanh thu', val: '128.4M ₫', trend: '+12.5%', trendUp: true, color: '#D70018', iconBg: 'rgba(215,0,24,0.1)', icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>` },
        { label: 'Số đơn hàng', val: '1,284', trend: '+8.1%', trendUp: true, color: '#6366f1', iconBg: 'rgba(99,102,241,0.1)', icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>` },
        { label: 'Tỷ lệ hủy đơn', val: '1.2%', trend: '-0.3%', trendUp: true, color: '#22c55e', iconBg: 'rgba(34,197,94,0.1)', icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>` },
        { label: 'Khách hàng mới', val: '342', trend: '+5.2%', trendUp: true, color: '#f59e0b', iconBg: 'rgba(245,158,11,0.1)', icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/><line x1="20" y1="8" x2="20" y2="14"/></svg>` },
      ],
      barData: [
        { month: 'T1', val: '82M', pct: 55, color: '#e2e8f0' },
        { month: 'T2', val: '96M', pct: 65, color: '#e2e8f0' },
        { month: 'T3', val: '110M', pct: 74, color: '#e2e8f0' },
        { month: 'T4', val: '88M', pct: 59, color: '#e2e8f0' },
        { month: 'T5', val: '128M', pct: 86, color: '#D70018' },
        { month: 'T6', val: '105M', pct: 71, color: '#e2e8f0' },
        { month: 'T7', val: '118M', pct: 79, color: '#e2e8f0' },
        { month: 'T8', val: '94M', pct: 63, color: '#e2e8f0' },
        { month: 'T9', val: '135M', pct: 91, color: '#e2e8f0' },
        { month: 'T10', val: '122M', pct: 82, color: '#e2e8f0' },
        { month: 'T11', val: '148M', pct: 99, color: '#e2e8f0' },
        { month: 'T12', val: '150M', pct: 100, color: '#e2e8f0' },
      ],
      legendData: [
        { label: 'Đã giao', color: '#22c55e', pct: 61 },
        { label: 'Đang giao', color: '#6366f1', pct: 28 },
        { label: 'Chờ xác nhận', color: '#f59e0b', pct: 9 },
        { label: 'Đã huỷ', color: '#D70018', pct: 2 },
      ],
      topProducts: [
        { name: 'Gundam RX-78-2 MG', emoji: '🤖', sold: 248, revenue: '310M ₫', pct: 90, color: '#D70018' },
        { name: 'Iron Man MK50', emoji: '🦾', sold: 187, revenue: '654.5M ₫', pct: 70, color: '#6366f1' },
        { name: 'Dragon Ball Z Set', emoji: '🐉', sold: 163, revenue: '145M ₫', pct: 60, color: '#f59e0b' },
        { name: 'Ferrari F40 1:18', emoji: '🏎️', sold: 142, revenue: '92.3M ₫', pct: 52, color: '#0ea5e9' },
        { name: 'One Piece Luffy', emoji: '⚓', sold: 98, revenue: '205.8M ₫', pct: 36, color: '#22c55e' },
      ],
      paymentMethods: [
        { name: 'COD', pct: 48, amount: '61.6M ₫', color: '#f59e0b', bg: 'rgba(245,158,11,0.1)', icon: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>` },
        { name: 'VNPAY', pct: 31, amount: '39.8M ₫', color: '#D70018', bg: 'rgba(215,0,24,0.1)', icon: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D70018" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>` },
        { name: 'Chuyển khoản', pct: 21, amount: '27M ₫', color: '#6366f1', bg: 'rgba(99,102,241,0.1)', icon: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>` },
      ],
    };
  },
};
</script>

<style scoped>
@import "/style_admin/analytics.css";
</style>
