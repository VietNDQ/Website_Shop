<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý khách hàng</h1>
        <p class="page-sub">Hồ sơ, lịch sử mua hàng và kiểm duyệt đánh giá</p>
      </div>
    </div>

    <!-- Tabs: Danh sách / Đánh giá -->
    <div class="cus-tabs">
      <button class="cus-tab" :class="{ active: tab === 'list' }" @click="tab = 'list'">Danh sách khách hàng</button>
      <button class="cus-tab" :class="{ active: tab === 'reviews' }" @click="tab = 'reviews'">Đánh giá & Bình luận</button>
    </div>

    <!-- Danh sách khách hàng -->
    <div v-if="tab === 'list'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="search" type="text" placeholder="Tìm theo tên, email, SĐT..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="filterGroup">
            <option value="">Tất cả nhóm</option>
            <option>Khách mới</option>
            <option>Khách thân thiết</option>
            <option>Khách VIP</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Khách hàng</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Tổng đơn</th>
            <th>Tổng chi tiêu</th>
            <th>Nhóm</th>
            <th>Ngày đăng ký</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredCustomers" :key="c.id" @click="openProfile(c)" style="cursor:pointer">
            <td>
              <div class="customer-cell">
                <div class="cus-avatar" :style="{ background: c.avatarBg }">{{ c.name[0] }}</div>
                <div>
                  <p class="cus-name">{{ c.name }}</p>
                  <p class="cus-phone">ID: #{{ c.id }}</p>
                </div>
              </div>
            </td>
            <td>{{ c.email }}</td>
            <td>{{ c.phone }}</td>
            <td><strong>{{ c.orders }}</strong> đơn</td>
            <td><span class="price-red">{{ c.spent }}</span></td>
            <td><span class="group-badge" :class="'g-' + c.group">{{ c.group }}</span></td>
            <td>{{ c.joinDate }}</td>
            <td>
              <div class="action-btns">
                <button class="act-btn view" title="Xem hồ sơ">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-count">{{ filteredCustomers.length }} khách hàng</span>
        <div class="pagination">
          <button class="pg-btn">&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn">2</button>
          <button class="pg-btn">&gt;</button>
        </div>
      </div>
    </div>

    <!-- Đánh giá & bình luận -->
    <div v-if="tab === 'reviews'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" placeholder="Tìm theo sản phẩm, khách hàng..." />
        </div>
        <div class="toolbar-right">
          <select class="sel">
            <option value="">Tất cả sao</option>
            <option>5 sao</option>
            <option>4 sao</option>
            <option>3 sao</option>
            <option>1-2 sao</option>
          </select>
          <select class="sel">
            <option value="">Trạng thái</option>
            <option>Đã duyệt</option>
            <option>Chờ duyệt</option>
            <option>Đã ẩn</option>
          </select>
        </div>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Khách hàng</th>
            <th>Sản phẩm</th>
            <th>Đánh giá</th>
            <th>Nội dung</th>
            <th>Ngày</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reviews" :key="r.id">
            <td>{{ r.customer }}</td>
            <td>{{ r.product }}</td>
            <td>
              <div class="stars">
                <span v-for="i in 5" :key="i" :class="i <= r.stars ? 'star-on' : 'star-off'">★</span>
              </div>
            </td>
            <td class="review-text">{{ r.content }}</td>
            <td>{{ r.date }}</td>
            <td><span class="status-pill" :class="'s-' + r.status">{{ r.statusLabel }}</span></td>
            <td>
              <div class="action-btns">
                <button class="act-btn edit" title="Duyệt">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
                <button class="act-btn del" title="Ẩn">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Profile modal -->
    <div class="modal-overlay" v-if="showProfile" @click.self="showProfile = false">
      <div class="modal-box modal-lg" v-if="selectedCustomer">
        <div class="modal-header">
          <h2>Hồ sơ: {{ selectedCustomer.name }}</h2>
          <button class="modal-close" @click="showProfile = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="profile-top">
            <div class="profile-avatar-lg" :style="{ background: selectedCustomer.avatarBg }">{{ selectedCustomer.name[0] }}</div>
            <div>
              <h3 class="profile-name">{{ selectedCustomer.name }}</h3>
              <p class="profile-email">{{ selectedCustomer.email }}</p>
              <span class="group-badge" :class="'g-' + selectedCustomer.group">{{ selectedCustomer.group }}</span>
            </div>
          </div>
          <div class="profile-stats">
            <div class="ps-item"><p class="ps-val">{{ selectedCustomer.orders }}</p><p class="ps-lbl">Tổng đơn hàng</p></div>
            <div class="ps-item"><p class="ps-val price-red">{{ selectedCustomer.spent }}</p><p class="ps-lbl">Tổng chi tiêu</p></div>
            <div class="ps-item"><p class="ps-val">{{ selectedCustomer.joinDate }}</p><p class="ps-lbl">Ngày đăng ký</p></div>
          </div>
          <h4 class="detail-sec-title" style="margin-top:16px">Lịch sử mua hàng gần đây</h4>
          <table class="data-table" style="margin-top:8px">
            <thead><tr><th>Mã đơn</th><th>Sản phẩm</th><th>Tổng</th><th>Ngày</th><th>TT</th></tr></thead>
            <tbody>
              <tr><td>#DH8821</td><td>Gundam RX-78</td><td>1.250.000 ₫</td><td>18/05/2026</td><td><span class="status-pill s-delivered">Đã giao</span></td></tr>
              <tr><td>#DH8710</td><td>Iron Man MK50</td><td>3.500.000 ₫</td><td>10/04/2026</td><td><span class="status-pill s-delivered">Đã giao</span></td></tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showProfile = false">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminCustomers',
  data() {
    return {
      tab: 'list', search: '', filterGroup: '',
      showProfile: false, selectedCustomer: null,
      customers: [
        { id: 1001, name: 'Nguyễn Văn A', email: 'a@gmail.com', phone: '0912 345 678', orders: 18, spent: '28.400.000 ₫', group: 'Khách VIP', joinDate: '12/01/2025', avatarBg: 'linear-gradient(135deg,#D70018,#7c3aed)' },
        { id: 1002, name: 'Trần Thị B', email: 'b@gmail.com', phone: '0987 654 321', orders: 5, spent: '6.750.000 ₫', group: 'Khách thân thiết', joinDate: '03/03/2025', avatarBg: 'linear-gradient(135deg,#6366f1,#0ea5e9)' },
        { id: 1003, name: 'Lê Quốc C', email: 'c@gmail.com', phone: '0901 222 333', orders: 1, spent: '2.100.000 ₫', group: 'Khách mới', joinDate: '15/05/2026', avatarBg: 'linear-gradient(135deg,#f59e0b,#22c55e)' },
        { id: 1004, name: 'Phạm Thu D', email: 'd@gmail.com', phone: '0933 111 444', orders: 12, spent: '15.600.000 ₫', group: 'Khách thân thiết', joinDate: '20/06/2024', avatarBg: 'linear-gradient(135deg,#0ea5e9,#6366f1)' },
      ],
      reviews: [
        { id: 1, customer: 'Nguyễn Văn A', product: 'Gundam RX-78', stars: 5, content: 'Sản phẩm rất đẹp, đúng như mô tả!', date: '18/05/2026', status: 'delivered', statusLabel: 'Đã duyệt' },
        { id: 2, customer: 'Trần Thị B', product: 'Mô hình F1', stars: 3, content: 'Bình thường, màu sắc hơi nhạt...', date: '17/05/2026', status: 'pending', statusLabel: 'Chờ duyệt' },
        { id: 3, customer: 'Lê Quốc C', product: 'Dragon Ball', stars: 1, content: 'Giao hàng chậm, sản phẩm lỗi!', date: '16/05/2026', status: 'shipping', statusLabel: 'Chờ duyệt' },
      ],
    };
  },
  computed: {
    filteredCustomers() {
      return this.customers.filter(c => {
        const ms = !this.search || c.name.toLowerCase().includes(this.search.toLowerCase()) || c.email.includes(this.search);
        const mg = !this.filterGroup || c.group === this.filterGroup;
        return ms && mg;
      });
    },
  },
  methods: {
    openProfile(c) { this.selectedCustomer = c; this.showProfile = true; },
  },
};
</script>

<style scoped>
@import "/style_admin/customers.css";
</style>
