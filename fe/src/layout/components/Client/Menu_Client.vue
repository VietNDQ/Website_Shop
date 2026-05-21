<template>
  <nav class="main-nav-bar">
    <div class="nav-inner">
      <a href="/" class="nav-logo">
        BALAB
        <div class="nav-logo-s">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="5 12 12 5 19 12" />
            <polyline points="5 19 12 12 19 19" />
          </svg>
        </div>
      </a>

      <div class="nav-cat-wrap">
        <button class="nav-cat-btn" @click.stop="toggleCategoryDropdown">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <rect x="3" y="3" width="7" height="7" />
            <rect x="14" y="3" width="7" height="7" />
            <rect x="14" y="14" width="7" height="7" />
            <rect x="3" y="14" width="7" height="7" />
          </svg>
          Danh mục
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 12 15 18 9" />
          </svg>
        </button>

        <div class="nav-cat-dropdown" v-if="showCategoryDropdown">
          <button class="nav-cat-item" @click="applyCategoryFilter(null)">Tất cả danh mục</button>
          <button v-for="cat in categories" :key="cat.id" class="nav-cat-item" @click="applyCategoryFilter(cat.id)">
            {{ cat.ten_danh_muc }}
          </button>
        </div>
      </div>

      <div class="nav-search-wrap" @mousedown.stop @click.stop>
        <svg class="nav-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input
          class="nav-search"
          type="text"
          v-model.trim="menuSearchQuery"
          @input="showMenuSuggestions = true"
          @keyup.enter="submitMenuSearch"
          @focus="showMenuSuggestions = true"
          @blur="hideMenuSuggestionsWithDelay"
          placeholder="Bạn muốn mua gì hôm nay?"
        />
        <div v-if="showMenuSuggestions && menuSuggestions.length > 0" class="nav-suggestions-box">
          <button
            v-for="item in menuSuggestions"
            :key="`${item.type}-${item.id}`"
            type="button"
            class="nav-suggestion-item"
            @mousedown.prevent="selectMenuSuggestion(item)"
          >
            {{ item.label }}
          </button>
        </div>
      </div>
      <button class="btn-menu-search" @click="submitMenuSearch">Tìm</button>

      <button class="btn-cart" @click="$router.push('/gio-hang')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <path d="M16 10a4 4 0 01-8 0" />
        </svg>
        Giỏ hàng
        <div class="cart-badge" v-if="cartCount > 0">{{ cartCount }}</div>
      </button>

      <template v-if="isLoggedIn">
        <div class="account-dropdown">
          <button class="btn-account" @click.stop="toggleDropdown">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            {{ userName }}
          </button>
          <div class="dropdown-content" v-if="showDropdown">
            <div class="user-info">
              <span class="user-name-title" :title="userName">{{ userName }}</span>
              <span class="user-role-title">Khách hàng</span>
            </div>
            <button class="dropdown-item" @click="$router.push('/thong-tin-ca-nhan'); showDropdown = false;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              Tài khoản của tôi
            </button>
          </div>
        </div>
      </template>
      <template v-else>
        <button class="btn-account" @click="$router.push('/login')">Đăng nhập</button>
      </template>
    </div>
  </nav>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../../store/cartStore";

