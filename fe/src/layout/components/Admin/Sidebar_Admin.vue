<template>
  <aside class="admin-sidebar" :class="{ collapsed: isCollapsed }">
    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-icon">
        <svg
          width="22"
          height="22"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <polyline points="5 12 12 5 19 12" />
          <polyline points="5 19 12 12 19 19" />
        </svg>
      </div>
      <span class="logo-text">Mô Hình BALAB</span>
      <button class="collapse-btn" @click="toggleSidebar">
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
      <div class="user-avatar" :style="avatarStyle">
        <span v-if="!userAvatar">{{
          userName ? userName.charAt(0).toUpperCase() : "A"
        }}</span>
        <div class="user-status"></div>
      </div>
      <div class="user-info">
        <p class="user-name">{{ userName }}</p>
        <p class="user-role">{{ userRole }}</p>
      </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav">
      <!-- Dashboard -->
      <div class="nav-section-label">Tổng quan</div>
      <router-link
        to="/nhan-vien/dashboard"
        class="nav-item"
        exact-active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>
        </div>
        <span class="nav-label">Dashboard</span>
        <div class="nav-tooltip">Dashboard</div>
      </router-link>

      <router-link
        to="/nhan-vien/analytics"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <line x1="18" y1="20" x2="18" y2="10" />
            <line x1="12" y1="20" x2="12" y2="4" />
            <line x1="6" y1="20" x2="6" y2="14" />
          </svg>
        </div>
        <span class="nav-label">Thống kê</span>
        <div class="nav-tooltip">Thống kê</div>
      </router-link>

      <!-- Quản lý -->
      <div class="nav-section-label">Quản lý</div>

      <router-link
        to="/nhan-vien/products"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"
            />
            <line x1="7" y1="7" x2="7.01" y2="7" />
          </svg>
        </div>
        <span class="nav-label">Sản phẩm</span>
        <div class="nav-badge" v-if="sidebarStats.products_count > 0">{{ sidebarStats.products_count }}</div>
        <div class="nav-tooltip">Sản phẩm</div>
      </router-link>

      <router-link
        to="/nhan-vien/orders"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 01-8 0" />
          </svg>
        </div>
        <span class="nav-label">Đơn hàng</span>
        <div class="nav-badge nav-badge-red" v-if="sidebarStats.pending_orders_count > 0">{{ sidebarStats.pending_orders_count }}</div>
        <div class="nav-tooltip">Đơn hàng</div>
      </router-link>

      <router-link
        to="/nhan-vien/categories"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"
            />
          </svg>
        </div>
        <span class="nav-label">Danh mục</span>
        <div class="nav-tooltip">Danh mục</div>
      </router-link>

      <div
        class="nav-item"
        :class="{ expanded: expandedMenus.includes('customers') }"
        @click="toggleMenu('customers')"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 00-3-3.87" />
            <path d="M16 3.13a4 4 0 010 7.75" />
          </svg>
        </div>
        <span class="nav-label">Khách hàng</span>
        <svg
          class="nav-chevron"
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
        <div class="nav-tooltip">Khách hàng</div>
      </div>
      <div class="nav-submenu" v-show="expandedMenus.includes('customers')">
        <router-link
          to="/nhan-vien/customers"
          class="nav-subitem"
          active-class="active"
          >Danh sách KH</router-link
        >
        <router-link
          to="/nhan-vien/customers/groups"
          class="nav-subitem"
          active-class="active"
          >Nhóm KH</router-link
        >
        <router-link
          to="/nhan-vien/customers/reviews"
          class="nav-subitem"
          active-class="active"
          >Đánh giá</router-link
        >
      </div>

      <!-- Khuyến mãi -->
      <div class="nav-section-label">Marketing</div>

      <router-link
        to="/nhan-vien/promotions"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"
            />
            <line x1="7" y1="7" x2="7.01" y2="7" />
          </svg>
        </div>
        <span class="nav-label">Khuyến mãi</span>
        <div class="nav-tooltip">Khuyến mãi</div>
      </router-link>

      <!-- Nội dung -->
      <div class="nav-section-label">Nội dung</div>

      <router-link
        to="/nhan-vien/banners"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <polyline points="21 15 16 10 5 21" />
          </svg>
        </div>
        <span class="nav-label">Banner</span>
        <div class="nav-tooltip">Banner</div>
      </router-link>

      <router-link to="/nhan-vien/blog" class="nav-item" active-class="active">
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
        </div>
        <span class="nav-label">Bài viết</span>
        <div class="nav-tooltip">Bài viết</div>
      </router-link>

      <!-- Hệ thống -->
      <div class="nav-section-label">Hệ thống</div>

      <router-link to="/nhan-vien/staff" class="nav-item" active-class="active">
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>
        <span class="nav-label">Nhân viên</span>
        <div class="nav-tooltip">Nhân viên</div>
      </router-link>

      <!-- <router-link to="/nhan-vien/profile" class="nav-item" active-class="active">
        <div class="nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
            <circle cx="12" cy="7" r="4" />
            <path d="M16 3.13a4 4 0 010 7.75" />
          </svg>
        </div>
        <span class="nav-label">Hồ sơ cá nhân</span>
        <div class="nav-tooltip">Hồ sơ cá nhân</div>
      </router-link> -->

      <router-link
        to="/nhan-vien/settings"
        class="nav-item"
        active-class="active"
      >
        <div class="nav-icon">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <circle cx="12" cy="12" r="3" />
            <path
              d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"
            />
          </svg>
        </div>
        <span class="nav-label">Cài đặt</span>
        <div class="nav-tooltip">Cài đặt</div>
      </router-link>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
      <button class="logout-btn" @click="handleLogout">
        <div class="nav-icon">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
        </div>
        <span class="nav-label">Đăng xuất</span>
        <div class="nav-tooltip">Đăng xuất</div>
      </button>
    </div>

    <!-- Modal Xác nhận Đăng xuất -->
    <div
      class="logout-modal-backdrop"
      v-if="showLogoutModal"
      @click.self="showLogoutModal = false"
    >
      <div class="logout-modal">
        <div class="modal-icon">
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
        </div>
        <h3>Xác nhận đăng xuất</h3>
        <p>
          Bạn có chắc chắn muốn đăng xuất khỏi hệ thống quản trị Mô Hình BALAB?
        </p>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showLogoutModal = false">
            Ở lại trang
          </button>
          <button class="btn-confirm" @click="confirmLogout">Đăng xuất</button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script>
