<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Thông tin cá nhân</h1>
        <p class="page-sub">Quản lý hồ sơ và bảo mật tài khoản</p>
      </div>
      <button class="btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13"/><polyline points="7 3 7 8 15 8"/></svg>
        Lưu thay đổi
      </button>
    </div>

    <div class="profile-layout">
      <!-- Left: Avatar + Nav -->
      <div class="profile-sidebar-card card card-body">
        <div class="profile-avatar-section">
          <div class="profile-avatar-xl">A</div>
          <button class="avatar-upload-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Đổi ảnh
          </button>
        </div>
        <div class="profile-nav">
          <button v-for="n in navItems" :key="n.key"
            class="profile-nav-item" :class="{ active: activeSection === n.key }"
            @click="activeSection = n.key">
            <span v-html="n.icon"></span>
            {{ n.label }}
          </button>
        </div>
      </div>

      <!-- Right: Content -->
      <div class="profile-content">
        <!-- Thông tin cá nhân -->
        <div class="card card-body" v-if="activeSection === 'info'">
          <h2 class="section-heading">Thông tin cá nhân</h2>
          <div class="form-grid">
            <div class="form-group">
              <label>Họ và tên <span class="req">*</span></label>
              <input type="text" v-model="form.name" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" v-model="form.email" />
            </div>
            <div class="form-group">
              <label>Số điện thoại</label>
              <input type="text" v-model="form.phone" />
            </div>
            <div class="form-group">
              <label>Ngày sinh</label>
              <input type="date" v-model="form.dob" />
            </div>
            <div class="form-group span-2">
              <label>Địa chỉ</label>
              <input type="text" v-model="form.address" />
            </div>
            <div class="form-group span-2">
              <label>Giới thiệu bản thân</label>
              <textarea v-model="form.bio" rows="3" placeholder="Nhập thông tin giới thiệu..."></textarea>
            </div>
          </div>
        </div>

        <!-- Bảo mật -->
        <div class="card card-body" v-if="activeSection === 'security'">
          <h2 class="section-heading">Quản lý bảo mật</h2>
          <div class="form-grid">
            <div class="form-group span-2">
              <label>Mật khẩu hiện tại <span class="req">*</span></label>
              <input type="password" placeholder="••••••••" />
            </div>
            <div class="form-group">
              <label>Mật khẩu mới <span class="req">*</span></label>
              <input type="password" placeholder="••••••••" />
            </div>
            <div class="form-group">
              <label>Xác nhận mật khẩu mới</label>
              <input type="password" placeholder="••••••••" />
            </div>
          </div>
          <div class="security-divider"></div>
          <h3 class="subsection-title">Xác thực 2 lớp (2FA)</h3>
          <div class="twofa-row">
            <div>
              <p class="twofa-label">Xác thực qua ứng dụng (Google Authenticator)</p>
              <p class="twofa-desc">Bảo vệ tài khoản bằng mã OTP mỗi lần đăng nhập</p>
            </div>
            <button class="toggle" :class="{ on: twofa }" @click="twofa = !twofa"></button>
          </div>
        </div>

        <!-- Thông báo -->
        <div class="card card-body" v-if="activeSection === 'notifications'">
          <h2 class="section-heading">Cấu hình thông báo</h2>
          <div class="notif-list">
            <div class="notif-item" v-for="n in notifSettings" :key="n.key">
              <div class="notif-info">
                <p class="notif-label">{{ n.label }}</p>
                <p class="notif-desc">{{ n.desc }}</p>
              </div>
              <div class="notif-toggles">
                <div class="notif-toggle-wrap">
                  <span class="notif-toggle-label">Email</span>
                  <button class="toggle" :class="{ on: n.email }" @click="n.email = !n.email"></button>
                </div>
                <div class="notif-toggle-wrap">
                  <span class="notif-toggle-label">In-app</span>
                  <button class="toggle" :class="{ on: n.inapp }" @click="n.inapp = !n.inapp"></button>
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
export default {
  name: 'AdminProfile',
  data() {
    return {
      activeSection: 'info',
      twofa: false,
      form: {
        name: 'Admin Việt',
        email: 'admin@skyline.vn',
        phone: '0912 345 678',
        dob: '1995-06-15',
        address: '123 Lê Lợi, Q.1, TP.HCM',
        bio: 'Quản trị viên hệ thống Skyline Models.',
      },
      navItems: [
        { key: 'info', label: 'Thông tin cá nhân', icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>` },
        { key: 'security', label: 'Bảo mật', icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>` },
        { key: 'notifications', label: 'Thông báo', icon: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>` },
      ],
      notifSettings: [
        { key: 'new_order', label: 'Đơn hàng mới', desc: 'Nhận thông báo khi có đơn hàng mới được đặt', email: true, inapp: true },
        { key: 'low_stock', label: 'Sản phẩm sắp hết hàng', desc: 'Cảnh báo khi tồn kho dưới ngưỡng cho phép', email: true, inapp: false },
        { key: 'new_review', label: 'Đánh giá mới', desc: 'Thông báo khi khách hàng để lại đánh giá mới', email: false, inapp: true },
        { key: 'cancelled_order', label: 'Đơn hàng bị huỷ', desc: 'Cảnh báo khi có đơn hàng bị huỷ', email: true, inapp: true },
      ],
    };
  },
};
</script>

<style scoped>
@import "/style_admin/profile.css";
</style>