export default {
  data() {
    return {
      isLoggedIn: false,
      userName: "",
      showDropdown: false,
      categories: [],
      selectedCategoryId: null,
      menuSearchQuery: "",
      showCategoryDropdown: false,
      menuSuggestions: [],
      showMenuSuggestions: false,
      menuSuggestDebounceTimer: null,
      menuSuggestCache: {},
    };
  },
  computed: {
    cartCount() {
      const cartStore = useCartStore();
      return cartStore.cartCount;
    },
  },
  watch: {
    "$route.query": {
      handler() {
        this.syncMenuStateFromRoute();
      },
      deep: true,
    },
    menuSearchQuery() {
      this.debouncedMenuSuggest();
    },
  },
  mounted() {
    this.checkLoginStatus();
    this.fetchCategories();
    this.syncMenuStateFromRoute();

    const cartStore = useCartStore();
    cartStore.loadCart();

    window.addEventListener("clientLoginUpdated", this.onLoginUpdated);
    document.addEventListener("click", this.closeDropdown);
  },
  beforeUnmount() {
    window.removeEventListener("clientLoginUpdated", this.onLoginUpdated);
    document.removeEventListener("click", this.closeDropdown);
    if (this.menuSuggestDebounceTimer) clearTimeout(this.menuSuggestDebounceTimer);
  },
  methods: {
    async fetchCategories() {
      try {
        const res = await axios.get("http://127.0.0.1:8000/api/danh-muc");
        this.categories = Array.isArray(res.data) ? res.data : [];
      } catch {
        this.categories = [];
      }
    },
    syncMenuStateFromRoute() {
      const q = this.$route.query || {};
      this.menuSearchQuery = q.search ? String(q.search) : "";
      this.selectedCategoryId = q.id_danh_muc ? Number(q.id_danh_muc) : null;
    },
    toggleCategoryDropdown() {
      this.showCategoryDropdown = !this.showCategoryDropdown;
    },
    applyCategoryFilter(categoryId) {
      this.selectedCategoryId = categoryId;
      this.showCategoryDropdown = false;
      this.submitMenuSearch();
    },
    debouncedMenuSuggest() {
      if (this.menuSuggestDebounceTimer) clearTimeout(this.menuSuggestDebounceTimer);
      this.menuSuggestDebounceTimer = setTimeout(() => {
        this.fetchMenuSuggestions();
      }, 400);
    },
    async fetchMenuSuggestions() {
      const keyword = this.menuSearchQuery.trim();
      if (keyword.length < 2) {
        this.menuSuggestions = [];
        return;
      }

      const cacheKey = `${keyword}|${this.selectedCategoryId || ""}`;
      if (this.menuSuggestCache[cacheKey]) {
        this.menuSuggestions = this.menuSuggestCache[cacheKey];
        this.showMenuSuggestions = true;
        return;
      }

      try {
        const res = await axios.get("http://127.0.0.1:8000/api/tim-kiem/goi-y", {
          params: {
            q: keyword,
            id_danh_muc: this.selectedCategoryId || "",
            limit: 8,
          },
        });
        const list = res.data?.suggestions || [];
        this.menuSuggestCache[cacheKey] = list;
        this.menuSuggestions = list;
        this.showMenuSuggestions = list.length > 0;
      } catch {
        this.menuSuggestions = [];
      }
    },
    hideMenuSuggestionsWithDelay() {
      setTimeout(() => {
        this.showMenuSuggestions = false;
      }, 150);
    },
    async selectMenuSuggestion(item) {
      this.menuSearchQuery = item.label || "";
      this.showMenuSuggestions = false;
      await this.trackMenuSuggestionClick(item);
      this.submitMenuSearch();
    },
    async trackMenuSuggestionClick(item) {
      try {
        await axios.post("http://127.0.0.1:8000/api/tim-kiem/track", {
          keyword: this.menuSearchQuery,
          suggestion: item.label || "",
          type: item.type || "product",
          id_san_pham: item.type === "product" ? item.id : null,
          id_danh_muc: item.id_danh_muc || null,
        });
      } catch {}
    },
    async submitMenuSearch() {
      const params = {};
      if (this.menuSearchQuery) params.search = this.menuSearchQuery;
      if (this.selectedCategoryId) params.id_danh_muc = this.selectedCategoryId;
      this.showMenuSuggestions = false;

      try {
        await axios.get("http://127.0.0.1:8000/api/tim-kiem", {
          params: {
            q: this.menuSearchQuery || "",
            id_danh_muc: this.selectedCategoryId || "",
            limit: 8,
          },
        });
      } catch {}

      this.$router.push({ path: "/", query: params });
    },
    closeDropdown() {
      this.showDropdown = false;
      this.showCategoryDropdown = false;
    },
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    async onLoginUpdated() {
      this.checkLoginStatus();
      const cartStore = useCartStore();
      await cartStore.syncCartWithBackend();
    },
    checkLoginStatus() {
      const token = localStorage.getItem("token_client");
      const name = localStorage.getItem("ho_ten_client");
      if (token) {
        this.isLoggedIn = true;
        this.userName = name || "Đang tải...";

        axios
          .get("http://127.0.0.1:8000/api/check-token", {
            headers: { Authorization: "Bearer " + token },
          })
          .then((res) => {
            if (res.data.status) {
              this.userName = res.data.ho_ten;
              localStorage.setItem("ho_ten_client", res.data.ho_ten);
            } else {
              this.handleLogout(false);
            }
          })
          .catch(() => {
            if (!name) this.userName = "Khách hàng";
          });
      } else {
        this.isLoggedIn = false;
        this.userName = "";
      }
    },
    async handleLogout(showAlert = true) {
      localStorage.removeItem("token_client");
      localStorage.removeItem("ho_ten_client");
      this.isLoggedIn = false;
      this.userName = "";

      const cartStore = useCartStore();
      await cartStore.clearCart();

      if (showAlert) {
        if (this.$toast) {
          this.$toast.success("Đăng xuất thành công!");
        } else {
          alert("Đăng xuất thành công!");
        }
      }

      if (this.$route.path !== "/") {
        this.$router.push("/");
      } else {
        window.location.reload();
      }
    },
  },
};
</script>

<style scoped>
.nav-cat-wrap {
  position: relative;
}

.nav-cat-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  min-width: 220px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1);
  z-index: 1100;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-cat-item {
  width: 100%;
  border: none;
  background: #fff;
  text-align: left;
  padding: 9px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  color: #1f2937;
}

.nav-cat-item:hover {
  background: #f8fafc;
}

.btn-menu-search {
  border: none;
  border-radius: 10px;
  padding: 10px 14px;
  background: #e30019;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.nav-search-wrap {
  position: relative;
}

.nav-suggestions-box {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  z-index: 1200;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
  max-height: 320px;
  overflow-y: auto;
  padding: 6px 0;
}

.nav-suggestion-item {
  width: 100%;
  border: none;
  background: #fff;
  color: #0f172a;
  cursor: pointer;
  font-size: 14px;
  line-height: 1.4;
  padding: 11px 16px;
  text-align: left;
}

.nav-suggestion-item:hover {
  background: #f8fafc;
}

.account-dropdown {
  position: relative;
  display: inline-block;
}

.account-dropdown .dropdown-content {
  display: block;
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 8px;
  background-color: #fff;
  min-width: 170px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
  overflow: hidden;
  z-index: 1000;
  border: 1px solid #e2e8f0;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.dropdown-content .user-info {
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.dropdown-content .user-name-title {
  font-size: 13px;
  font-weight: 700;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

.dropdown-content .user-role-title {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 2px;
}

.dropdown-content .dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  color: #dc2626;
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s, color 0.15s;
}

.dropdown-content .dropdown-item:hover {
  background-color: #fef2f2;
}

.dropdown-content .dropdown-item svg {
  flex-shrink: 0;
}

.btn-cart {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
}

.cart-badge {
  position: absolute;
  top: -8px;
  right: -10px;
  background-color: #ffd700;
  color: #000;
  font-size: 13px;
  font-weight: bold;
  min-width: 18px;
  height: 18px;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
