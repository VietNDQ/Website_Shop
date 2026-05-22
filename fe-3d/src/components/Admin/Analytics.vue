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

    <!-- Loading spinner -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
      <p class="loading-text">Đang tải dữ liệu thống kê...</p>
    </div>

    <div v-else>
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
              {{ k.trend }} so với chu kỳ trước
            </div>
          </div>
          <div class="kpi-accent-bar" :style="{ background: k.color }"></div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="charts-row">
        <!-- Doanh thu theo thời gian -->
        <div class="card card-body chart-card">
          <h3 class="card-title-sm" style="margin-bottom:20px">{{ chartTitle }}</h3>
          <div class="bar-chart">
            <div class="bar-item" v-for="b in barData" :key="b.label">
              <div class="bar-fill-wrap">
                <div class="bar-fill" :style="{ height: b.pct + '%', background: b.color }"></div>
              </div>
              <div class="bar-label">{{ b.label }}</div>
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
              <circle v-for="c in donutCircles" :key="c.label"
                cx="60" cy="60" r="50" fill="none" :stroke="c.color" stroke-width="16"
                :stroke-dasharray="c.dashArray" :stroke-dashoffset="c.dashOffset" />
            </svg>
            <div class="donut-center">
              <p class="donut-val">{{ totalStatusOrders }}</p>
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
              <img v-if="p.image" :src="formatProductImage(p.image)" class="tp-img-custom" />
              <div v-else class="tp-emoji">{{ p.emoji }}</div>
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
            <div v-if="topProducts.length === 0" class="no-data">
              Không có sản phẩm bán chạy trong khoảng thời gian này.
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
  </div>
</template>

<script>
import axios from 'axios';

const BASE = '/api/quan-ly';

export default {
  name: 'AdminAnalytics',
  data() {
    return {
      loading: true,
      activePeriod: 'month',
      periods: [
        { key: 'today', label: 'Hôm nay' },
        { key: 'week', label: 'Tuần này' },
        { key: 'month', label: 'Tháng này' },
        { key: 'year', label: 'Năm nay' },
      ],
      kpis: [],
      barData: [],
      totalStatusOrders: 0,
      legendData: [],
      topProducts: [],
      paymentMethods: [],
    };
  },
  computed: {
    chartTitle() {
      const map = {
        today: 'Doanh thu hôm nay (theo giờ)',
        week: 'Doanh thu tuần này (theo ngày)',
        month: 'Doanh thu tháng này (theo khoảng ngày)',
        year: 'Doanh thu năm nay (theo tháng)',
      };
      return map[this.activePeriod] || 'Doanh thu';
    },
    donutCircles() {
      const circumference = 314.159; // 2 * pi * r (where r = 50)
      let currentOffset = 25; // initial offset to match visually
      
      return this.legendData.map(item => {
        const pct = item.pct || 0;
        const dashLength = (pct / 100) * circumference;
        const dashArray = `${dashLength.toFixed(1)} ${(circumference - dashLength).toFixed(1)}`;
        const dashOffset = currentOffset.toFixed(1);
        
        currentOffset -= dashLength;
        
        return {
          ...item,
          dashArray,
          dashOffset
        };
      });
    }
  },
  watch: {
    activePeriod() {
      this.fetchAnalyticsData();
    }
  },
  mounted() {
    this.fetchAnalyticsData();
  },
  methods: {
    async fetchAnalyticsData() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token_admin');
        const res = await axios.get(`${BASE}/analytics`, {
          params: { period: this.activePeriod },
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (res.data.status) {
          const d = res.data.data;
          this.kpis = d.stats;
          this.barData = d.barData;
          this.totalStatusOrders = d.totalStatusOrders;
          this.legendData = d.legendData;
          this.topProducts = d.topProducts;
          this.paymentMethods = d.paymentMethods;
        }
      } catch (error) {
        console.error('Lỗi khi tải dữ liệu thống kê:', error);
      } finally {
        this.loading = false;
      }
    },
    formatProductImage(img) {
      if (!img) return null;
      if (img.startsWith('http://') || img.startsWith('https://')) {
        return img;
      }
      return `${img}`;
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/analytics.css";

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  min-height: 400px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.spinner {
  width: 44px;
  height: 44px;
  border: 4px solid #f1f5f9;
  border-top: 4px solid #D70018;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}

.loading-text {
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.tp-img-custom {
  width: 32px;
  height: 32px;
  object-fit: cover;
  border-radius: 6px;
  flex-shrink: 0;
}

.no-data {
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
  padding: 20px 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
