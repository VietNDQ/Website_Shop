<template>
  <div class="cart-page pb-28">
    <div class="cart-container">
      <h1 class="cart-title">Giỏ Hàng Của Bạn</h1>

      <!-- Skeleton Loading State (Shimmer Effect) -->
      <div v-if="cartStore && cartStore.loading && cartStore.items.length === 0" class="cart-skeleton-container">
        <div class="cart-grid">
          <!-- Left Column Skeletons -->
          <div class="cart-items-column">
            <div class="skeleton-item-card" v-for="n in 3" :key="n">
              <div class="skeleton-img shimmer"></div>
              <div class="skeleton-details">
                <div class="skeleton-line title shimmer"></div>
                <div class="skeleton-line meta shimmer"></div>
                <div class="skeleton-line price shimmer"></div>
              </div>
            </div>
          </div>
          <!-- Right Column Skeleton -->
          <div class="cart-summary-column">
            <div class="skeleton-summary-card">
              <div class="skeleton-line title shimmer"></div>
              <div class="skeleton-line row shimmer"></div>
              <div class="skeleton-line row shimmer"></div>
              <div class="skeleton-line btn shimmer"></div>
            </div>
          </div>
        </div>
      </div>

      <template v-else-if="cartStore">
        <!-- Empty Cart State -->
        <div v-if="visibleItems.length === 0" class="cart-empty-state">
          <div class="empty-icon-wrapper">
            <i class="fa-solid fa-bag-shopping"></i>
          </div>
          <h2>Giỏ hàng của bạn đang trống</h2>
          <p>Hãy khám phá các mô hình chất lượng và thêm chúng vào giỏ hàng của bạn nhé!</p>
          <button class="btn-continue-shopping" @click="$router.push('/')">
            Tiếp tục mua sắm
          </button>
        </div>

        <!-- Cart Content Grid -->
        <div v-else class="cart-grid">
          <!-- Left Column: Items List -->
          <div class="cart-items-column">

            <!-- Select All Row -->
            <div class="flex items-center gap-3 px-1 pb-2 border-b border-gray-100 mb-1">
              <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <span class="relative flex items-center justify-center">
                  <input type="checkbox" class="sr-only" :checked="selectAll" @change="onSelectAllChange" />
                  <span
                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                    :class="selectAll ? 'bg-red-600 border-red-600' : 'bg-white border-gray-300'">
                    <svg v-if="selectAll" class="w-3 h-3 text-white" viewBox="0 0 12 10" fill="none">
                      <path d="M1 5l3.5 3.5L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </span>
                </span>
                <span class="text-sm font-semibold text-gray-700">
                  Chọn tất cả ({{ visibleItems.length }} sản phẩm)
                </span>
              </label>
            </div>

            <transition-group name="cart-item-anim" tag="div" class="cart-items-list">
              <div class="cart-card item-card" v-for="item in visibleItems" :key="item.id_bien_the">
                <!-- Per-item checkbox -->
                <label class="item-checkbox-label flex-shrink-0 cursor-pointer self-center">
                  <input type="checkbox" class="sr-only" :checked="item.isSelected !== false"
                    @change="cartStore.toggleItemSelect(item.id_bien_the)" />
                  <span
                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                    :class="item.isSelected !== false ? 'bg-red-600 border-red-600' : 'bg-white border-gray-300'">
                    <svg v-if="item.isSelected !== false" class="w-3 h-3 text-white" viewBox="0 0 12 10" fill="none">
                      <path d="M1 5l3.5 3.5L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </span>
                </label>

                <!-- Image with Lazy Loading & Fade-in transition -->
                <div class="item-img-wrapper" :class="{ 'loaded': isImageLoaded(item.id_bien_the) }">
                  <img :src="getProductImageUrl(item.hinh_anh)" :alt="item.ten_san_pham" loading="lazy"
                    @load="onImageLoaded(item.id_bien_the)" />
                  <div v-if="!isImageLoaded(item.id_bien_the)" class="img-skeleton shimmer"></div>
                </div>

                <div class="item-details">
                  <div class="item-header">
                    <h3 class="item-name" @click="$router.push(`/product/${item.id_san_pham}`)">
                      {{ item.ten_san_pham }}
                    </h3>
                    <!-- Trash button triggers Soft Delete -->
                    <button class="btn-remove-item" @click="removeItem(item.id_bien_the)" title="Xóa khỏi giỏ hàng">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </div>

                  <!-- Variant Selection & Quick Edit Dropdown Trigger -->
                  <div class="item-variant">
                    <span class="variant-text">{{ item.ten_bien_the || 'Mặc định' }}</span>
                    <button class="btn-quick-edit" @click="openQuickEdit(item)">
                      <span>Thay đổi</span>
                      <i class="fa-solid fa-chevron-down"></i>
                    </button>
                  </div>

                  <!-- Stock Scarcity Alert (FOMO) -->
                  <div v-if="item.so_luong_ton_kho <= 5" class="scarcity-tag">
                    <i class="fa-solid fa-fire-flame-curved"></i> Chỉ còn {{ item.so_luong_ton_kho }} sản phẩm cuối
                    cùng!
                  </div>

                  <div class="item-footer">
                    <!-- Quantity Control Widget -->
                    <div class="quantity-controller">
                      <button class="qty-btn" @click="decrementQty(item)" :disabled="item.so_luong <= 1">
                        <i class="fa-solid fa-minus"></i>
                      </button>
                      <input type="number" class="qty-input" v-model.number="item.so_luong" @change="updateQty(item)"
                        min="1" :max="item.so_luong_ton_kho" />
                      <button class="qty-btn" @click="incrementQty(item)"
                        :disabled="item.so_luong >= item.so_luong_ton_kho">
                        <i class="fa-solid fa-plus"></i>
                      </button>
                    </div>

                    <!-- Prices Block -->
                    <div class="item-price-block">
                      <span class="item-single-price">{{ formatCurrency(item.gia_ban) }}</span>
                      <span class="item-total-price">{{ formatCurrency(item.gia_ban * item.so_luong) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </transition-group>
          </div>

        </div>
      </template>


      <!-- Custom Interactive Undo Toast Popup -->
      <div class="undo-toast" :class="{ 'visible': undoToastVisible }">
        <div class="undo-toast-inner">
          <div class="undo-toast-body">
            <i class="fa-solid fa-trash-can-arrow-up undo-icon"></i>
            <div class="undo-text">
              <p>Đã xóa sản phẩm khỏi giỏ hàng</p>
              <span class="undo-item-name" v-if="undoTargetItem">{{ undoTargetItem.ten_san_pham }}</span>
            </div>
          </div>
          <button class="btn-undo-action" @click="undoRemove">
            <i class="fa-solid fa-rotate-left"></i> Hoàn tác
          </button>
        </div>
        <div class="undo-countdown-bar" :style="{ transform: `scaleX(${undoCountdownProgress})` }"></div>
      </div>

      <!-- Quick Variant Edit Dialog (Glassmorphism modal) -->
      <div v-if="showQuickEdit" class="quick-edit-modal-overlay" @click.self="closeQuickEdit">
        <div class="quick-edit-card glass-modal">
          <div class="modal-header">
            <h3>Thay Đổi Phân Loại</h3>
            <button class="btn-close-modal" @click="closeQuickEdit">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div v-if="loadingProductDetails" class="modal-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Đang tải thông số sản phẩm...
          </div>

          <div v-else class="modal-body">
            <div class="modal-item-info">
              <img :src="getProductImageUrl(activeEditItem?.hinh_anh)" alt="Product"
                class="modal-item-img" />
              <div>
                <h4>{{ activeEditItem.ten_san_pham }}</h4>
                <p class="modal-item-price">{{ formatCurrency(activeEditItem.gia_ban) }}</p>
              </div>
            </div>

            <!-- Custom Color pills -->
            <div class="option-group" v-if="availableColors.length > 0">
              <label class="option-label">Màu sắc</label>
              <div class="option-pills">
                <button v-for="color in availableColors" :key="color" class="pill-btn"
                  :class="{ 'active': selectedColor === color }" @click="selectColor(color)">
                  {{ color }}
                </button>
              </div>
            </div>

            <!-- Custom Size pills -->
            <div class="option-group" v-if="availableSizes.length > 0">
              <label class="option-label">Kích thước</label>
              <div class="option-pills">
                <button v-for="size in availableSizes" :key="size" class="pill-btn" :class="{
                  'active': selectedSize === size,
                  'disabled': !isSizeAvailable(size)
                }" :disabled="!isSizeAvailable(size)" @click="selectSize(size)">
                  {{ size }}
                </button>
              </div>
            </div>

            <!-- Stock status indicator -->
            <div v-if="selectedVariantStock !== null" class="modal-stock-info"
              :class="{ 'warning': selectedVariantStock <= 5 }">
              <i class="fa-solid" :class="selectedVariantStock <= 5 ? 'fa-fire-flame-curved' : 'fa-check'"></i>
              Số lượng tồn kho khả dụng: <strong>{{ selectedVariantStock }}</strong> sản phẩm
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-cancel" @click="closeQuickEdit">Hủy bỏ</button>
            <button class="btn-save" :disabled="!isSelectionValid || isSavingEdit" @click="saveVariantEdit">
              <i class="fa-solid fa-spinner fa-spin" v-if="isSavingEdit"></i>
              {{ isSavingEdit ? ' Đang cập nhật...' : 'Cập nhật' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cross-selling Section -->
      <div class="cross-selling-section">
        <h2 class="section-title">Có Thể Bạn Cũng Thích</h2>
        <div v-if="loadingRecommendations" class="rec-loading">
          <i class="fa-solid fa-spinner fa-spin"></i> Đang chuẩn bị gợi ý...
        </div>
        <div v-else class="recommendations-grid">
          <div v-for="prod in recommendations" :key="prod.id" class="rec-card"
            @click="$router.push(`/product/${prod.id}`)">
            <div class="rec-img-wrapper">
              <img :src="getProductImage(prod)" :alt="prod.ten_san_pham" />
            </div>
            <div class="rec-info">
              <h3 class="rec-name">{{ prod.ten_san_pham }}</h3>
              <p class="rec-price">{{ formatCurrency(prod.gia_co_ban) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ========= STICKY BOTTOM BAR ========= -->
      <div style="background-color: #EDE9E6;" v-if="cartStore && visibleItems.length > 0"
        class="fixed bottom-0 left-0 w-full bg-gray-100 border-t border-gray-300 shadow-[0_-8px_24px_rgba(0,0,0,0.08)] z-50 flex justify-center">
        <div style="padding: 5px; background-color: #EDE9E6; padding: 6px ;"
          class="w-full bg-gray-100 max-w-3xl flex items-center justify-between py-5">

          <div class="flex items-center ">
            <label class="flex items-center gap-3 cursor-pointer group">
              <input type="checkbox" :checked="selectAll" @change="onSelectAllChange"
                class="w-5 h-5 text-red-600 bg-white border-gray-300 rounded focus:ring-red-500 cursor-pointer">
              <span class="text-gray-700 font-semibold select-none group-hover:text-red-600 text-base">
                Chọn tất cả ({{ visibleItems.length }} sản phẩm)
              </span>
            </label>
          </div>

          <div class="flex items-center gap-4">

            <div class="flex flex-col text-right justify-center">

              <div class="flex items-baseline justify-end gap-2 mb-1">
                <span class="text-xl text-gray-800 font-medium">
                  Tạm tính:
                </span>
                <span class="text-2xl font-black text-red-600 tracking-tight leading-none">
                  {{ formatCurrency(cartStore.cartTotal) }}
                </span>
              </div>

              <p class="text-xs text-gray-400">
                Chưa bao gồm phí vận chuyển
              </p>

            </div>

            <button @click="proceedCheckout" :disabled="cartStore.selectedCount === 0"
              style="background-color: #e30019; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 500; border: none; cursor: pointer;">
              Mua ngay ({{ cartStore.selectedCount }})
            </button>

          </div>

        </div>
      </div>

  </div>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../store/cartStore";

export default {
  name: "Cart",
  data() {
    return {
      cartStore: null,
      voucherCode: "",
      isVoucherApplied: false,
      voucherMessage: "",
      discountPercent: 0,
      discountFixed: 0,

      // Image loading trackers
      loadedImages: [],

      // Soft delete states
      softDeletedItemIds: [],
      undoToastVisible: false,
      undoTargetItem: null,
      undoTimerId: null,
      undoProgressIntervalId: null,
      undoTimeTotal: 4000, // 4 seconds soft delete delay
      undoTimeRemaining: 4000,

      // Quick variant edit states
      showQuickEdit: false,
      loadingProductDetails: false,
      isSavingEdit: false,
      activeEditItem: null,
      productDetailData: null,
      selectedColor: "",
      selectedSize: "",

      // Cross selling recommendations
      loadingRecommendations: true,
      recommendations: [],
    };
  },
  computed: {
    visibleItems() {
      if (!this.cartStore) return [];
      return this.cartStore.items.filter(item => !this.softDeletedItemIds.includes(item.id_bien_the));
    },
    selectAll() {
      if (!this.cartStore || this.visibleItems.length === 0) return false;
      return this.visibleItems.every(item => item.isSelected !== false);
    },
    undoCountdownProgress() {
      return this.undoTimeRemaining / this.undoTimeTotal;
    },
    discountAmount() {
      if (!this.cartStore) return 0;
      let amount = 0;
      if (this.discountPercent > 0) {
        amount += (this.cartStore.cartTotal * this.discountPercent) / 100;
      }
      amount += this.discountFixed;
      return Math.min(amount, this.cartStore.cartTotal);
    },
    finalTotal() {
      if (!this.cartStore) return 0;
      const base = this.cartStore.cartTotal - this.discountAmount + 30000;
      return Math.max(0, base);
    },
    availableColors() {
      if (!this.productDetailData || !this.productDetailData.bien_thes) return [];
      const colors = new Set();
      this.productDetailData.bien_thes.forEach(variant => {
        if (variant.thuoc_tinh && variant.thuoc_tinh.Color) {
          colors.add(variant.thuoc_tinh.Color);
        } else if (variant.thuoc_tinh && variant.thuoc_tinh["Màu sắc"]) {
          colors.add(variant.thuoc_tinh["Màu sắc"]);
        }
      });
      return Array.from(colors);
    },
    availableSizes() {
      if (!this.productDetailData || !this.productDetailData.bien_thes) return [];
      const sizes = new Set();
      this.productDetailData.bien_thes.forEach(variant => {
        if (variant.thuoc_tinh && variant.thuoc_tinh.Size) {
          sizes.add(variant.thuoc_tinh.Size);
        } else if (variant.thuoc_tinh && variant.thuoc_tinh["Kích thước"]) {
          sizes.add(variant.thuoc_tinh["Kích thước"]);
        }
      });
      return Array.from(sizes);
    },
    selectedVariant() {
      if (!this.productDetailData || !this.productDetailData.bien_thes) return null;
      return this.productDetailData.bien_thes.find(variant => {
        const color = variant.thuoc_tinh?.Color || variant.thuoc_tinh?.["Màu sắc"];
        const size = variant.thuoc_tinh?.Size || variant.thuoc_tinh?.["Kích thước"];
        return color === this.selectedColor && size === this.selectedSize;
      });
    },
    selectedVariantStock() {
      return this.selectedVariant ? this.selectedVariant.so_luong_ton_kho : null;
    },
    isSelectionValid() {
      if (!this.selectedVariant) return false;
      return this.selectedVariant.id !== this.activeEditItem?.id_bien_the;
    }
  },
  created() {
    this.cartStore = useCartStore();
    this.cartStore.loadCart();
    this.loadRecommendations();
  },
  beforeUnmount() {
    // If user navigates away while there is a pending soft delete, commit it instantly.
    if (this.undoToastVisible && this.undoTargetItem) {
      this.commitRemoveItem();
    }
  },
  methods: {
    getProductImageUrl(imagePath) {
      if (!imagePath) return "https://via.placeholder.com/150";
      if (imagePath.startsWith("http")) return imagePath;
      return "http://127.0.0.1:8000" + (imagePath.startsWith("/") ? "" : "/") + imagePath;
    },

    formatCurrency(val) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(val);
    },

    // Lazy load image tracking
    onImageLoaded(id) {
      if (!this.loadedImages.includes(id)) {
        this.loadedImages.push(id);
      }
    },
    isImageLoaded(id) {
      return this.loadedImages.includes(id);
    },

    async updateQty(item) {
      if (item.so_luong < 1) item.so_luong = 1;
      if (item.so_luong > item.so_luong_ton_kho) {
        item.so_luong = item.so_luong_ton_kho;
        this.showToast(`Chỉ còn tối đa ${item.so_luong_ton_kho} sản phẩm trong kho.`, "warning");
      }

      const result = await this.cartStore.updateQty(item.id_bien_the, item.so_luong);
      if (!result.success) {
        this.showToast(result.message, "error");
        await this.cartStore.loadCart();
      }
    },

    async incrementQty(item) {
      if (item.so_luong < item.so_luong_ton_kho) {
        item.so_luong++;
        await this.updateQty(item);
      }
    },

    async decrementQty(item) {
      if (item.so_luong > 1) {
        item.so_luong--;
        await this.updateQty(item);
      }
    },

    // Soft delete (Undo mechanism)
    removeItem(variantId) {
      // If there is already a pending delete, commit it first
      if (this.undoToastVisible && this.undoTargetItem) {
        this.commitRemoveItem();
      }

      // Find item
      const item = this.cartStore.items.find(i => i.id_bien_the === variantId);
      if (!item) return;

      // Soft delete on FE
      this.softDeletedItemIds.push(variantId);
      this.undoTargetItem = item;
      this.undoToastVisible = true;
      this.undoTimeRemaining = this.undoTimeTotal;

      // Start countdown progress bar
      const tick = 50;
      this.undoProgressIntervalId = setInterval(() => {
        this.undoTimeRemaining -= tick;
        if (this.undoTimeRemaining <= 0) {
          clearInterval(this.undoProgressIntervalId);
        }
      }, tick);

      // Start delete commit timer
      this.undoTimerId = setTimeout(() => {
        this.commitRemoveItem();
      }, this.undoTimeTotal);
    },

    async commitRemoveItem() {
      // Clear timers
      clearTimeout(this.undoTimerId);
      clearInterval(this.undoProgressIntervalId);

      if (this.undoTargetItem) {
        const variantId = this.undoTargetItem.id_bien_the;
        const result = await this.cartStore.removeItem(variantId);

        // Remove from local array
        this.softDeletedItemIds = this.softDeletedItemIds.filter(id => id !== variantId);

        if (!result.success) {
          this.showToast(result.message || "Không thể xóa sản phẩm.", "error");
          // Revert if error
          await this.cartStore.loadCart();
        }
      }

      this.undoTargetItem = null;
      this.undoToastVisible = false;
    },

    undoRemove() {
      // Clear timers
      clearTimeout(this.undoTimerId);
      clearInterval(this.undoProgressIntervalId);

      if (this.undoTargetItem) {
        const variantId = this.undoTargetItem.id_bien_the;
        // Restore on FE
        this.softDeletedItemIds = this.softDeletedItemIds.filter(id => id !== variantId);
        this.showToast(`Đã khôi phục sản phẩm: ${this.undoTargetItem.ten_san_pham}`, "success");
      }

      this.undoTargetItem = null;
      this.undoToastVisible = false;
    },

    applyVoucher() {
      if (this.isVoucherApplied) {
        this.isVoucherApplied = false;
        this.voucherCode = "";
        this.discountPercent = 0;
        this.discountFixed = 0;
        this.voucherMessage = "";
        return;
      }

      if (!this.voucherCode.trim()) {
        this.showToast("Vui lòng nhập mã giảm giá!", "warning");
        return;
      }

      const code = this.voucherCode.toUpperCase().trim();

      if (code === "WELCOME10" || code === "BALAB10") {
        this.discountPercent = 10;
        this.isVoucherApplied = true;
        this.voucherMessage = "Áp dụng mã giảm giá 10% thành công!";
        this.showToast("Áp dụng mã giảm giá thành công!", "success");
      } else if (code === "WELCOME50" || code === "GIAM50") {
        this.discountFixed = 50000;
        this.isVoucherApplied = true;
        this.voucherMessage = "Áp dụng mã giảm giá 50.000đ thành công!";
        this.showToast("Áp dụng mã giảm giá thành công!", "success");
      } else {
        this.isVoucherApplied = false;
        this.voucherMessage = "Mã giảm giá không hợp lệ hoặc đã hết hạn.";
        this.showToast("Mã giảm giá không hợp lệ.", "error");
      }
    },

    onSelectAllChange(e) {
      this.cartStore.toggleSelectAll(e.target.checked);
    },

    proceedCheckout() {
      if (!this.cartStore || this.cartStore.selectedCount === 0) {
        this.showToast('Vui lòng chọn ít nhất một sản phẩm để thanh toán!', 'warning');
        return;
      }
      sessionStorage.removeItem("buy_now_checkout_item");
      this.$router.push('/thanh-toan');
    },

    showToast(message, type = "success") {
      if (this.$toast) {
        this.$toast[type](message);
      } else {
        console.log(`[Toast ${type}]: ${message}`);
      }
    },

    async loadRecommendations() {
      try {
        const res = await axios.get("http://127.0.0.1:8000/api/san-pham", {
          params: { sort: "newest" }
        });
        if (res.data && res.data.data) {
          this.recommendations = res.data.data.slice(0, 4);
        }
      } catch (error) {
        console.error("Lỗi khi tải gợi ý mua sắm:", error);
      } finally {
        this.loadingRecommendations = false;
      }
    },

    getProductImage(prod) {
      if (prod.hinh_anhs && prod.hinh_anhs.length > 0) {
        const main = prod.hinh_anhs.find(img => img.la_anh_dai_dien) || prod.hinh_anhs[0];
        const path = main.duong_dan_anh;
        if (!path) return 'https://via.placeholder.com/150';
        if (path.startsWith('http')) return path;
        return 'http://127.0.0.1:8000' + (path.startsWith('/') ? '' : '/') + path;
      }
      return 'https://via.placeholder.com/150';
    },

    // Quick variant edit methods
    async openQuickEdit(item) {
      this.activeEditItem = item;
      this.showQuickEdit = true;
      this.loadingProductDetails = true;
      this.selectedColor = "";
      this.selectedSize = "";
      this.productDetailData = null;

      try {
        const res = await axios.get(`http://127.0.0.1:8000/api/san-pham/${item.id_san_pham}`);
        if (res.data) {
          this.productDetailData = res.data;
          const currentVariant = this.productDetailData.bien_thes.find(v => v.id === item.id_bien_the);
          if (currentVariant && currentVariant.thuoc_tinh) {
            this.selectedColor = currentVariant.thuoc_tinh.Color || currentVariant.thuoc_tinh["Màu sắc"] || "";
            this.selectedSize = currentVariant.thuoc_tinh.Size || currentVariant.thuoc_tinh["Kích thước"] || "";
          }
        }
      } catch (error) {
        console.error("Lỗi tải thông tin biến thể:", error);
        this.showToast("Không thể tải thông tin biến thể sản phẩm.", "error");
        this.closeQuickEdit();
      } finally {
        this.loadingProductDetails = false;
      }
    },

    closeQuickEdit() {
      this.showQuickEdit = false;
      this.activeEditItem = null;
      this.productDetailData = null;
    },

    selectColor(color) {
      this.selectedColor = color;
      if (this.selectedSize && !this.isSizeAvailable(this.selectedSize)) {
        this.selectedSize = "";
      }
    },

    selectSize(size) {
      if (this.isSizeAvailable(size)) {
        this.selectedSize = size;
      }
    },

    isSizeAvailable(size) {
      if (!this.productDetailData || !this.selectedColor) return true;
      return this.productDetailData.bien_thes.some(v => {
        const color = v.thuoc_tinh?.Color || v.thuoc_tinh?.["Màu sắc"];
        const s = v.thuoc_tinh?.Size || v.thuoc_tinh?.["Kích thước"];
        return color === this.selectedColor && s === size && v.so_luong_ton_kho > 0;
      });
    },

    async saveVariantEdit() {
      if (!this.selectedVariant) return;

      this.isSavingEdit = true;
      const originalQty = this.activeEditItem.so_luong;

      try {
        await this.cartStore.removeItem(this.activeEditItem.id_bien_the);
        const result = await this.cartStore.addItem(this.productDetailData, this.selectedVariant, originalQty);

        if (result.success) {
          this.showToast("Cập nhật phân loại sản phẩm thành công.", "success");
          this.closeQuickEdit();
        } else {
          this.showToast(result.message || "Cập nhật phân loại thất bại.", "error");
          const oldVariant = this.productDetailData.bien_thes.find(v => v.id === this.activeEditItem.id_bien_the);
          if (oldVariant) {
            await this.cartStore.addItem(this.productDetailData, oldVariant, originalQty);
          }
        }
      } catch (error) {
        console.error("Lỗi cập nhật biến thể:", error);
        this.showToast("Cập nhật phân loại thất bại.", "error");
      } finally {
        this.isSavingEdit = false;
        await this.cartStore.loadCart();
      }
    }
  }
};
</script>

<style scoped>
.cart-page {
  background-color: #f7fafc;
  min-height: 100vh;
  padding: 16px 20px 40px 20px;
  font-family: 'Outfit', sans-serif;
  color: #1a202c;
}

.cart-container {
  max-width: 750px;
  margin: 0 auto;
  padding-bottom: 80px;
}

.cart-title {
  font-size: 28px;
  font-weight: 800;
  margin-top: 0;
  margin-bottom: 16px;
  text-align: left;
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Skeleton Loading Shimmer Animation */
.shimmer {
  background: linear-gradient(90deg, #e2e8f0 25%, #edf2f7 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: loadingShimmer 1.5s infinite linear;
}

@keyframes loadingShimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.cart-skeleton-container {
  width: 100%;
}

.skeleton-item-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #edf2f7;
  padding: 20px;
  display: flex;
  gap: 20px;
  margin-bottom: 16px;
}

.skeleton-img {
  width: 100px;
  height: 100px;
  border-radius: 12px;
  flex-shrink: 0;
}

.skeleton-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: center;
}

.skeleton-line {
  height: 14px;
  border-radius: 4px;
}

.skeleton-line.title {
  width: 60%;
  height: 18px;
}

.skeleton-line.meta {
  width: 30%;
}

.skeleton-line.price {
  width: 40%;
}

.skeleton-summary-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #edf2f7;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.skeleton-line.btn {
  height: 48px;
  border-radius: 10px;
  margin-top: 10px;
}

/* Image loading placeholder fade-in */
.item-img-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 12px;
  overflow: hidden;
  background-color: #f1f5f9;
  border: 1px solid #e2e8f0;
  flex-shrink: 0;
}

.item-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.item-img-wrapper.loaded img {
  opacity: 1;
}

.img-skeleton {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

/* Soft delete transitions list */
.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item-anim-enter-active,
.cart-item-anim-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.cart-item-anim-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.cart-item-anim-leave-to {
  opacity: 0;
  transform: scale(0.9);
  max-height: 0;
  margin: 0 !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  border: 0 !important;
}

/* Custom interactive Undo Toast */
.undo-toast {
  position: fixed;
  bottom: 24px;
  left: 24px;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(10px);
  color: white;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  width: 360px;
  z-index: 2500;
  transform: translateY(120px);
  opacity: 0;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s;
  pointer-events: none;
}

.undo-toast.visible {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}

.undo-toast-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
}

.undo-toast-body {
  display: flex;
  align-items: center;
  gap: 12px;
}

.undo-icon {
  font-size: 20px;
  color: #ef4444;
}

.undo-text {
  text-align: left;
}

.undo-text p {
  margin: 0;
  font-size: 13px;
  font-weight: 500;
  color: #cbd5e1;
}

.undo-item-name {
  font-size: 14px;
  font-weight: 700;
  color: #ffffff;
  display: block;
  max-width: 160px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-undo-action {
  background: #ffffff;
  color: #0f172a;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
}

.btn-undo-action:hover {
  background: #f1f5f9;
  transform: scale(1.05);
}

.undo-countdown-bar {
  height: 4px;
  background: linear-gradient(90deg, #ef4444, #f87171);
  transform-origin: left;
  transition: transform 0.05s linear;
}

/* Empty Cart State */
.cart-empty-state {
  text-align: center;
  background: #ffffff;
  padding: 60px 40px;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid #edf2f7;
  max-width: 600px;
  margin: 40px auto;
}

.empty-icon-wrapper {
  font-size: 64px;
  color: #a0aec0;
  margin-bottom: 20px;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-10px);
  }
}

.cart-empty-state h2 {
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 12px;
  color: #2d3748;
}

.cart-empty-state p {
  color: #718096;
  margin-bottom: 30px;
  font-size: 15px;
}

.btn-continue-shopping {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 12px 28px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 99px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
  transition: all 0.2s ease;
}

.btn-continue-shopping:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
}

/* Cart Grid Layout */
.cart-grid {
  display: block;
}

.cart-items-column {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #edf2f7;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
}

/* Cart Item Card */
.item-card {
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.25s ease;
}

.item-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
}

