<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Nhân viên & Phân quyền</h1>
        <p class="page-sub">Quản lý tài khoản và vai trò trong hệ thống</p>
      </div>
      <button class="btn-primary" @click="openModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm nhân viên
      </button>
    </div>

    <div class="staff-grid">
      <!-- Staff list -->
      <div class="card" style="flex:1">
        <div class="table-toolbar">
          <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input v-model="search" type="text" placeholder="Tìm theo tên, email, SĐT..." />
          </div>
          <div class="toolbar-right">
            <select class="sel" v-model="filterRole">
              <option value="">Tất cả vai trò</option>
              <option>Super Admin</option>
              <option>Quản lý</option>
              <option>Nhân viên kho</option>
              <option>Nhân viên bán hàng</option>
            </select>
          </div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th style="color:black">Nhân viên</th>
              <th style="color:black">Email</th>
              <th style="color:black">Vai trò</th>
              <th style="color:black">Trạng thái</th>
              <th style="color:black">Đăng nhập cuối</th>
              <th style="color:black">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredStaff" :key="s.id">
              <td>
                <div class="customer-cell">
                  <div class="staff-avatar" :style="{ background: s.avatarBg }">{{ s.name[0] }}</div>
                  <div>
                    <p class="cus-name">{{ s.name }}</p>
                    <p class="cus-phone">{{ s.phone }}</p>
                  </div>
                </div>
              </td>
              <td>{{ s.email }}</td>
              <td class="whitespace-nowrap"><span class="role-badge" :class="'r-' + s.roleKey">{{ s.role }}</span></td>
              <td class="whitespace-nowrap">
                <span class="status-pill" :class="s.active ? 's-delivered' : 's-cancelled'">
                  {{ s.active ? 'Đang hoạt động' : 'Bị khóa' }}
                </span>
              </td>
              <td class="whitespace-nowrap">{{ s.lastLogin }}</td>
              <td>
                <div class="action-btns">
                  <button class="act-btn edit" @click="openModal('edit', s)" title="Sửa chi tiết"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                  <button 
                    class="act-btn" 
                    :class="s.active ? 'del' : 'edit'" 
                    @click="confirmToggleLock(s)" 
                    :title="isCurrentUser(s.email) ? 'Không thể tự khóa tài khoản của chính mình' : (s.active ? 'Khóa tài khoản' : 'Mở khóa tài khoản')"
                    :style="isCurrentUser(s.email) ? 'opacity: 0.4; cursor: not-allowed;' : ''"
                  >
                    <!-- Locked Padlock (Closed) if active -->
                    <svg v-if="s.active" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <!-- Unlocked Padlock (Open) if locked -->
                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 019.9-1"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredStaff.length === 0">
              <td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">Không tìm thấy nhân viên phù hợp.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Activity logs -->
      <div class="card log-card">
        <div class="card-header-bar">
          <span class="card-title-sm">Nhật ký hoạt động</span>
        </div>
        <div class="log-list">
          <div class="log-item" v-for="log in activityLogs" :key="log.id">
            <div class="log-dot" :style="{ background: log.color }"></div>
            <div class="log-body">
              <p class="log-user">{{ log.user }}</p>
              <p class="log-action">{{ log.action }}</p>
              <p class="log-time">{{ log.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit staff modal -->
    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2>{{ modalMode === 'add' ? 'Thêm nhân viên mới' : 'Cập nhật thông tin nhân viên' }}</h2>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group span-2">
              <label>Họ và tên <span class="req">*</span></label>
              <input type="text" v-model="form.name" placeholder="Nguyễn Văn A" required />
            </div>
            <div class="form-group">
              <label>Email <span class="req">*</span></label>
              <input type="email" v-model="form.email" :disabled="modalMode === 'edit'" placeholder="nhanvien@company.com" required />
            </div>
            <div class="form-group">
              <label>Số điện thoại</label>
              <input type="text" v-model="form.phone" placeholder="0912 345 678" />
            </div>
            <div class="form-group span-2">
              <label>Vai trò <span class="req">*</span></label>
              <select v-model="form.role" :disabled="modalMode === 'edit' && isCurrentUser(form.email)">
                <option value="Quản lý">Quản lý</option>
                <option value="Nhân viên kho">Nhân viên kho</option>
                <option value="Nhân viên bán hàng">Nhân viên bán hàng</option>
                <option value="Super Admin">Super Admin</option>
              </select>
              <span v-if="modalMode === 'edit' && isCurrentUser(form.email)" style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                * Bạn không thể tự thay đổi vai trò của chính mình.
              </span>
            </div>
            
            <template v-if="modalMode === 'add'">
              <div class="form-group">
                <label>Mật khẩu tạm thời <span class="req">*</span></label>
                <input type="password" v-model="form.password" placeholder="••••••••" required />
              </div>
              <div class="form-group">
                <label>Xác nhận mật khẩu <span class="req">*</span></label>
                <input type="password" v-model="form.confirmPassword" placeholder="••••••••" required />
              </div>
            </template>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showModal = false">Hủy</button>
          <button class="btn-primary" @click="saveStaff">{{ modalMode === 'add' ? 'Tạo tài khoản' : 'Lưu thay đổi' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminStaff',
  data() {
    return {
      search: '',
      filterRole: '',
      showModal: false,
      modalMode: 'add',
      form: {
        id: null,
        name: '',
        email: '',
        phone: '',
        role: 'Nhân viên kho',
        password: '',
        confirmPassword: '',
      },
      staff: [],
      activityLogs: [],
    };
  },
  computed: {
    filteredStaff() {
      return this.staff.filter(s => {
        const searchLower = this.search.toLowerCase();
        const ms = !this.search || 
          s.name.toLowerCase().includes(searchLower) ||
          s.email.toLowerCase().includes(searchLower) ||
          (s.phone && s.phone.includes(searchLower));
        const mr = !this.filterRole || s.role === this.filterRole;
        return ms && mr;
      });
    },
  },
  mounted() {
    this.fetchStaffData();
    this.initPusher();
  },
  methods: {
    initPusher() {
      if (window.Pusher) {
        this.setupPusher();
      } else {
        const script = document.createElement('script');
        script.src = 'https://js.pusher.com/8.4.0/pusher.min.js';
        script.onload = () => {
          this.setupPusher();
        };
        document.head.appendChild(script);
      }
    },
    setupPusher() {
      try {
        const pusher = new window.Pusher('794a0b225fca675fc9a7', {
          cluster: 'ap1'
        });

        const channel = pusher.subscribe('my-channel');
        channel.bind('my-event', (data) => {
          this.activityLogs.unshift(data);
          if (this.activityLogs.length > 30) {
            this.activityLogs.pop();
          }
        });
      } catch (err) {
        console.error('Error setting up Pusher:', err);
      }
    },
    getConfig() {
      return {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
        }
      };
    },
    async fetchStaffData() {
      try {
        const res = await axios.get('/api/quan-tri/nhan-vien/data', this.getConfig());
        if (res.data.status) {
          this.staff = res.data.data.staff || [];
          this.activityLogs = res.data.data.logs || [];
        }
      } catch (err) {
        this.showToast('Lỗi tải dữ liệu nhân viên!', 'error');
      }
    },
    getRoleKey(role) {
      if (role === 'Super Admin') return 'super';
      if (role === 'Quản lý') return 'manager';
      if (role === 'Nhân viên kho') return 'warehouse';
      if (role === 'Nhân viên bán hàng') return 'sales';
      return 'sales';
    },
    openModal(mode, staffMember = null) {
      this.modalMode = mode;
      if (staffMember) {
        this.form = {
          id: staffMember.id,
          name: staffMember.name,
          email: staffMember.email,
          phone: staffMember.phone === 'Chưa cấu hình' ? '' : staffMember.phone,
          role: staffMember.role,
          password: '',
          confirmPassword: '',
        };
      } else {
        this.form = {
          id: null,
          name: '',
          email: '',
          phone: '',
          role: 'Nhân viên kho',
          password: '',
          confirmPassword: '',
        };
      }
      this.showModal = true;
    },
    async saveStaff() {
      if (!this.form.name || !this.form.email) {
        this.showToast('Vui lòng nhập đầy đủ các trường thông tin bắt buộc!', 'error');
        return;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.form.email)) {
        this.showToast('Địa chỉ email không hợp lệ!', 'error');
        return;
      }

      if (this.modalMode === 'add') {
        if (!this.form.password) {
          this.showToast('Vui lòng nhập mật khẩu tạm thời!', 'error');
          return;
        }
        if (this.form.password !== this.form.confirmPassword) {
          this.showToast('Xác nhận mật khẩu không khớp!', 'error');
          return;
        }
        
        try {
          const payload = {
            ho_ten: this.form.name.trim(),
            email: this.form.email.trim(),
            so_dien_thoai: this.form.phone ? this.form.phone.trim() : null,
            vai_tro: this.form.role,
            mat_khau: this.form.password,
          };
          const res = await axios.post('/api/quan-tri/nhan-vien/create', payload, this.getConfig());
          if (res.data.status) {
            this.showToast(`Đã tạo tài khoản cho "${this.form.name}" thành công!`, 'success');
            this.fetchStaffData();
            this.showModal = false;
          } else {
            this.showToast(res.data.message || 'Lỗi tạo tài khoản nhân viên!', 'error');
          }
        } catch (err) {
          const errMsg = err.response?.data?.message || 'Có lỗi xảy ra khi tạo nhân viên!';
          this.showToast(errMsg, 'error');
        }
      } else {
        try {
          const payload = {
            id: this.form.id,
            ho_ten: this.form.name.trim(),
            so_dien_thoai: this.form.phone ? this.form.phone.trim() : null,
            vai_tro: this.form.role,
          };
          const res = await axios.post('/api/quan-tri/nhan-vien/update', payload, this.getConfig());
          if (res.data.status) {
            this.showToast(`Cập nhật thông tin nhân viên "${this.form.name}" thành công!`, 'info');
            this.fetchStaffData();
            this.showModal = false;
          } else {
            this.showToast(res.data.message || 'Lỗi cập nhật nhân viên!', 'error');
          }
        } catch (err) {
          const errMsg = err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật nhân viên!';
          this.showToast(errMsg, 'error');
        }
      }
    },
    confirmToggleLock(staffMember) {
      if (this.isCurrentUser(staffMember.email)) {
        this.showToast('Không thể tự khóa tài khoản của chính mình!', 'error');
        return;
      }

      const isLocking = staffMember.active;
      const titleText = isLocking ? 'KHÓA TÀI KHOẢN NHÂN VIÊN?' : 'MỞ KHÓA TÀI KHOẢN?';
      const htmlText = isLocking 
        ? `Bạn có chắc muốn khóa tài khoản của <b style="color: #D70018;">"${staffMember.name}"</b>?<br><span style="font-size: 13px; color: #64748b;">Nhân viên bị khóa sẽ không thể truy cập bất kỳ khu vực nào của hệ thống.</span>`
        : `Bạn có muốn mở khóa tài khoản cho <b style="color: #10b981;">"${staffMember.name}"</b>?<br><span style="font-size: 13px; color: #64748b;">Nhân viên sẽ được khôi phục toàn bộ quyền truy cập.</span>`;
      
      const confirmColor = isLocking ? '#D70018' : '#10b981';
      const confirmButtonText = isLocking ? 'Xác nhận khóa' : 'Mở khóa ngay';

      window.Swal.fire({
        title: titleText,
        html: htmlText,
        icon: isLocking ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#f1f5f9',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Hủy bỏ',
        background: '#ffffff',
        color: '#0f172a',
        customClass: {
          popup: 'swal-premium-popup',
          title: 'swal-premium-title',
          htmlContainer: 'swal-premium-text',
          confirmButton: 'btn-swal-confirm',
          cancelButton: 'btn-swal-cancel'
        },
        buttonsStyling: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await axios.post(`/api/quan-tri/nhan-vien/${staffMember.id}/toggle-lock`, {}, this.getConfig());
            if (res.data.status) {
              const toastMsg = isLocking 
                ? `Đã khóa tài khoản của "${staffMember.name}"!`
                : `Đã mở khóa tài khoản của "${staffMember.name}"!`;
              const toastType = isLocking ? 'danger' : 'success';
              this.showToast(toastMsg, toastType);
              this.fetchStaffData();
            } else {
              this.showToast(res.data.message || 'Lỗi thay đổi trạng thái tài khoản!', 'error');
            }
          } catch (err) {
            this.showToast('Có lỗi xảy ra khi thay đổi trạng thái!', 'error');
          }
        }
      });
    },
    isCurrentUser(email) {
      const currentUserEmail = localStorage.getItem('email') || 'admin@skyline.vn';
      return email === currentUserEmail;
    },
    showToast(message, type = "success") {
      if (type === "success") {
        this.$toast.success(message);
      } else if (type === "danger" || type === "error") {
        this.$toast.error(message);
      } else if (type === "warning") {
        this.$toast.warning(message);
      } else {
        this.$toast.info(message);
      }
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/staff.css";
</style>