import axios from "axios";

export default {
  name: "Sidebar_Admin",
  data() {
    return {
      isCollapsed: false,
      expandedMenus: [],
      userName: "Admin Việt",
      userRole: "Super Admin",
      userAvatar: "",
      showLogoutModal: false,
      sidebarStats: {
        products_count: 0,
        pending_orders_count: 0,
        total_orders_count: 0,
      },
    };
  },
  computed: {
    avatarStyle() {
      if (this.userAvatar) {
        const imgUrl = this.userAvatar.startsWith("http")
          ? this.userAvatar
          : "http://127.0.0.1:8000/" + this.userAvatar;
        return {
          backgroundImage: `url(${imgUrl})`,
          backgroundSize: "cover",
          backgroundPosition: "center",
          color: "transparent",
        };
      }
      return {};
    },
  },
  mounted() {
    this.loadUserData();
    this.fetchSidebarStats();
    window.addEventListener("profileUpdated", this.loadUserData);
    window.addEventListener("orderStatusUpdated", this.fetchSidebarStats);
  },
  beforeUnmount() {
    window.removeEventListener("profileUpdated", this.loadUserData);
    window.removeEventListener("orderStatusUpdated", this.fetchSidebarStats);
  },
  methods: {
    loadUserData() {
      this.userName = localStorage.getItem("ho_ten") || "Admin Việt";
      this.userRole = localStorage.getItem("ten_vai_tro") || "Super Admin";
      this.userAvatar = localStorage.getItem("anh_dai_dien") || "";
    },
    async fetchSidebarStats() {
      try {
        const token = localStorage.getItem("token_admin");
        if (!token) return;
        const response = await axios.get("http://127.0.0.1:8000/api/quan-ly/sidebar-stats", {
          headers: {
            Authorization: "Bearer " + token,
          },
        });
        if (response.data.status === 1) {
          this.sidebarStats = response.data.data;
        }
      } catch (err) {
        console.error("Error fetching sidebar stats:", err);
      }
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
    },
    toggleMenu(key) {
      const idx = this.expandedMenus.indexOf(key);
      if (idx > -1) {
        this.expandedMenus.splice(idx, 1);
      } else {
        this.expandedMenus.push(key);
      }
    },
    handleLogout() {
      this.showLogoutModal = true;
    },
    async confirmLogout() {
      this.showLogoutModal = false;
      try {
        const token = localStorage.getItem("token_admin");
        if (token) {
          await axios.post(
            "http://127.0.0.1:8000/api/dang-xuat",
            {},
            {
              headers: {
                Authorization: "Bearer " + token,
              },
            },
          );
        }
      } catch (err) {
        console.error("Error logging out on backend:", err);
      } finally {
        // Clear local storage credentials
        localStorage.removeItem("token_admin");
        localStorage.removeItem("ho_ten");
        localStorage.removeItem("ten_vai_tro");
        localStorage.removeItem("anh_dai_dien");
        localStorage.removeItem("email");

        // Redirect to login
        this.$router.push("/login");
      }
    },
  },
};
</script>