/* Per-item checkbox wrapper */
.item-checkbox-label {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 2px;
}

.item-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.item-name {
  font-size: 16px;
  font-weight: 700;
  color: #1a202c;
  cursor: pointer;
  transition: color 0.15s ease;
  line-height: 1.4;
  text-align: left;
}

.item-name:hover {
  color: #3b82f6;
}

.btn-remove-item {
  background: none;
  border: none;
  color: #a0aec0;
  cursor: pointer;
  padding: 6px;
  font-size: 16px;
  transition: color 0.15s ease, transform 0.15s ease;
}

.btn-remove-item:hover {
  color: #e53e3e;
  transform: scale(1.1);
}

.item-variant {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 6px;
  justify-content: flex-start;
}

.variant-text {
  font-size: 13px;
  color: #718096;
  font-weight: 600;
  background-color: #f7fafc;
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid #edf2f7;
}

.btn-quick-edit {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.15s ease;
}

.btn-quick-edit:hover {
  background-color: #ebf8ff;
}

/* Scarcity / FOMO alert styling */
.scarcity-tag {
  color: #dd6b20;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  background-color: #fffaf0;
  border: 1px solid #feebc8;
  padding: 4px 10px;
  border-radius: 6px;
  align-self: flex-start;
  animation: blink 2s infinite ease-in-out;
}

