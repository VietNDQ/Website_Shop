import { defineStore } from 'pinia';
import axios from 'axios';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    loading: false,
  }),
  getters: {
    cartCount: (state) => {
      return state.items.reduce((total, item) => total + item.so_luong, 0);
    },
    cartTotal: (state) => {
      // Only sum items that are ticked (isSelected). Default to true if missing.
      return state.items.reduce((total, item) => {
        if (item.isSelected !== false) return total + (item.gia_ban * item.so_luong);
        return total;
      }, 0);
    },
    selectedCount: (state) => {
      return state.items.filter(item => item.isSelected !== false).length;
    },
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
     * Load cart items from API (if logged in) or LocalStorage (if guest)
     */
    async loadCart() {
      if (this.isLoggedIn()) {
        this.loading = true;
        try {
          const res = await axios.get("/api/khach-hang/gio-hang", {
            headers: this.getHeaders(),
          });
          if (res.data && res.data.status) {
            // Restore persisted selection state from localStorage
            const savedSelections = this._loadSavedSelections();
            this.items = res.data.cart.map(item => ({
              ...item,
              isSelected: savedSelections.hasOwnProperty(item.id_bien_the)
                ? savedSelections[item.id_bien_the]
                : true,
            }));
          }
        } catch (error) {
          console.error("Lỗi khi tải giỏ hàng từ máy chủ:", error);
          this.loadLocalCart();
        } finally {
          this.loading = false;
        }
      } else {
        this.loadLocalCart();
      }
    },

    // Save and load selection state in localStorage (works for both guest and logged-in)
    _saveSelections() {
      const map = {};
      this.items.forEach(item => { map[item.id_bien_the] = item.isSelected !== false; });
      localStorage.setItem("cart_selections", JSON.stringify(map));
    },

    _loadSavedSelections() {
      try {
        return JSON.parse(localStorage.getItem("cart_selections") || "{}");
      } catch { return {}; }
    },

    loadLocalCart() {
      const localCart = localStorage.getItem("cart");
      const parsed = localCart ? JSON.parse(localCart) : [];
      // Normalise isSelected for items that were saved without the flag
      this.items = parsed.map(item => ({ ...item, isSelected: item.isSelected !== false }));
    },

    /**
     * Add item to cart
     */
    async addItem(product, variant, qty = 1) {
      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/gio-hang/add",
            {
              id_bien_the: variant.id,
              so_luong: qty
            },
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.items = res.data.cart;
            return { success: true };
          }
        } catch (error) {
          console.error("Lỗi khi thêm vào giỏ hàng:", error);
          const msg = error.response?.data?.message || "Không thể thêm sản phẩm vào giỏ hàng.";
          return { success: false, message: msg };
        }
      } else {
        // Guest mode logic
        const existingIndex = this.items.findIndex(item => item.id_bien_the === variant.id);
        
        // Format attributes description (e.g. "Size: M - Màu: Đỏ")
        let attributesStr = "";
        if (variant.thuoc_tinh) {
          const attrs = [];
          for (const [key, value] of Object.entries(variant.thuoc_tinh)) {
            attrs.push(`${key}: ${value}`);
          }
          attributesStr = attrs.join(' - ');
        }

        const image = product.hinh_anhs && product.hinh_anhs.length > 0 
          ? product.hinh_anhs[0].duong_dan_anh 
          : "";

        if (existingIndex > -1) {
          // Check stock limit
          const newQty = this.items[existingIndex].so_luong + qty;
          if (variant.so_luong_ton_kho < newQty) {
            return { 
              success: false, 
              message: `Không đủ hàng tồn kho. Chỉ còn ${variant.so_luong_ton_kho} sản phẩm.` 
            };
          }
          this.items[existingIndex].so_luong = newQty;
        } else {
          if (variant.so_luong_ton_kho < qty) {
            return { 
              success: false, 
              message: `Không đủ hàng tồn kho. Chỉ còn ${variant.so_luong_ton_kho} sản phẩm.` 
            };
          }
          this.items.push({
            id_bien_the: variant.id,
            id_san_pham: product.id,
            ten_san_pham: product.ten_san_pham,
            ten_bien_the: attributesStr,
            gia_ban: parseFloat(variant.gia_ban),
            so_luong_ton_kho: variant.so_luong_ton_kho,
            hinh_anh: image,
            so_luong: qty,
            isSelected: true,
          });
        }
        
        localStorage.setItem("cart", JSON.stringify(this.items));
        // Dispatch event for backward compatibility with old scripts
        window.dispatchEvent(new Event("cartUpdated"));
        return { success: true };
      }
    },

    /**
     * Update quantity of a variant item
     */
    async updateQty(variantId, qty) {
      if (qty < 1) {
        return this.removeItem(variantId);
      }

      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/gio-hang/update",
            {
              id_bien_the: variantId,
              so_luong: qty
            },
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.items = res.data.cart;
            return { success: true };
          }
        } catch (error) {
          console.error("Lỗi khi cập nhật giỏ hàng:", error);
          const msg = error.response?.data?.message || "Không thể cập nhật số lượng.";
          return { success: false, message: msg };
        }
      } else {
        const index = this.items.findIndex(item => item.id_bien_the === variantId);
        if (index > -1) {
          // Check stock
          const item = this.items[index];
          if (item.so_luong_ton_kho < qty) {
            return { 
              success: false, 
              message: `Không đủ hàng tồn kho. Chỉ còn ${item.so_luong_ton_kho} sản phẩm.` 
            };
          }
          this.items[index].so_luong = qty;
          localStorage.setItem("cart", JSON.stringify(this.items));
          window.dispatchEvent(new Event("cartUpdated"));
        }
        return { success: true };
      }
    },

    /**
     * Remove item from cart
     */
    async removeItem(variantId) {
      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/gio-hang/remove",
            { id_bien_the: variantId },
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.items = res.data.cart;
            return { success: true };
          }
        } catch (error) {
          console.error("Lỗi khi xóa khỏi giỏ hàng:", error);
          return { success: false, message: "Không thể xóa sản phẩm khỏi giỏ hàng." };
        }
      } else {
        this.items = this.items.filter(item => item.id_bien_the !== variantId);
        localStorage.setItem("cart", JSON.stringify(this.items));
        window.dispatchEvent(new Event("cartUpdated"));
        return { success: true };
      }
    },

    /**
     * Merge guest cart into backend database cart on login
     */
    async syncCartWithBackend() {
      const localCart = localStorage.getItem("cart");
      const localItems = localCart ? JSON.parse(localCart) : [];

      if (localItems.length > 0 && this.isLoggedIn()) {
        try {
          const payload = localItems.map(item => ({
            id_bien_the: item.id_bien_the,
            so_luong: item.so_luong
          }));

          const res = await axios.post(
            "/api/khach-hang/gio-hang/sync",
            { cart_items: payload },
            { headers: this.getHeaders() }
          );

          if (res.data && res.data.status) {
            // Successfully merged! Clear local guest storage
            localStorage.removeItem("cart");
            this.items = res.data.cart;
          }
        } catch (error) {
          console.error("Lỗi khi đồng bộ giỏ hàng với máy chủ:", error);
          // If sync fails, load the backend cart anyway to stay authenticated
          await this.loadCart();
        }
      } else {
        // Just load backend cart
        await this.loadCart();
      }
      window.dispatchEvent(new Event("cartUpdated"));
    },

    /**
     * Clear all cart items
     */
    async clearCart() {
      if (this.isLoggedIn()) {
        try {
          const res = await axios.post(
            "/api/khach-hang/gio-hang/clear",
            {},
            { headers: this.getHeaders() }
          );
          if (res.data && res.data.status) {
            this.items = [];
          }
        } catch (error) {
          console.error("Lỗi khi làm sạch giỏ hàng:", error);
        }
      } else {
        this.items = [];
        localStorage.removeItem("cart");
      }
      window.dispatchEvent(new Event("cartUpdated"));
    },

    /**
     * Remove only the selected (checked-out) items, keeping unselected items in cart
     */
    async removeSelectedItems() {
      const selectedIds = this.items
        .filter(item => item.isSelected !== false)
        .map(item => item.id_bien_the);

      if (selectedIds.length === 0) return;

      for (const variantId of selectedIds) {
        await this.removeItem(variantId);
      }

      // Clear persisted selections for removed items
      const saved = this._loadSavedSelections();
      selectedIds.forEach(id => { delete saved[id]; });
      localStorage.setItem("cart_selections", JSON.stringify(saved));

      window.dispatchEvent(new Event("cartUpdated"));
    },

    /**
     * Toggle isSelected for a single item
     */
    toggleItemSelect(variantId) {
      const item = this.items.find(i => i.id_bien_the === variantId);
      if (item) {
        item.isSelected = !item.isSelected;
        // Always persist selection state
        this._saveSelections();
        if (!this.getToken()) {
          localStorage.setItem("cart", JSON.stringify(this.items));
        }
      }
    },

    /**
     * Select or deselect all items at once
     * @param {boolean} value - true = select all, false = deselect all
     */
    toggleSelectAll(value) {
      this.items.forEach(item => { item.isSelected = value; });
      // Always persist selection state
      this._saveSelections();
      if (!this.getToken()) {
        localStorage.setItem("cart", JSON.stringify(this.items));
      }
    },
  }
});