<style scoped>
/* ─── SIDEBAR ─── */
.admin-sidebar {
  width: 260px;
  height: 100vh;
  position: sticky;
  top: 0;
  background: #0f172a;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow: hidden;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-right: 1px solid rgba(255, 255, 255, 0.06);
  z-index: 200;
}
.admin-sidebar.collapsed {
  width: 72px;
}

/* Logo */
.sidebar-logo {
  height: 68px;
  display: flex;
  align-items: center;
  padding: 0 18px;
  gap: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}
.logo-icon {
  width: 38px;
  height: 38px;
  background: var(--red, #d70018);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(215, 0, 24, 0.45);
}
.logo-text {
  font-family: "Barlow Condensed", sans-serif;
  font-weight: 900;
  font-size: 18px;
  letter-spacing: 0.1em;
  color: #fff;
  white-space: nowrap;
  flex: 1;
  overflow: hidden;
  transition:
    opacity 0.2s,
    width 0.3s;
}
.collapse-btn {
  width: 28px;
  height: 28px;
  background: rgba(255, 255, 255, 0.07);
  border: none;
  border-radius: 7px;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition:
    background 0.2s,
    color 0.2s,
    transform 0.3s;
}
.collapse-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}
.admin-sidebar.collapsed .collapse-btn {
  transform: rotate(180deg);
}

/* Khi thu nhỏ: ẩn logo, căn giữa nút collapse để không bị overflow:hidden cắt mất */
.admin-sidebar.collapsed .sidebar-logo {
  padding: 0;
  justify-content: center;
  align-items: center;
  position: relative;
  z-index: 1000;
  gap: 0;
}
.admin-sidebar.collapsed .logo-icon {
  display: none;
}
.admin-sidebar.collapsed .collapse-btn {
  transform: rotate(180deg);
  color: #fff;
  background: rgba(255, 255, 255, 0.12);
  width: 36px;
  height: 36px;
}

/* User info */
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}
.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #d70018, #7c3aed);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  flex-shrink: 0;
  position: relative;
}
.user-status {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  background: #22c55e;
  border-radius: 50%;
  border: 2px solid #0f172a;
}
.user-info {
  overflow: hidden;
  transition: opacity 0.2s;
}
.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-role {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
  white-space: nowrap;
  margin-top: 1px;
}

/* Hide text when collapsed */
.admin-sidebar.collapsed .logo-text,
.admin-sidebar.collapsed .user-info,
.admin-sidebar.collapsed .nav-label,
.admin-sidebar.collapsed .nav-badge,
.admin-sidebar.collapsed .nav-chevron,
.admin-sidebar.collapsed .nav-section-label {
  opacity: 0;
  width: 0;
  margin: 0;
  padding: 0;
  overflow: hidden;
  pointer-events: none;
}

/* Nav */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 12px 10px;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
}
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}
.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 99px;
}

.nav-section-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.25);
  padding: 16px 8px 6px;
  white-space: nowrap;
  overflow: hidden;
  transition:
    opacity 0.2s,
    height 0.3s;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 8px;
  height: 44px;
  border-radius: 10px;
  cursor: pointer;
  text-decoration: none;
  color: rgba(255, 255, 255, 0.55);
  transition:
    background 0.2s,
    color 0.2s;
  position: relative;
  white-space: nowrap;
  margin-bottom: 2px;
  user-select: none;
}
.nav-item:hover {
  background: rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.85);
}
.nav-item.active {
  background: rgba(215, 0, 24, 0.18);
  color: #fff;
}
.nav-item.active .nav-icon {
  color: #d70018;
}
.nav-item.active::before {
  content: "";
  position: absolute;
  left: 0;
  top: 8px;
  bottom: 8px;
  width: 3px;
  background: #d70018;
  border-radius: 0 3px 3px 0;
}