@keyframes blink {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.8;
  }
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 14px;
}

/* Quantity selector */
.quantity-controller {
  display: flex;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  background-color: #fff;
}

.qty-btn {
  background: #f8fafc;
  border: none;
  width: 32px;
  height: 32px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  font-size: 12px;
  color: #4a5568;
  transition: background-color 0.15s;
}

.qty-btn:hover:not(:disabled) {
  background-color: #edf2f7;
}

.qty-btn:disabled {
  color: #cbd5e0;
  cursor: not-allowed;
}

.qty-input {
  width: 44px;
  height: 32px;
  border: none;
  border-left: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
  text-align: center;
  font-size: 14px;
  font-weight: 700;
  color: #2d3748;
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.item-price-block {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.item-single-price {
  font-size: 12px;
  color: #718096;
  font-weight: 500;
}

.item-total-price {
  font-size: 16px;
  font-weight: 800;
  color: #dc2626;
  margin-top: 2px;
}

/* Summary Card */
.summary-card {
  padding: 24px;
  position: sticky;
  top: 100px;
}

.summary-title {
  font-size: 20px;
  font-weight: 800;
  margin-bottom: 20px;
  color: #1a202c;
  border-bottom: 1px solid #edf2f7;
  padding-bottom: 12px;
  text-align: left;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #4a5568;
  margin-bottom: 14px;
}

.summary-value {
  font-weight: 700;
  color: #2d3748;
}

.summary-value.discount {
  color: #e53e3e;
}

.voucher-section {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}

.voucher-input {
  flex-grow: 1;
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  outline: none;
}

.voucher-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.btn-apply-voucher {
  background-color: #1a202c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-apply-voucher:hover {
  background-color: #2d3748;
}

.btn-apply-voucher.applied {
  background-color: #e2e8f0;
  color: #4a5568;
}

.btn-apply-voucher.applied:hover {
  background-color: #fed7d7;
  color: #c53030;
}

.voucher-msg {
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 14px;
  text-align: left;
}

.voucher-msg.success {
  color: #38a169;
}

.voucher-msg.error {
  color: #e53e3e;
}

.summary-divider {
  border: 0;
  border-top: 1px solid #edf2f7;
  margin: 18px 0;
}

.total-row {
  font-size: 16px;
  color: #1a202c;
  margin-bottom: 24px;
}

.total-row span {
  font-weight: 800;
}

.summary-total-value {
  font-size: 20px;
  font-weight: 900;
  color: #dc2626;
}

.btn-checkout {
  width: 100%;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  padding: 14px 20px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
  transition: all 0.2s ease;
}

.btn-checkout:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(220, 38, 38, 0.25);
  background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
}

/* Quick variant edit glass modal */
.quick-edit-modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  padding: 0px;
}

