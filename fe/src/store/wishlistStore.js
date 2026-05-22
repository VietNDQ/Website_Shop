import { defineStore } from 'pinia';
import axios from 'axios';

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    wishlist: [],
    recentlyViewed: [],
    loading: false,
  }),
  getters: {
    wishlistCount: (state) => state.wishlist.length,
    recentlyViewedCount: (state) => state.recentlyViewed.length,
  },
  actions: {
    getToken() {
      return localStorage.getItem("token_client");
    },
    
    getHeaders() {
      const token = this.getToken();
      return token ? { Authorization: `Bearer ${token}` } : {};
    },

    isLoggedIn() {
      return !!this.getToken();
    },

    /**
     * Helper to normalize product objects from backend format to frontend format.
     */
    _normalizeProduct(prod) {
      if (!prod) return null;

      // Extract image path
      let image = "";
      if (prod.hinh_anh) {
        image = prod.hinh_anh;
      } else if (prod.hinh_anhs && prod.hinh_anhs.length > 0) {
        const mainImg = prod.hinh_anhs.find(img => img.la_anh_dai_dien === true || img.la_anh_dai_dien === 1);
        image = mainImg ? mainImg.duong_dan_anh : prod.hinh_anhs[0].duong_dan_anh;
      }

      return {
        id: prod.id,
        name: prod.ten_san_pham || prod.name || "",
        price: parseFloat(prod.gia_co_ban || prod.price || 0),
        gia_goc: prod.gia_goc ? parseFloat(prod.gia_goc) : null,
        image: image,
      };
    },

    /**
     * Load both wishlist and recently viewed lists
     */
    async loadAll() {
      await Promise.all([
        this.loadWishlist(),
        this.loadRecentlyViewed()
      ]);
    },

    /**
     * Load wishlist from backend (if logged in) or localStorage (if guest)
     */
    async loadWishlist() {
      if (this.isLoggedIn()) {
        this.loading = true;
        try {
          const res = await axios.get("/api/khach-hang/yeu-thich", {
            headers: this.getHeaders(),
          });
          if (res.data && res.data.status) {
            this.wishlist = res.data.wishlist.map(item => this._normalizeProduct(item));
          }
        } catch (error) {
          console.error("Lỗi khi tải danh sách yêu thích từ máy chủ:", error);
          this.loadLocalWishlist();
        } finally {
          this.loading = false;
        }
      } else {
        this.loadLocalWishlist();
      }
    },

    loadLocalWishlist() {
      try {
        const local = localStorage.getItem("wishlist");
        this.wishlist = local ? JSON.parse(local).map(item => this._normalizeProduct(item)) : [];
      } catch (e) {
        console.error("Lỗi đọc local wishlist:", e);
        this.wishlist = [];
      }
    },

    /**
     * Load recently viewed history
     */
    async loadRecentlyViewed() {
      if (this.isLoggedIn()) {
        this.loading = true;
        try {
          const res = await axios.get("/api/khach-hang/da-xem", {
            headers: this.getHeaders(),
          });
          if (res.data && res.data.status) {
            this.recentlyViewed = res.data.recentlyViewed.map(item => this._normalizeProduct(item));
          }
        } catch (error) {
          console.error("Lỗi khi tải danh sách đã xem từ máy chủ:", error);
          this.loadLocalRecentlyViewed();
        } finally {
          this.loading = false;
        }
      } else {
        this.loadLocalRecentlyViewed();
      }
    },

    loadLocalRecentlyViewed() {
      try {
        const local = localStorage.getItem("recentlyViewed");
        this.recentlyViewed = local ? JSON.parse(local).map(item => this._normalizeProduct(item)) : [];
      } catch (e) {
        console.error("Lỗi đọc local recentlyViewed:", e);
        this.recentlyViewed = [];
      }
    },

    /**
     * Toggle wishlist status for a product
     */
    async toggleWishlist(product) {
      if (!product || !product.id) return false;

      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/yeu-thich/toggle",
            { id_san_pham: product.id },
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.wishlist = res.data.wishlist.map(item => this._normalizeProduct(item));
            return res.data.is_wished;
          }
        } catch (error) {
          console.error("Lỗi khi toggle yêu thích:", error);
        }
        return false;
      } else {
        // Guest mode
        const normalized = this._normalizeProduct(product);
        const index = this.wishlist.findIndex(item => item.id === product.id);
        let isWished = false;

        if (index > -1) {
          this.wishlist.splice(index, 1);
          isWished = false;
        } else {
          this.wishlist.unshift(normalized);
          isWished = true;
        }

        localStorage.setItem("wishlist", JSON.stringify(this.wishlist));
        return isWished;
      }
    },

    /**
     * Check if a product is in the wishlist
     */
    isWished(productId) {
      return this.wishlist.some(item => item.id === productId);
    },

    /**
     * Add a product to recently viewed list
     */
    async addRecentlyViewed(product) {
      if (!product || !product.id) return;

      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/da-xem/add",
            { id_san_pham: product.id },
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.recentlyViewed = res.data.recentlyViewed.map(item => this._normalizeProduct(item));
          }
        } catch (error) {
          console.error("Lỗi khi thêm vào danh sách đã xem:", error);
        }
      } else {
        // Guest mode
        const normalized = this._normalizeProduct(product);
        
        // Remove existing to place at the top
        this.recentlyViewed = this.recentlyViewed.filter(item => item.id !== product.id);
        this.recentlyViewed.unshift(normalized);

        // Cap at 20 items
        if (this.recentlyViewed.length > 20) {
          this.recentlyViewed = this.recentlyViewed.slice(0, 20);
        }

        localStorage.setItem("recentlyViewed", JSON.stringify(this.recentlyViewed));
      }
    },

    /**
     * Sync local guest wishlist and viewed history with database upon login
     */
    async syncWithBackend() {
      if (!this.isLoggedIn()) return;

      // 1. Sync Wishlist
      const localWishlist = localStorage.getItem("wishlist");
      const localWishlistItems = localWishlist ? JSON.parse(localWishlist) : [];

      if (localWishlistItems.length > 0) {
        try {
          const productIds = localWishlistItems.map(item => item.id);
          await axios.post(
            "/api/khach-hang/yeu-thich/sync",
            { id_san_phams: productIds },
            { headers: this.getHeaders() }
          );
          localStorage.removeItem("wishlist");
        } catch (error) {
          console.error("Lỗi đồng bộ danh sách yêu thích:", error);
        }
      }

      // 2. Sync Recently Viewed
      const localRecent = localStorage.getItem("recentlyViewed");
      const localRecentItems = localRecent ? JSON.parse(localRecent) : [];

      if (localRecentItems.length > 0) {
        try {
          const productIds = localRecentItems.map(item => item.id);
          await axios.post(
            "/api/khach-hang/da-xem/sync",
            { id_san_phams: productIds },
            { headers: this.getHeaders() }
          );
          localStorage.removeItem("recentlyViewed");
        } catch (error) {
          console.error("Lỗi đồng bộ danh sách đã xem gần đây:", error);
        }
      }

      // Load fresh data from backend
      await this.loadAll();
    }
  }
});