.nav-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 8px;
  transition: background 0.2s;
}
.nav-item:hover .nav-icon {
  background: rgba(255, 255, 255, 0.06);
}

.nav-label {
  flex: 1;
  font-size: 13.5px;
  font-weight: 500;
  transition: opacity 0.2s;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 99px;
  background: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.7);
  flex-shrink: 0;
  transition: opacity 0.2s;
}
.nav-badge-red {
  background: rgba(215, 0, 24, 0.3);
  color: #ff6b7a;
}

.nav-chevron {
  flex-shrink: 0;
  transition:
    transform 0.3s,
    opacity 0.2s;
  color: rgba(255, 255, 255, 0.3);
}
.nav-item.expanded .nav-chevron {
  transform: rotate(180deg);
}

/* Tooltip (shown when collapsed) */
.nav-tooltip {
  display: none;
  position: absolute;
  left: calc(100% + 12px);
  top: 50%;
  transform: translateY(-50%);
  background: #1e293b;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 7px;
  white-space: nowrap;
  pointer-events: none;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.08);
  z-index: 999;
}
.nav-tooltip::before {
  content: "";
  position: absolute;
  right: 100%;
  top: 50%;
  transform: translateY(-50%);
  border: 5px solid transparent;
  border-right-color: #1e293b;
}
.admin-sidebar.collapsed .nav-item:hover .nav-tooltip {
  display: block;
}

/* Submenu */
.nav-submenu {
  padding: 2px 0 4px 16px;
  overflow: hidden;
}
.nav-subitem {
  display: block;
  padding: 8px 12px 8px 28px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.45);
  text-decoration: none;
  border-radius: 8px;
  transition:
    color 0.2s,
    background 0.2s;
  white-space: nowrap;
  position: relative;
  margin-bottom: 1px;
}
.nav-subitem::before {
  content: "";
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  transition: background 0.2s;
}
.nav-subitem:hover {
  color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.05);
}
.nav-subitem.active {
  color: #fff;
  background: rgba(215, 0, 24, 0.15);
}
.nav-subitem.active::before {
  background: #d70018;
}

/* Footer */
.sidebar-footer {
  flex-shrink: 0;
  padding: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0 16px;
  height: 38px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.45);
  cursor: pointer;
  transition:
    background 0.2s,
    color 0.2s;
  font-family: "DM Sans", sans-serif;
  white-space: nowrap;
}
.logout-btn:hover {
  background: rgba(215, 0, 24, 0.15);
  color: #ff6b7a;
}
.logout-btn .nav-icon {
  width: 20px;
  height: 20px;
  margin-right: 18px; /* mr-3 equivalent */
  display: flex;
  align-items: center;
  justify-content: center;
}
.logout-btn .nav-label {
  font-size: 13px;
  font-weight: 500;
  flex: none; /* Disable flex: 1 so it doesn't push the text away */
}

/* ─── CONFIRM LOGOUT MODAL ─── */
.logout-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.25s ease-out;
}

.logout-modal {
  background: #ffffff;
  border-radius: 20px;
  padding: 32px;
  width: 90%;
  max-width: 420px;
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.8);
  animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-icon {
  width: 60px;
  height: 60px;
  background: rgba(215, 0, 24, 0.1);
  color: #d70018;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.logout-modal h3 {
  font-family: "DM Sans", sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 8px;
  letter-spacing: 0;
  text-transform: none;
}

.logout-modal p {
  font-size: 14px;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 28px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn-cancel {
  flex: 1;
  padding: 12px 20px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
  color: #0f172a;
  border-color: #cbd5e1;
}

.btn-confirm {
  flex: 1;
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #d70018, #b90014);
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.25);
}

.btn-confirm:hover {
  opacity: 0.95;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(215, 0, 24, 0.35);
}

.btn-confirm:active {
  transform: translateY(0);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes scaleUp {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