.quick-edit-card {
  width: 100%;
  max-width: 440px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalScaleUp {
  from {
    transform: scale(0.95);
    opacity: 0;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

.modal-header {
  padding: 18px 20px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 17px;
  font-weight: 800;
  color: #1a202c;
  margin: 0;
}

.btn-close-modal {
  background: none;
  border: none;
  color: #718096;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
  transition: color 0.15s;
}

.btn-close-modal:hover {
  color: #2d3748;
}

.modal-loading {
  padding: 60px 0;
  text-align: center;
  color: #718096;
}

.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
  max-height: 380px;
}

.modal-item-info {
  display: flex;
  gap: 16px;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px dashed #edf2f7;
}

.modal-item-img {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid #edf2f7;
}

.modal-item-info h4 {
  font-size: 14px;
  font-weight: 700;
  margin: 0 0 4px 0;
  text-align: left;
  line-height: 1.4;
}

.modal-item-price {
  font-size: 14px;
  font-weight: 800;
  color: #2b6cb0;
  margin: 0;
  text-align: left;
}

.option-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
}

.option-label {
  font-size: 13px;
  font-weight: 700;
  color: #4a5568;
}

.option-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill-btn {
  background-color: #f7fafc;
  border: 1px solid #e2e8f0;
  color: #4a5568;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.pill-btn:hover:not(.disabled) {
  border-color: #3b82f6;
  color: #3b82f6;
}

.pill-btn.active {
  background-color: #ebf8ff;
  border-color: #3b82f6;
  color: #3b82f6;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);
}

.pill-btn.disabled {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
  color: #cbd5e0;
  cursor: not-allowed;
  text-decoration: line-through;
}

.modal-stock-info {
  font-size: 12px;
  font-weight: 700;
  color: #38a169;
  background-color: #f0fff4;
  border: 1px solid #c6f6d5;
  padding: 8px 12px;
  border-radius: 8px;
  text-align: left;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-stock-info.warning {
  color: #dd6b20;
  background-color: #fffaf0;
  border-color: #feebc8;
}

.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #edf2f7;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancel {
  background-color: #f7fafc;
  border: 1px solid #cbd5e0;
  color: #4a5568;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.15s;
}

.btn-cancel:hover {
  background-color: #edf2f7;
}

.btn-save {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 10px 22px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.25);
}

