<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Thông tin cá nhân</h1>
        <p class="page-sub">Quản lý hồ sơ và bảo mật tài khoản</p>
      </div>
    </div>

    <div class="profile-layout">
      <!-- Left: Avatar + Nav -->
      <div class="profile-sidebar-card card card-body">
        <div class="profile-avatar-section">
          <div class="profile-avatar-xl" :style="avatarStyle">
            <span v-if="!form.avatar && !avatarPreviewUrl">{{ form.name ? form.name.charAt(0).toUpperCase() : 'A' }}</span>
          </div>
          <input type="file" ref="avatarInput" style="display: none" @change="onAvatarSelected" accept="image/*" />
          <button class="avatar-upload-btn" @click="$refs.avatarInput.click()">
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
              <input type="text" v-model="form.name" required />
            </div>
            <div class="form-group">
              <label>Email <span class="req">*</span></label>
              <input type="email" v-model="form.email" required />
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
            <button style="margin-top: 10px;" class="btn-primary" @click="handleSave">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13"/><polyline points="7 3 7 8 15 8"/></svg>
        Lưu thay đổi
      </button>
        </div>

        <!-- Bảo mật -->
        <div class="card card-body" v-if="activeSection === 'security'">
          <h2 class="section-heading">Quản lý bảo mật</h2>
          <div class="form-grid">
            <div class="form-group span-2">
              <label>Mật khẩu hiện tại <span class="req">*</span></label>
              <input type="password" v-model="security.currentPassword" placeholder="••••••••" required />
            </div>
            <div class="form-group">
              <label>Mật khẩu mới <span class="req">*</span></label>
              <input type="password" v-model="security.newPassword" placeholder="••••••••" required />
            </div>
            <div class="form-group">
              <label>Xác nhận mật khẩu mới <span class="req">*</span></label>
              <input type="password" v-model="security.confirmPassword" placeholder="••••••••" required />
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
import axios from 'axios';

export default {
  name: 'AdminProfile',
  data() {
    return {
      activeSection: 'info',
      twofa: false,
      form: {
        name: '',
        email: '',
        phone: '',
        dob: '',
        address: '',
        bio: '',
        avatar: null,
      },
      security: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
      },
      selectedAvatarFile: null,
      avatarPreviewUrl: null,
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
  computed: {
    avatarStyle() {
      const img = this.avatarPreviewUrl || this.form.avatar;
      if (img) {
        return {
          backgroundImage: `url(${img})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          color: 'transparent'
        };
      }
      return {};
    }
  },
  mounted() {
    this.fetchProfile();
  },
  methods: {
    getConfig(contentType = 'application/json') {
      return {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
          'Content-Type': contentType
        }
      };
    },
    async fetchProfile() {
      try {
        const res = await axios.get('/api/thong-tin-ca-nhan/profile', this.getConfig());
        if (res.data.status) {
          const d = res.data.data;
          this.form.name = d.name || '';
          this.form.email = d.email || '';
          this.form.phone = d.phone || '';
          this.form.dob = d.dob || '';
          this.form.address = d.address || '';
          this.form.bio = d.bio || '';
          this.form.avatar = d.avatar || null;
        }
      } catch (err) {
        this.showToast('Lỗi tải thông tin cá nhân!', 'error');
      }
    },
    onAvatarSelected(e) {
      const file = e.target.files[0];
      if (file) {
        this.selectedAvatarFile = file;
        this.avatarPreviewUrl = URL.createObjectURL(file);
      }
    },
    async handleSave() {
      if (this.activeSection === 'info') {
        await this.saveProfileInfo();
      } else if (this.activeSection === 'security') {
        await this.saveSecurity();
      } else {
        this.showToast('Lưu cấu hình thông báo thành công!', 'success');
      }
    },
    async saveProfileInfo() {
      if (!this.form.name.trim()) {
        this.showToast('Họ và tên không được để trống!', 'error');
        return;
      }
      if (!this.form.email.trim()) {
        this.showToast('Email không được để trống!', 'error');
        return;
      }

      try {
        const formData = new FormData();
        formData.append('ho_ten', this.form.name.trim());
        formData.append('email', this.form.email.trim());
        formData.append('so_dien_thoai', this.form.phone ? this.form.phone.trim() : '');
        formData.append('ngay_sinh', this.form.dob || '');
        formData.append('dia_chi', this.form.address || '');
        formData.append('gioi_thieu', this.form.bio || '');
        
        if (this.selectedAvatarFile) {
          formData.append('avatar', this.selectedAvatarFile);
        }

        const res = await axios.post(
          '/api/thong-tin-ca-nhan/update',
          formData,
          this.getConfig('multipart/form-data')
        );

        if (res.data.status) {
          this.showToast('Cập nhật thông tin cá nhân thành công!', 'success');
          
          // Cập nhật lại localStorage để hiển thị đồng bộ ở Topbar/Sidebar
          localStorage.setItem('ho_ten', this.form.name.trim());
          localStorage.setItem('email', this.form.email.trim());
          if (res.data.data.avatar) {
            this.form.avatar = res.data.data.avatar;
            localStorage.setItem('anh_dai_dien', res.data.data.avatar);
          }
          this.avatarPreviewUrl = null;
          this.selectedAvatarFile = null;

          // Đợi 1 chút để Toast hiển thị rồi reload trang để đồng bộ Topbar
          window.dispatchEvent(new Event('profileUpdated'));
          setTimeout(() => {
            window.location.reload();
          }, 800);
        } else {
          this.showToast(res.data.message || 'Lỗi cập nhật thông tin!', 'error');
        }
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật hồ sơ!';
        this.showToast(errMsg, 'error');
      }
    },
    async saveSecurity() {
      if (!this.security.currentPassword) {
        this.showToast('Vui lòng nhập mật khẩu hiện tại!', 'error');
        return;
      }
      if (!this.security.newPassword) {
        this.showToast('Vui lòng nhập mật khẩu mới!', 'error');
        return;
      }
      if (this.security.newPassword.length < 6) {
        this.showToast('Mật khẩu mới phải từ 6 ký tự trở lên!', 'error');
        return;
      }
      if (this.security.newPassword !== this.security.confirmPassword) {
        this.showToast('Xác nhận mật khẩu mới không khớp!', 'error');
        return;
      }

      try {
        const payload = {
          current_password: this.security.currentPassword,
          new_password: this.security.newPassword,
          confirm_password: this.security.confirmPassword,
        };

        const res = await axios.post(
          '/api/thong-tin-ca-nhan/update-password',
          payload,
          this.getConfig()
        );

        if (res.data.status) {
          this.showToast('Thay đổi mật khẩu thành công!', 'success');
          this.security.currentPassword = '';
          this.security.newPassword = '';
          this.security.confirmPassword = '';
        } else {
          this.showToast(res.data.message || 'Lỗi thay đổi mật khẩu!', 'error');
        }
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Có lỗi xảy ra khi đổi mật khẩu!';
        this.showToast(errMsg, 'error');
      }
    },
    showToast(message, type = 'success') {
      if (type === 'success') {
        this.$toast.success(message);
      } else if (type === 'danger' || type === 'error') {
        this.$toast.error(message);
      } else if (type === 'warning') {
        this.$toast.warning(message);
      } else {
        this.$toast.info(message);
      }
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/profile.css";
</style>
