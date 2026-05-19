<template>
  <header class="admin-topbar">
    <!-- Page Title -->
    <div class="topbar-left">
      <div class="page-title-wrap">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <div class="breadcrumb">
          <span>Nhân viên</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="9 18 15 12 9 6" />
          </svg>
          <span class="breadcrumb-current">{{ pageTitle }}</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="topbar-right">
      <!-- Search -->
      <div class="topbar-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input type="text" placeholder="Tìm kiếm..." />
        <div class="search-shortcut">⌘K</div>
      </div>

      <!-- Notifications -->
      <button class="topbar-icon-btn" id="notif-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
          <path d="M13.73 21a2 2 0 01-3.46 0" />
        </svg>
        <span class="icon-badge">3</span>
      </button>

      <!-- Messages -->
      <button class="topbar-icon-btn" id="msg-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
        </svg>
        <span class="icon-badge icon-badge-blue">5</span>
      </button>

      <!-- Divider -->
      <div class="topbar-divider"></div>

      <!-- Profile -->
      <router-link to="/nhan-vien/profile">
        <button class="topbar-profile" id="profile-btn">
          <div class="profile-avatar" :style="avatarStyle">
            <span v-if="!userAvatar">{{ userName ? userName.charAt(0).toUpperCase() : 'A' }}</span>
          </div>
          <div class="profile-info">
            <p class="profile-name">{{ userName }}</p>
            <p class="profile-role">{{ userRole }}</p>
          </div>
        </button>
      </router-link>
    </div>
  </header>
</template>

<script>
import axios from 'axios';

export default {
  name: "Topbar_Admin",
  props: {
    pageTitle: {
      type: String,
      default: "Dashboard",
    },
  },
  data() {
    return {
      userName: "Admin Việt",
      userRole: "Super Admin",
      userAvatar: "",
    };
  },
  computed: {
    avatarStyle() {
      if (this.userAvatar) {
        const imgUrl = this.userAvatar.startsWith('http') 
          ? this.userAvatar 
          : 'http://127.0.0.1:8000/' + this.userAvatar;
        return {
          backgroundImage: `url(${imgUrl})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          color: 'transparent'
        };
      }
      return {};
    }
  },
  mounted() {
    this.loadUserData();
    this.fetchFreshUserData();
    window.addEventListener('profileUpdated', this.loadUserData);
  },
  beforeUnmount() {
    window.removeEventListener('profileUpdated', this.loadUserData);
  },
  methods: {
    loadUserData() {
      this.userName = localStorage.getItem('ho_ten') || 'Admin Việt';
      this.userRole = localStorage.getItem('ten_vai_tro') || 'Super Admin';
      this.userAvatar = localStorage.getItem('anh_dai_dien') || '';
    },
    async fetchFreshUserData() {
      try {
        const token = localStorage.getItem('token_admin');
        if (!token) return;
        
        const res = await axios.get('http://127.0.0.1:8000/api/thong-tin-ca-nhan/profile', {
          headers: {
            Authorization: 'Bearer ' + token
          }
        });
        if (res.data.status) {
          const d = res.data.data;
          localStorage.setItem('ho_ten', d.name);
          localStorage.setItem('ten_vai_tro', d.roleName);
          localStorage.setItem('anh_dai_dien', d.avatar || '');
          window.dispatchEvent(new Event('profileUpdated'));
        }
      } catch (err) {
        console.error('Error fetching fresh user data:', err);
      }
    }
  }
};
</script>

<style scoped>
.admin-topbar {
  height: 64px;
  background: #fff;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
  flex-shrink: 0;
}

.page-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  line-height: 1;
  margin: 0;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 3px;
  font-size: 11.5px;
  color: #94a3b8;
}
.breadcrumb-current {
  color: #D70018;
  font-weight: 600;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.topbar-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 0 12px;
  height: 38px;
  min-width: 220px;
  transition: border-color 0.2s, box-shadow 0.2s;
  color: #94a3b8;
}
.topbar-search:focus-within {
  border-color: #D70018;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.08);
  color: #64748b;
}
.topbar-search input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  color: #0f172a;
  flex: 1;
  font-family: 'DM Sans', sans-serif;
}
.topbar-search input::placeholder {
  color: #94a3b8;
}
.search-shortcut {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  background: #e2e8f0;
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
}

.topbar-icon-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: background 0.2s, color 0.2s, border-color 0.2s;
  flex-shrink: 0;
}
.topbar-icon-btn:hover {
  background: #fff;
  border-color: #D70018;
  color: #D70018;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.1);
}
.icon-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #D70018;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  width: 17px;
  height: 17px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
}
.icon-badge-blue {
  background: #3b82f6;
}

.topbar-divider {
  width: 1px;
  height: 28px;
  background: #e2e8f0;
  margin: 0 4px;
}

.topbar-profile {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px 6px 6px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
  color: #64748b;
}
.topbar-profile:hover {
  background: #fff;
  border-color: #D70018;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  color: #0f172a;
}
.profile-avatar {
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #D70018, #7c3aed);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}
.profile-info {
  text-align: left;
}
.profile-name {
  font-size: 12.5px;
  font-weight: 600;
  color: #0f172a;
  line-height: 1;
  white-space: nowrap;
}
.profile-role {
  font-size: 10.5px;
  color: #94a3b8;
  margin-top: 2px;
  white-space: nowrap;
}
</style>
