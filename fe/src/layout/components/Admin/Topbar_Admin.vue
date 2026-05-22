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
      <div class="topbar-search-container" ref="searchContainer">
        <div class="topbar-search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
          <input 
            type="text" 
            placeholder="Tìm kiếm..." 
            v-model="searchQuery"
            @input="onSearchInput"
            @focus="searchFocused = true"
            ref="searchInput"
          />
          <div class="search-shortcut" @click="focusSearch">⌘K</div>
        </div>

        <!-- Search Dropdown Panel -->
        <div class="search-dropdown" v-if="searchFocused && (searchQuery || searchLoading)">
          <div v-if="searchLoading" class="dropdown-empty">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color: #D70018;"></i>
            <p style="margin-top: 10px;">Đang tìm kiếm...</p>
          </div>
          <div v-else-if="!hasSearchResults" class="dropdown-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
            <p>Không tìm thấy kết quả nào cho "{{ searchQuery }}"</p>
          </div>
          <div v-else>
            <!-- Products -->
            <div class="search-section" v-if="searchResults.products && searchResults.products.length > 0">
              <div class="search-section-title">
                <i class="fa-solid fa-box"></i> Sản phẩm
              </div>
              <div 
                v-for="p in searchResults.products" 
                :key="'prod-'+p.id" 
                class="search-item"
                @click="navigateAndCloseSearch(p.link)"
              >
                <div 
                  class="search-item-img" 
                  :style="p.image ? { backgroundImage: `url(/${p.image})` } : {}"
                >
                  <i v-if="!p.image" class="fa-solid fa-image"></i>
                </div>
                <div class="search-item-info">
                  <p class="search-item-name">{{ p.title }}</p>
                  <p class="search-item-sub">{{ p.subtitle }}</p>
                </div>
                <i class="fa-solid fa-chevron-right search-item-arrow"></i>
              </div>
            </div>

            <!-- Orders -->
            <div class="search-section" v-if="searchResults.orders && searchResults.orders.length > 0">
              <div class="search-section-title">
                <i class="fa-solid fa-receipt"></i> Đơn hàng
              </div>
              <div 
                v-for="o in searchResults.orders" 
                :key="'ord-'+o.id" 
                class="search-item"
                @click="navigateAndCloseSearch(o.link)"
              >
                <div class="search-item-img" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                  <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="search-item-info">
                  <p class="search-item-name" style="color: #10b981; font-weight: 700;">{{ o.title }}</p>
                  <p class="search-item-sub">{{ o.subtitle }}</p>
                </div>
                <i class="fa-solid fa-chevron-right search-item-arrow"></i>
              </div>
            </div>

            <!-- Customers -->
            <div class="search-section" v-if="searchResults.customers && searchResults.customers.length > 0">
              <div class="search-section-title">
                <i class="fa-solid fa-users"></i> Khách hàng
              </div>
              <div 
                v-for="c in searchResults.customers" 
                :key="'cust-'+c.id" 
                class="search-item"
                @click="navigateAndCloseSearch(c.link)"
              >
                <div 
                  class="search-item-img" 
                  :style="c.image ? { backgroundImage: `url(/${c.image})` } : { background: 'rgba(59, 130, 246, 0.1)', color: '#3b82f6' }"
                >
                  <i v-if="!c.image" class="fa-solid fa-user"></i>
                </div>
                <div class="search-item-info">
                  <p class="search-item-name">{{ c.title }}</p>
                  <p class="search-item-sub">{{ c.subtitle }}</p>
                </div>
                <i class="fa-solid fa-chevron-right search-item-arrow"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="topbar-dropdown-wrapper" ref="notifContainer">
        <button class="topbar-icon-btn" id="notif-btn" @click="toggleNotifs">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 01-3.46 0" />
          </svg>
          <span class="icon-badge" v-if="unreadNotifsCount > 0">{{ unreadNotifsCount }}</span>
        </button>

        <!-- Notifications Dropdown Panel -->
        <div class="dropdown-panel notif-dropdown" v-if="showNotifs">
          <div class="dropdown-header">
            <h3>Thông báo</h3>
            <button 
              v-if="unreadNotifsCount > 0" 
              class="dropdown-header-btn" 
              @click="markAllNotifsAsRead"
            >
              Đọc tất cả
            </button>
          </div>
          <div class="dropdown-list">
            <div v-if="notifications.length === 0" class="dropdown-empty">
              <i class="fa-solid fa-bell-slash fa-2x" style="color: #cbd5e1; margin-bottom: 8px;"></i>
              <p>Không có thông báo nào</p>
            </div>
            <div 
              v-else 
              v-for="n in notifications" 
              :key="'notif-'+n.id" 
              class="notif-item"
              :class="{ unread: !n.da_doc }"
              @click="readAndNavigateNotification(n)"
            >
              <div class="notif-icon-wrap" :class="n.loai">
                <i v-if="n.loai === 'don_hang_moi'" class="fa-solid fa-bag-shopping"></i>
                <i v-else-if="n.loai === 'het_hang'" class="fa-solid fa-triangle-exclamation"></i>
                <i v-else-if="n.loai === 'thu_lien_he'" class="fa-solid fa-envelope"></i>
                <i v-else class="fa-solid fa-bell"></i>
              </div>
              <div class="notif-body">
                <p class="notif-title">{{ n.tieu_de }}</p>
                <p class="notif-desc">{{ n.noi_dung }}</p>
                <span class="notif-time">{{ formatTimeAgo(n.tao_luc) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Messages -->
      <div class="topbar-dropdown-wrapper" ref="msgContainer">
        <button class="topbar-icon-btn" id="msg-btn" @click="toggleMsgs">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
          </svg>
          <span class="icon-badge icon-badge-blue" v-if="unreadContactsCount > 0">{{ unreadContactsCount }}</span>
        </button>

        <!-- Messages Dropdown Panel -->
        <div class="dropdown-panel msg-dropdown" v-if="showMsgs">
          <div class="dropdown-header">
            <h3>Hộp thư liên hệ</h3>
          </div>
          <div class="dropdown-list">
            <div v-if="contacts.length === 0" class="dropdown-empty">
              <i class="fa-solid fa-envelope-open fa-2x" style="color: #cbd5e1; margin-bottom: 8px;"></i>
              <p>Không có thư liên hệ nào</p>
            </div>
            <div 
              v-else 
              v-for="c in contacts" 
              :key="'contact-'+c.id" 
              class="msg-item"
              @click="openContact(c)"
            >
              <div class="msg-avatar">
                {{ c.ho_ten ? c.ho_ten.charAt(0).toUpperCase() : 'C' }}
              </div>
              <div class="msg-body">
                <div class="msg-sender-row">
                  <span class="msg-sender-name">{{ c.ho_ten }}</span>
                  <span 
                    class="msg-status-tag" 
                    :class="{ new: c.trang_thai === 0, read: c.trang_thai === 1, replied: c.trang_thai === 2 }"
                  >
                    {{ c.trang_thai === 0 ? 'Mới' : (c.trang_thai === 1 ? 'Đã đọc' : 'Đã phản hồi') }}
                  </span>
                </div>
                <p class="msg-subject">{{ c.tieu_de }}</p>
                <p class="msg-excerpt">{{ c.noi_dung }}</p>
                <span class="msg-time">{{ formatTimeAgo(c.tao_luc) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

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

    <!-- Reply Modal Overlay -->
    <div class="modal-overlay" v-if="showReplyModal && activeContact" @click.self="showReplyModal = false">
      <div class="contact-modal">
        <div class="modal-header">
          <h3>Chi tiết thư liên hệ</h3>
          <button class="modal-close-btn" @click="showReplyModal = false">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="contact-meta-grid">
            <div class="meta-field">
              <span class="meta-label">Người gửi</span>
              <span class="meta-value">{{ activeContact.ho_ten }}</span>
            </div>
            <div class="meta-field">
              <span class="meta-label">Email</span>
              <span class="meta-value">{{ activeContact.email }}</span>
            </div>
            <div class="meta-field">
              <span class="meta-label">Số điện thoại</span>
              <span class="meta-value">{{ activeContact.so_dien_thoai || 'Chưa cung cấp' }}</span>
            </div>
            <div class="meta-field">
              <span class="meta-label">Thời gian gửi</span>
              <span class="meta-value">{{ new Date(activeContact.tao_luc).toLocaleString('vi-VN') }}</span>
            </div>
          </div>

          <div class="contact-message-card">
            <h4 class="message-subject">{{ activeContact.tieu_de }}</h4>
            <p class="message-body">{{ activeContact.noi_dung }}</p>
          </div>

          <!-- Status badge in modal -->
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Trạng thái:</span>
            <span 
              class="msg-status-tag" 
              style="padding: 3px 8px; font-size: 10px; border-radius: 6px;"
              :class="{ new: activeContact.trang_thai === 0, read: activeContact.trang_thai === 1, replied: activeContact.trang_thai === 2 }"
            >
              {{ activeContact.trang_thai === 0 ? 'Chưa đọc' : (activeContact.trang_thai === 1 ? 'Đã đọc' : 'Đã phản hồi') }}
            </span>
          </div>

          <!-- Reply section -->
          <div class="reply-section">
            <!-- If replied, show past reply -->
            <div v-if="activeContact.trang_thai === 2 && activeContact.phan_hoi" class="past-reply-card">
              <div class="past-reply-title">Phản hồi của cửa hàng</div>
              <p class="past-reply-body">{{ activeContact.phan_hoi }}</p>
            </div>

            <!-- Reply form -->
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
              <label for="reply-text" style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                {{ activeContact.trang_thai === 2 ? 'Cập nhật phản hồi' : 'Viết phản hồi' }}
              </label>
              <textarea 
                id="reply-text" 
                class="reply-textarea" 
                placeholder="Nhập nội dung phản hồi gửi đến khách hàng..."
                v-model="replyText"
              ></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showReplyModal = false">Đóng</button>
          <button class="btn-primary" :disabled="replying" @click="submitReply">
            <span v-if="replying"><i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...</span>
            <span v-else><i class="fa-solid fa-paper-plane"></i> Gửi phản hồi</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useToast } from 'vue-toastification';

export default {
  name: "Topbar_Admin",
  props: {
    pageTitle: {
      type: String,
      default: "Dashboard",
    },
  },
  setup() {
    const authStore = useAuthStore();
    const toast = useToast();
    return { authStore, toast };
  },
  data() {
    return {
      userAvatar: "",
      
      // Search
      searchQuery: "",
      searchResults: {
        products: [],
        orders: [],
        customers: []
      },
      searchLoading: false,
      searchFocused: false,
      searchTimeout: null,
      
      // Notifications
      notifications: [],
      unreadNotifsCount: 0,
      showNotifs: false,
      
      // Messages / Contacts
      contacts: [],
      unreadContactsCount: 0,
      showMsgs: false,
      
      // Reply Modal
      showReplyModal: false,
      activeContact: null,
      replyText: "",
      replying: false,
      
      // Pusher
      pusher: null
    };
  },
  computed: {
    userName() {
      return this.authStore.hoTen || "Nhân viên";
    },
    userRole() {
      const roleMap = {
        1: "Super Admin",
        2: "Quản lý",
        4: "Nhân viên kho",
        5: "Nhân viên bán hàng"
      };
      return roleMap[this.authStore.vaiTro] || "Nhân viên";
    },
    avatarStyle() {
      if (this.userAvatar) {
        const imgUrl = this.userAvatar.startsWith('http') 
          ? this.userAvatar 
          : '/' + this.userAvatar;
        return {
          backgroundImage: `url(${imgUrl})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          color: 'transparent'
        };
      }
      return {};
    },
    hasSearchResults() {
      const r = this.searchResults;
      return (r.products && r.products.length > 0) || 
             (r.orders && r.orders.length > 0) || 
             (r.customers && r.customers.length > 0);
    }
  },
  mounted() {
    this.loadUserData();
    this.fetchFreshUserData();
    window.addEventListener('profileUpdated', this.loadUserData);
    
    // Fetch initial notifications and contacts count
    this.fetchNotifications();
    this.fetchContacts();
    
    // Initialize Pusher
    this.initPusher();
    
    // Register outside click and shortcuts
    document.addEventListener('click', this.handleOutsideClick);
    document.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('profileUpdated', this.loadUserData);
    document.removeEventListener('click', this.handleOutsideClick);
    document.removeEventListener('keydown', this.handleKeydown);
    
    if (this.pusher) {
      try {
        this.pusher.unsubscribe('my-channel');
      } catch (err) {
        console.error(err);
      }
    }
  },
  methods: {
    loadUserData() {
      this.userAvatar = this.authStore.anhDaiDien || '';
    },
    async fetchFreshUserData() {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        const res = await axios.get('/api/thong-tin-ca-nhan/profile', {
          headers: {
            Authorization: 'Bearer ' + token
          }
        });
        if (res.data.status) {
          const d = res.data.data;
          
          // Sync with Pinia store
          const userObj = {
            id: d.id,
            ho_ten: d.name,
            email: d.email || this.authStore.email,
            vai_tro: this.authStore.vaiTro, // Keep original role
            anh_dai_dien: d.avatar || ''
          };
          this.authStore.setAdminData(userObj, token);
        }
      } catch (err) {
        console.error('Error fetching fresh user data:', err);
      }
    },
    
    // Global Search Methods
    onSearchInput() {
      clearTimeout(this.searchTimeout);
      if (!this.searchQuery.trim()) {
        this.searchResults = { products: [], orders: [], customers: [] };
        this.searchLoading = false;
        return;
      }
      
      this.searchLoading = true;
      this.searchTimeout = setTimeout(async () => {
        try {
          const token = this.authStore.tokenAdmin;
          if (!token) return;
          
          const res = await axios.get('/api/staff/search', {
            params: { q: this.searchQuery },
            headers: { Authorization: 'Bearer ' + token }
          });
          
          this.searchResults = res.data;
        } catch (err) {
          console.error('Search API error:', err);
        } finally {
          this.searchLoading = false;
        }
      }, 400);
    },
    focusSearch() {
      if (this.$refs.searchInput) {
        this.$refs.searchInput.focus();
        this.searchFocused = true;
      }
    },
    navigateAndCloseSearch(link) {
      this.searchFocused = false;
      this.searchQuery = "";
      this.$router.push(link);
    },
    
    // Notifications methods
    async fetchNotifications() {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        const res = await axios.get('/api/staff/notifications', {
          headers: { Authorization: 'Bearer ' + token }
        });
        
        this.notifications = res.data.notifications || [];
        this.unreadNotifsCount = res.data.unread_count || 0;
      } catch (err) {
        console.error('Fetch notifications error:', err);
      }
    },
    toggleNotifs() {
      this.showNotifs = !this.showNotifs;
      this.showMsgs = false;
      this.searchFocused = false;
      if (this.showNotifs) {
        this.fetchNotifications();
      }
    },
    async readAndNavigateNotification(notif) {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        if (!notif.da_doc) {
          const res = await axios.post(`/api/staff/notifications/${notif.id}/read`, {}, {
            headers: { Authorization: 'Bearer ' + token }
          });
          if (res.data.status) {
            notif.da_doc = true;
            this.unreadNotifsCount = res.data.unread_count;
          }
        }
        
        this.showNotifs = false;
        if (notif.duong_dan) {
          this.$router.push(notif.duong_dan);
        }
      } catch (err) {
        console.error('Mark notification as read error:', err);
      }
    },
    async markAllNotifsAsRead() {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        const res = await axios.post('/api/staff/notifications/read-all', {}, {
          headers: { Authorization: 'Bearer ' + token }
        });
        
        if (res.data.status) {
          this.notifications.forEach(n => n.da_doc = true);
          this.unreadNotifsCount = 0;
          this.toast.success(res.data.message || 'Đã đọc tất cả thông báo!');
        }
      } catch (err) {
        console.error('Mark all read error:', err);
        this.toast.error('Có lỗi xảy ra khi đọc thông báo.');
      }
    },
    
    // Contact Letters methods
    async fetchContacts() {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        const res = await axios.get('/api/staff/contacts', {
          params: { limit: 10 },
          headers: { Authorization: 'Bearer ' + token }
        });
        
        this.contacts = res.data.contacts.data || [];
        this.unreadContactsCount = res.data.unread_count || 0;
      } catch (err) {
        console.error('Fetch contacts error:', err);
      }
    },
    toggleMsgs() {
      this.showMsgs = !this.showMsgs;
      this.showNotifs = false;
      this.searchFocused = false;
      if (this.showMsgs) {
        this.fetchContacts();
      }
    },
    async openContact(contact) {
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        if (contact.trang_thai === 0) {
          const res = await axios.post(`/api/staff/contacts/${contact.id}/read`, {}, {
            headers: { Authorization: 'Bearer ' + token }
          });
          if (res.data.status) {
            contact.trang_thai = 1;
            this.unreadContactsCount = res.data.unread_count;
          }
        }
        
        this.showMsgs = false;
        this.activeContact = contact;
        this.replyText = contact.phan_hoi || '';
        this.showReplyModal = true;
      } catch (err) {
        console.error('Open contact error:', err);
      }
    },
    async submitReply() {
      if (!this.replyText.trim()) {
        this.toast.warning('Nội dung phản hồi không được để trống.');
        return;
      }
      
      this.replying = true;
      try {
        const token = this.authStore.tokenAdmin;
        if (!token) return;
        
        const res = await axios.post(`/api/staff/contacts/${this.activeContact.id}/reply`, {
          phan_hoi: this.replyText
        }, {
          headers: { Authorization: 'Bearer ' + token }
        });
        
        if (res.data.status) {
          this.toast.success(res.data.message || 'Đã gửi phản hồi thư liên hệ thành công!');
          
          this.activeContact.trang_thai = 2;
          this.activeContact.phan_hoi = this.replyText;
          
          this.fetchContacts();
          this.showReplyModal = false;
          this.activeContact = null;
          this.replyText = '';
        }
      } catch (err) {
        console.error('Submit reply error:', err);
        this.toast.error(err.response?.data?.message || 'Có lỗi xảy ra khi phản hồi.');
      } finally {
        this.replying = false;
      }
    },
    
    // Helpers & Event handlers
    formatTimeAgo(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      const now = new Date();
      const seconds = Math.floor((now - date) / 1000);
      
      if (seconds < 60) return 'Vừa xong';
      const minutes = Math.floor(seconds / 60);
      if (minutes < 60) return `${minutes} phút trước`;
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `${hours} giờ trước`;
      const days = Math.floor(hours / 24);
      if (days < 30) return `${days} ngày trước`;
      
      return date.toLocaleDateString('vi-VN');
    },
    handleOutsideClick(event) {
      if (this.$refs.searchContainer && !this.$refs.searchContainer.contains(event.target)) {
        this.searchFocused = false;
      }
      if (this.$refs.notifContainer && !this.$refs.notifContainer.contains(event.target)) {
        this.showNotifs = false;
      }
      if (this.$refs.msgContainer && !this.$refs.msgContainer.contains(event.target)) {
        this.showMsgs = false;
      }
    },
    handleKeydown(event) {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        this.focusSearch();
      }
      if (event.key === 'Escape') {
        this.searchFocused = false;
        this.showNotifs = false;
        this.showMsgs = false;
        this.showReplyModal = false;
      }
    },
    
    // Pusher Implementation
    initPusher() {
      if (window.Pusher) {
        this.setupPusher();
      } else {
        const script = document.createElement('script');
        script.src = 'https://js.pusher.com/8.4.0/pusher.min.js';
        script.onload = () => this.setupPusher();
        document.head.appendChild(script);
      }
    },
    setupPusher() {
      try {
        this.pusher = new window.Pusher('794a0b225fca675fc9a7', { cluster: 'ap1' });
        const channel = this.pusher.subscribe('my-channel');
        
        channel.bind('new-order', (data) => {
          this.handleNewOrderRealtime(data);
        });
        
        channel.bind('new-contact', (data) => {
          this.handleNewContactRealtime(data);
        });
      } catch (err) {
        console.error('Pusher setup failed:', err);
      }
    },
    handleNewOrderRealtime(data) {
      this.playNotificationSound();
      
      const totalFormatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.total || 0);
      this.toast.success(`Có đơn hàng mới: ${data.ma_don_hang} từ ${data.customer} (${totalFormatted})!`, {
        timeout: 8000,
      });
      
      this.fetchNotifications();
    },
    handleNewContactRealtime(data) {
      this.playNotificationSound();
      this.toast.info(`Thư liên hệ mới từ: ${data.ho_ten} - "${data.tieu_de}"`, {
        timeout: 8000,
      });
      
      this.fetchNotifications();
      this.fetchContacts();
    },
    playNotificationSound() {
      try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const playNote = (freq, startTime, duration) => {
          const osc = audioCtx.createOscillator();
          const gain = audioCtx.createGain();
          
          osc.type = 'sine';
          osc.frequency.setValueAtTime(freq, startTime);
          
          gain.gain.setValueAtTime(0.08, startTime);
          gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);
          
          osc.connect(gain);
          gain.connect(audioCtx.destination);
          
          osc.start(startTime);
          osc.stop(startTime + duration);
        };
        
        const now = audioCtx.currentTime;
        playNote(523.25, now, 0.3); // C5
        playNote(659.25, now + 0.12, 0.4); // E5
      } catch (err) {
        console.warn('Notification audio block:', err);
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

.topbar-search-container,
.topbar-dropdown-wrapper {
  position: relative;
  display: inline-block;
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
  cursor: pointer;
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

/* ========================================================
   Dropdown styles
   ======================================================== */
.dropdown-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 360px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(16px);
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.03);
  overflow: hidden;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  animation: slideDownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideDownFade {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.search-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 460px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(16px);
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.03);
  z-index: 1000;
  max-height: 480px;
  overflow-y: auto;
  padding: 16px;
  animation: slideDownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.search-section {
  margin-bottom: 16px;
}
.search-section:last-child {
  margin-bottom: 0;
}
.search-section-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
  padding-left: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.search-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
  text-decoration: none;
}
.search-item:hover {
  background: rgba(215, 0, 24, 0.04);
}
.search-item-img {
  width: 36px;
  height: 36px;
  border-radius: 6px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #64748b;
  flex-shrink: 0;
  background-size: cover;
  background-position: center;
}
.search-item-info {
  flex: 1;
  min-width: 0;
}
.search-item-name {
  font-size: 13.5px;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.search-item-sub {
  font-size: 11px;
  color: #64748b;
  margin: 2px 0 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.search-item-arrow {
  color: #94a3b8;
  opacity: 0;
  transform: translateX(-5px);
  transition: all 0.2s ease;
}
.search-item:hover .search-item-arrow {
  opacity: 1;
  transform: translateX(0);
  color: #D70018;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid #f1f5f9;
  background: rgba(255, 255, 255, 0.8);
}
.dropdown-header h3 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 17px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  margin: 0;
}
.dropdown-header-btn {
  font-size: 12px;
  font-weight: 600;
  color: #D70018;
  background: none;
  border: none;
  cursor: pointer;
  transition: color 0.15s;
}
.dropdown-header-btn:hover {
  color: #b00013;
  text-decoration: underline;
}

.dropdown-list {
  max-height: 360px;
  overflow-y: auto;
}
.dropdown-empty {
  padding: 30px 20px;
  text-align: center;
  color: #94a3b8;
}
.dropdown-empty svg {
  margin-bottom: 8px;
  color: #cbd5e1;
}
.dropdown-empty p {
  font-size: 13px;
  margin: 0;
}

/* Notification Item */
.notif-item {
  display: flex;
  gap: 14px;
  padding: 14px 18px;
  border-bottom: 1px solid #f8fafc;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
}
.notif-item:hover {
  background: #f8fafc;
}
.notif-item.unread {
  background: rgba(215, 0, 24, 0.015);
}
.notif-item.unread::before {
  content: '';
  position: absolute;
  left: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #D70018;
}
.notif-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 15px;
}
.notif-icon-wrap.don_hang_moi {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}
.notif-icon-wrap.het_hang {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}
.notif-icon-wrap.thu_lien_he {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}
.notif-icon-wrap.other {
  background: rgba(100, 116, 139, 0.1);
  color: #64748b;
}

.notif-body {
  flex: 1;
  min-width: 0;
}
.notif-title {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 3px 0;
  line-height: 1.3;
}
.notif-desc {
  font-size: 12px;
  color: #64748b;
  margin: 0 0 4px 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.notif-time {
  font-size: 10px;
  color: #94a3b8;
}

/* Contact Message Item */
.msg-item {
  display: flex;
  gap: 12px;
  padding: 12px 18px;
  border-bottom: 1px solid #f8fafc;
  cursor: pointer;
  transition: background 0.15s;
}
.msg-item:hover {
  background: #f8fafc;
}
.msg-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ef4444, #f97316);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}
.msg-body {
  flex: 1;
  min-width: 0;
}
.msg-sender-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 3px;
}
.msg-sender-name {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}
.msg-status-tag {
  font-size: 9px;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: 4px;
  text-transform: uppercase;
}
.msg-status-tag.new {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}
.msg-status-tag.read {
  background: rgba(100, 116, 139, 0.1);
  color: #64748b;
}
.msg-status-tag.replied {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}
.msg-subject {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  margin: 0 0 2px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.msg-excerpt {
  font-size: 11.5px;
  color: #64748b;
  margin: 0 0 4px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.msg-time {
  font-size: 10px;
  color: #94a3b8;
}

/* ========================================================
   Reply Modal Overlay
   ======================================================== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.contact-modal {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
  animation: modalScaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalScaleUp {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid #f1f5f9;
}
.modal-header h3 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  margin: 0;
}
.modal-close-btn {
  background: #f1f5f9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}
.modal-close-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
  transform: rotate(90deg);
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.contact-meta-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: #f8fafc;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}
.meta-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.meta-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.05em;
}
.meta-value {
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
  word-break: break-all;
}

.contact-message-card {
  border-left: 4px solid #D70018;
  background: rgba(215, 0, 24, 0.02);
  padding: 16px 20px;
  border-radius: 0 12px 12px 0;
}
.message-subject {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 8px 0;
}
.message-body {
  font-size: 13.5px;
  line-height: 1.6;
  color: #475569;
  margin: 0;
  white-space: pre-wrap;
}

.reply-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.reply-textarea {
  width: 100%;
  min-height: 120px;
  border: 1.5px solid #cbd5e1;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 13.5px;
  outline: none;
  transition: all 0.2s;
  font-family: 'DM Sans', sans-serif;
  resize: vertical;
}
.reply-textarea:focus {
  border-color: #D70018;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.08);
}
.past-reply-card {
  background: rgba(139, 92, 246, 0.04);
  border-left: 4px solid #8b5cf6;
  padding: 14px 18px;
  border-radius: 0 10px 10px 0;
}
.past-reply-title {
  font-size: 12px;
  font-weight: 700;
  color: #8b5cf6;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.past-reply-body {
  font-size: 13px;
  color: #475569;
  margin: 0;
  line-height: 1.5;
  white-space: pre-wrap;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
.btn-secondary {
  padding: 10px 20px;
  background: #f1f5f9;
  border: none;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-secondary:hover {
  background: #e2e8f0;
  color: #0f172a;
}
.btn-primary {
  padding: 10px 24px;
  background: #D70018;
  border: none;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 700;
  color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}
.btn-primary:hover {
  background: #b00013;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}
.btn-primary:disabled {
  background: #fda4af;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
</style>
