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
              <option>Nhân viên kho</option>
              <option>Nhân viên bán hàng</option>
            </select>
          </div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>Nhân viên</th>
              <th>Email</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Đăng nhập cuối</th>
              <th>Thao tác</th>
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
              <td><span class="role-badge" :class="'r-' + s.roleKey">{{ s.role }}</span></td>
              <td>
                <span class="status-pill" :class="s.active ? 's-delivered' : 's-cancelled'">
                  {{ s.active ? 'Đang hoạt động' : 'Bị khóa' }}
                </span>
              </td>
              <td>{{ s.lastLogin }}</td>
              <td>
                <div class="action-btns">
                  <button class="act-btn edit" @click="openModal('edit', s)" title="Sửa chi tiết"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                  <button class="act-btn" :class="s.active ? 'del' : 'edit'" @click="confirmToggleLock(s)" :title="s.active ? 'Khóa tài khoản' : 'Mở khóa tài khoản'">
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
              <select v-model="form.role">
                <option value="Nhân viên kho">Nhân viên kho</option>
                <option value="Nhân viên bán hàng">Nhân viên bán hàng</option>
                <option value="Super Admin">Super Admin</option>
              </select>
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
      staff: [
        { id: 1, name: 'Nguyễn Quốc Việt', email: 'admin@skyline.vn', phone: '0912 000 001', role: 'Super Admin', roleKey: 'super', active: true, lastLogin: '18/05/2026 18:30', avatarBg: 'linear-gradient(135deg,#D70018,#7c3aed)' },
        { id: 2, name: 'Trần Văn Kho', email: 'kho@skyline.vn', phone: '0912 000 002', role: 'Nhân viên kho', roleKey: 'warehouse', active: true, lastLogin: '18/05/2026 14:12', avatarBg: 'linear-gradient(135deg,#0ea5e9,#6366f1)' },
        { id: 3, name: 'Lê Thị Sales', email: 'sales@skyline.vn', phone: '0912 000 003', role: 'Nhân viên bán hàng', roleKey: 'sales', active: true, lastLogin: '17/05/2026 09:45', avatarBg: 'linear-gradient(135deg,#22c55e,#0ea5e9)' },
        { id: 4, name: 'Phạm Văn Bị Khóa', email: 'locked@skyline.vn', phone: '0912 000 004', role: 'Nhân viên kho', roleKey: 'warehouse', active: false, lastLogin: '01/03/2026', avatarBg: 'linear-gradient(135deg,#94a3b8,#64748b)' },
      ],
      activityLogs: [
        { id: 1, user: 'Admin Việt', action: 'Thay đổi giá sản phẩm Gundam RX-78', time: '5 phút trước', color: '#6366f1' },
        { id: 2, user: 'Trần Văn Kho', action: 'Cập nhật tồn kho: +50 Iron Man MK50', time: '22 phút trước', color: '#0ea5e9' },
        { id: 3, user: 'Lê Thị Sales', action: 'Xử lý đơn hàng #DH8821 → Đang giao', time: '1 giờ trước', color: '#22c55e' },
        { id: 4, user: 'Admin Việt', action: 'Tạo mã giảm giá SALE20', time: '2 giờ trước', color: '#f59e0b' },
        { id: 5, user: 'Lê Thị Sales', action: 'Huỷ đơn hàng #DH8817 theo yêu cầu KH', time: '3 giờ trước', color: '#D70018' },
        { id: 6, user: 'Trần Văn Kho', action: 'Thêm sản phẩm mới: Gundam SEED', time: '5 giờ trước', color: '#94a3b8' },
      ],
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
  methods: {
    getRoleKey(role) {
      if (role === 'Super Admin') return 'super';
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
          phone: staffMember.phone,
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
    saveStaff() {
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
        
        if (this.staff.some(s => s.email.toLowerCase() === this.form.email.toLowerCase())) {
          this.showToast('Địa chỉ email này đã được sử dụng!', 'error');
          return;
        }

        const newId = this.staff.length > 0 ? Math.max(...this.staff.map(s => s.id)) + 1 : 1;
        const gradients = [
          'linear-gradient(135deg, #D70018, #7c3aed)',
          'linear-gradient(135deg, #0ea5e9, #6366f1)',
          'linear-gradient(135deg, #22c55e, #0ea5e9)',
          'linear-gradient(135deg, #f59e0b, #22c55e)',
          'linear-gradient(135deg, #ec4899, #8b5cf6)',
        ];
        const randomBg = gradients[Math.floor(Math.random() * gradients.length)];

        this.staff.push({
          id: newId,
          name: this.form.name.trim(),
          email: this.form.email.trim(),
          phone: this.form.phone ? this.form.phone.trim() : 'Chưa cấu hình',
          role: this.form.role,
          roleKey: this.getRoleKey(this.form.role),
          active: true,
          lastLogin: 'Chưa đăng nhập',
          avatarBg: randomBg
        });

        this.addActivityLog('Admin Việt', `Tạo tài khoản nhân viên mới: ${this.form.name}`, '#10b981');
        this.showToast(`Đã tạo tài khoản cho "${this.form.name}" thành công!`, 'success');
      } else {
        const idx = this.staff.findIndex(s => s.id === this.form.id);
        if (idx !== -1) {
          this.staff[idx].name = this.form.name.trim();
          this.staff[idx].phone = this.form.phone ? this.form.phone.trim() : 'Chưa cấu hình';
          this.staff[idx].role = this.form.role;
          this.staff[idx].roleKey = this.getRoleKey(this.form.role);

          this.addActivityLog('Admin Việt', `Cập nhật thông tin nhân viên: ${this.form.name}`, '#6366f1');
          this.showToast(`Cập nhật thông tin nhân viên "${this.form.name}" thành công!`, 'info');
        }
      }
      this.showModal = false;
    },
    confirmToggleLock(staffMember) {
      if (staffMember.email === 'admin@skyline.vn') {
        this.showToast('Không thể tự khóa tài khoản Admin hệ thống!', 'error');
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
      }).then((result) => {
        if (result.isConfirmed) {
          staffMember.active = !staffMember.active;
          const logMsg = isLocking 
            ? `Khóa tài khoản nhân viên: ${staffMember.name}` 
            : `Mở khóa tài khoản nhân viên: ${staffMember.name}`;
          const logColor = isLocking ? '#D70018' : '#10b981';
          const toastType = isLocking ? 'danger' : 'success';
          const toastMsg = isLocking 
            ? `Đã khóa tài khoản của "${staffMember.name}"!`
            : `Đã mở khóa tài khoản của "${staffMember.name}"!`;

          this.addActivityLog('Admin Việt', logMsg, logColor);
          this.showToast(toastMsg, toastType);
        }
      });
    },
    addActivityLog(user, action, color = '#6366f1') {
      const newId = this.activityLogs.length > 0 ? Math.max(...this.activityLogs.map(l => l.id)) + 1 : 1;
      this.activityLogs.unshift({
        id: newId,
        user: user,
        action: action,
        time: 'Vừa xong',
        color: color
      });
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
@import "/style_admin/staff.css";
</style>