.btn-save:disabled {
  background: #cbd5e0;
  color: #a0aec0;
  cursor: not-allowed;
  box-shadow: none;
}

/* Recommendations styling */
.cross-selling-section {
  max-width: 1200px;
  margin: 0px auto 0 auto;
  border-top: 1px solid #e2e8f0;
  padding-top: 40px;
}

.section-title {
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 24px;
  color: #1a202c;
  text-align: left;
}

.rec-loading {
  padding: 40px;
  color: #718096;
}

.recommendations-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.rec-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #edf2f7;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);
}

.rec-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border-color: #e2e8f0;
}

.rec-img-wrapper {
  aspect-ratio: 1;
  overflow: hidden;
  background-color: #f7fafc;
}

.rec-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.rec-card:hover .rec-img-wrapper img {
  transform: scale(1.05);
}

.rec-info {
  padding: 12px;
  text-align: left;
}

.rec-name {
  font-size: 14px;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 6px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.rec-price {
  font-size: 14px;
  font-weight: 800;
  color: #e53e3e;
  margin: 0;
}


/* Responsive queries */
@media (max-width: 991px) {
  .cart-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .recommendations-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .cart-page {
    padding: 20px 10px;
  }

  .cart-title {
    font-size: 22px;
    margin-bottom: 20px;
  }

  .item-card {
    padding: 12px;
    gap: 12px;
  }

  .item-img-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 8px;
  }

  .item-name {
    font-size: 14px;
  }

  .variant-text {
    font-size: 11px;
    padding: 2px 6px;
  }

  .btn-quick-edit {
    font-size: 11px;
    padding: 2px 4px;
  }

  .item-total-price {
    font-size: 14px;
  }

  .summary-card {
    position: static;
  }


}
</style>

