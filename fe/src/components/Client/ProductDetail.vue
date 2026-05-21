<template>
  <div class="pd-page">
    <div v-if="loading" class="pd-loading">
      <i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải thông tin sản phẩm...
    </div>
    
    <div v-else-if="!product" class="pd-not-found">
      <div class="not-found-card">
        <h2>Không tìm thấy sản phẩm</h2>
        <p>Sản phẩm có thể đã ngừng kinh doanh hoặc đường dẫn không hợp lệ.</p>
        <router-link to="/" class="btn-back-home">Quay lại Trang chủ</router-link>
      </div>
    </div>
    
    <div v-else>
      <!-- Breadcrumb -->
      <div class="pd-breadcrumb">
        <div class="pd-container">
          <router-link to="/" class="bc-link">Trang chủ</router-link>
          <span class="bc-sep">›</span>
          <span class="bc-link">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : 'Mô hình' }}</span>
          <span class="bc-sep">›</span>
          <span class="bc-current">{{ product.ten_san_pham }}</span>
        </div>
      </div>
      
      <!-- Main content -->
      <div class="pd-container pd-main">
        <!-- LEFT: Gallery -->
        <div class="pd-gallery">
          <div class="pd-main-img-wrap">
            <span class="pd-badge-sale" v-if="displayStock <= 0">HẾT HÀNG</span>
            <img :src="mainImageUrl" :alt="product.ten_san_pham" class="pd-main-img-el" />
            <div class="pd-zoom-hint">🔍 Hình ảnh thực tế từ sản phẩm</div>
          </div>
          <div class="pd-thumbs" v-if="product.hinh_anhs && product.hinh_anhs.length > 1">
            <div
              v-for="(img, i) in product.hinh_anhs"
              :key="img.id"
              class="pd-thumb-el"
              :class="{ active: activeImg === i }"
              @click="activeImg = i"
            >
              <img :src="getProductImageUrl(img.duong_dan_anh)" class="pd-thumb-img" />
            </div>
          </div>
        </div>

        <!-- RIGHT: Info -->
        <div class="pd-info">
          <div class="pd-brand">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : 'SKYLINE MODELS' }}</div>
          <h1 class="pd-title">{{ product.ten_san_pham }}</h1>

          <!-- Rating -->
          <div class="pd-rating-row">
            <div class="pd-stars">
              <span v-for="s in 5" :key="s" class="star-on">★</span>
            </div>
            <span class="pd-rating-count">(4.9/5 dựa trên 32 đánh giá)</span>
            <span class="pd-sold">• Tình trạng: {{ displayStock > 0 ? 'Đang bán' : 'Hết hàng' }}</span>
          </div>

          <!-- Price -->
          <div class="pd-price-box">
            <span class="pd-price">{{ formatPrice(displayPrice) }}</span>
            <template v-if="product.gia_goc && product.gia_goc > 0">
              <span class="pd-price-orig">{{ formatPrice(product.gia_goc) }}</span>
              <span class="pd-price-badge" v-if="product.gia_goc > displayPrice">
                -{{ Math.round((1 - displayPrice / product.gia_goc) * 100) }}%
              </span>
            </template>
          </div>

          <!-- Description snippet -->
          <div class="pd-description-short">
            <p>{{ product.mo_ta }}</p>
          </div>

          <!-- Variant Selector (Color) -->
          <div class="pd-option-group" v-if="availableColors.length > 0">
            <div class="pd-option-label">Màu sắc: <strong>{{ selectedColor }}</strong></div>
            <div class="pd-colors">
              <button
                v-for="color in availableColors"
                :key="color"
                class="pd-color-btn"
                :class="{ active: selectedColor === color }"
                @click="selectColor(color)"
              >
                {{ color }}
              </button>
            </div>
          </div>

          <!-- Variant Selector (Size) -->
          <div class="pd-option-group" v-if="availableSizes.length > 0">
            <div class="pd-option-label">Kích thước / Loại: <strong>{{ selectedSize }}</strong></div>
            <div class="pd-sizes">
              <button
                v-for="size in availableSizes"
                :key="size"
                class="pd-size-btn"
                :class="{ active: selectedSize === size }"
                @click="selectedSize = size"
              >
                {{ size }}
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="pd-option-group">
            <div class="pd-option-label">Số lượng:</div>
            <div class="pd-qty-row">
              <div class="pd-qty">
                <button @click="qty > 1 && qty--" class="pd-qty-btn">−</button>
                <span class="pd-qty-val">{{ qty }}</span>
                <button @click="qty < displayStock && qty++" class="pd-qty-btn" :disabled="displayStock <= 0">+</button>
              </div>
              <span class="pd-stock" v-if="displayStock > 0">Còn {{ displayStock }} sản phẩm trong kho</span>
              <span class="pd-stock out-of-stock" v-else>Hết hàng trong kho</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="pd-actions">
            <button class="pd-btn-cart" :disabled="displayStock <= 0" @click="addToCart">
              🛒 Thêm vào giỏ hàng
            </button>
            <button class="pd-btn-buy" :disabled="displayStock <= 0" @click="buyNow">
              ⚡ Mua ngay
            </button>
            <button class="pd-btn-wish" :class="{ wished: isWished }" @click="isWished = !isWished" title="Yêu thích">
              {{ isWished ? '❤️' : '🤍' }}
            </button>
          </div>

          <!-- Shipping info -->
          <div class="pd-shipping">
            <div class="pd-ship-row"><span>🚚</span> Giao hàng toàn quốc — Đóng gói chống sốc</div>
            <div class="pd-ship-row"><span>↩️</span> Đổi trả hàng miễn phí trong vòng <strong>15 ngày</strong> nếu có lỗi</div>
            <div class="pd-ship-row"><span>🔒</span> Cam kết sản phẩm chính hãng 100%</div>
          </div>
        </div>
      </div>

      <!-- Tabs: Description / Specs / Reviews -->
      <div class="pd-container pd-tabs-section">
        <div class="pd-tabs">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            class="pd-tab"
            :class="{ active: activeTab === tab.key }"
            @click="activeTab = tab.key"
          >{{ tab.label }}</button>
        </div>

        <!-- Description -->
        <div v-if="activeTab === 'desc'" class="pd-tab-content">
          <p style="white-space: pre-line;">{{ product.mo_ta }}</p>
        </div>

        <!-- Specs table -->
        <div v-if="activeTab === 'specs'" class="pd-tab-content">
          <table class="pd-spec-table">
            <tbody>
              <tr>
                <td class="pd-st-label">Tên sản phẩm</td>
                <td class="pd-st-val">{{ product.ten_san_pham }}</td>
              </tr>
              <tr>
                <td class="pd-st-label">Danh mục</td>
                <td class="pd-st-val">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : 'Mô hình' }}</td>
              </tr>
              <tr v-if="currentVariant">
                <td class="pd-st-label">Màu sắc hiện tại</td>
                <td class="pd-st-val">{{ selectedColor }}</td>
              </tr>
              <tr v-if="currentVariant">
                <td class="pd-st-label">Kích thước/Loại hiện tại</td>
                <td class="pd-st-val">{{ selectedSize }}</td>
              </tr>
              <tr>
                <td class="pd-st-label">Tình trạng bán</td>
                <td class="pd-st-val">{{ product.tinh_trang === 1 ? 'Đang kinh doanh' : 'Hết hàng' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Reviews -->
        <div v-if="activeTab === 'reviews'" class="pd-tab-content">
          <div class="pd-review-summary">
            <div class="pd-rs-score">4.9<span>/5</span></div>
            <div>
              <div class="pd-stars lg">
                <span v-for="s in 5" :key="s" class="star-on">★</span>
              </div>
              <div class="pd-rs-label">3 đánh giá thực tế từ người mua</div>
            </div>
          </div>
          <div class="pd-review-list">
            <div v-for="rv in reviews" :key="rv.id" class="pd-review-card">
              <div class="pd-rv-header">
                <div class="pd-rv-avatar">{{ rv.name[0] }}</div>
                <div>
                  <div class="pd-rv-name">{{ rv.name }}</div>
                  <div class="pd-stars sm">
                    <span v-for="s in 5" :key="s" :class="s <= rv.rating ? 'star-on' : 'star-off'">★</span>
                  </div>
                </div>
                <div class="pd-rv-date">{{ rv.date }}</div>
              </div>
              <p class="pd-rv-body">{{ rv.body }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div class="pd-container pd-related" v-if="related.length > 0">
        <h2 class="pd-section-title">Sản phẩm tương tự</h2>
        <div class="pd-related-grid">
          <div v-for="rp in related" :key="rp.id" class="pd-related-card" @click="$router.push(`/product/${rp.id}`)">
            <div class="pd-rc-img-wrap">
              <img :src="getProductImageUrl(rp.hinh_anhs && rp.hinh_anhs.length > 0 ? rp.hinh_anhs[0].duong_dan_anh : '')" class="pd-rc-img-el" />
            </div>
            <div class="pd-rc-name">{{ rp.ten_san_pham }}</div>
            <div class="pd-rc-price">{{ formatPrice(rp.gia_co_ban) }}</div>
            <div class="pd-rc-stars">
              <span v-for="s in 5" :key="s" class="star-on">★</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../store/cartStore";

export default {
  name: "ProductDetail",
  data() {
    return {
      product: null,
      loading: true,
      activeImg: 0,
      selectedColor: "",
      selectedSize: "",
      qty: 1,
      isWished: false,
      activeTab: "desc",
      tabs: [
        { key: "desc", label: "Mô tả sản phẩm" },
        { key: "specs", label: "Thông số kỹ thuật" },
        { key: "reviews", label: "Đánh giá (3)" },
      ],
      reviews: [
        {
          id: 1,
          name: "Nguyễn Minh Khoa",
          rating: 5,
          date: "12/05/2026",
          body: "Sản phẩm cực kỳ chất lượng, chi tiết rất sắc nét, đóng gói cẩn thận. Giao hàng nhanh, sẽ ủng hộ tiếp!",
        },
        {
          id: 2,
          name: "Trần Thu Hà",
          rating: 4,
          date: "03/04/2026",
          body: "Mô hình đẹp hơn mong đợi, màu ngoài thực tế rất sáng và sắc nét. Nói chung rất ổn.",
        },
        {
          id: 3,
          name: "Lê Văn Bình",
          rating: 5,
          date: "20/03/2026",
          body: "Mua làm quà sinh nhật cho bạn, bạn mình khen mãi. Rất đáng tiền!",
        },
      ],
      related: [],
    };
  },
  mounted() {
    this.loadProduct();
  },
  watch: {
    "$route.params.id": function () {
      this.loadProduct();
    },
  },
  computed: {
    availableColors() {
      if (!this.product || !this.product.bien_thes) return [];
      const colors = this.product.bien_thes
        .map((v) => v.thuoc_tinh && v.thuoc_tinh.color)
        .filter(Boolean);
      return [...new Set(colors)];
    },
    availableSizes() {
      if (!this.product || !this.product.bien_thes) return [];
      const sizes = this.product.bien_thes
        .filter((v) => !this.selectedColor || (v.thuoc_tinh && v.thuoc_tinh.color === this.selectedColor))
        .map((v) => v.thuoc_tinh && v.thuoc_tinh.size)
        .filter(Boolean);
      return [...new Set(sizes)];
    },
    currentVariant() {
      if (!this.product || !this.product.bien_thes || this.product.bien_thes.length === 0) return null;
      // Tìm biến thể khớp với cả màu và size đã chọn
      const found = this.product.bien_thes.find(
        (v) =>
          v.thuoc_tinh &&
          v.thuoc_tinh.color === this.selectedColor &&
          v.thuoc_tinh.size === this.selectedSize
      );
      if (found) return found;

      // Nếu không khớp hoàn toàn, thử tìm cái khớp màu trước
      const colorMatch = this.product.bien_thes.find(
        (v) => v.thuoc_tinh && v.thuoc_tinh.color === this.selectedColor
      );
      if (colorMatch) return colorMatch;

      return this.product.bien_thes[0];
    },
    displayPrice() {
      if (this.currentVariant) {
        return this.currentVariant.gia_ban;
      }
      return this.product ? this.product.gia_co_ban : 0;
    },
    displayStock() {
      if (this.currentVariant) {
        return this.currentVariant.so_luong_ton_kho;
      }
      return this.product ? 0 : 0;
    },
    mainImageUrl() {
      if (this.product && this.product.hinh_anhs && this.product.hinh_anhs.length > 0) {
        const imgObj = this.product.hinh_anhs[this.activeImg] || this.product.hinh_anhs[0];
        return this.getProductImageUrl(imgObj.duong_dan_anh);
      }
      return "https://via.placeholder.com/500?text=No+Image";
    },
  },
  methods: {
    async loadProduct() {
      this.loading = true;
      this.qty = 1;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      const id = this.$route.params.id;
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/san-pham/${id}`);
        const prod = response.data;
        this.product = prod;

        // Cài đặt mặc định cho các biến thể
        if (prod.bien_thes && prod.bien_thes.length > 0) {
          const firstVariant = prod.bien_thes[0];
          this.selectedColor = (firstVariant.thuoc_tinh && firstVariant.thuoc_tinh.color) || "";
          this.selectedSize = (firstVariant.thuoc_tinh && firstVariant.thuoc_tinh.size) || "";
        } else {
          this.selectedColor = "";
          this.selectedSize = "";
        }

        this.activeImg = 0;

        // Tải sản phẩm tương tự
        this.loadRelatedProducts(prod.id_danh_muc, prod.id);
      } catch (error) {
        console.error("Lỗi khi tải chi tiết sản phẩm:", error);
      } finally {
        this.loading = false;
      }
    },
    async loadRelatedProducts(categoryId, currentProductId) {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/san-pham", {
          params: { id_danh_muc: categoryId },
        });
        const list = response.data.data || [];
        this.related = list.filter((p) => p.id !== currentProductId).slice(0, 4);
      } catch (error) {
        console.error("Lỗi khi tải sản phẩm tương tự:", error);
      }
    },
    selectColor(color) {
      this.selectedColor = color;
      // Tìm các kích cỡ có sẵn cho màu sắc mới này
      if (this.product && this.product.bien_thes) {
        const sizes = this.product.bien_thes
          .filter((v) => v.thuoc_tinh && v.thuoc_tinh.color === color)
          .map((v) => v.thuoc_tinh && v.thuoc_tinh.size)
          .filter(Boolean);
        if (sizes.length > 0) {
          this.selectedSize = sizes[0];
        }
      }
    },
    getProductImageUrl(imagePath) {
      if (!imagePath) return "https://via.placeholder.com/500?text=No+Image";
      if (imagePath.startsWith("http")) {
        return imagePath;
      }
      return "http://127.0.0.1:8000" + (imagePath.startsWith("/") ? "" : "/") + imagePath;
    },
    formatPrice(val) {
      if (!val) return "0 đ";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(val);
    },
    async addToCart() {
      if (!this.product || !this.currentVariant) return;

      const cartStore = useCartStore();
      const result = await cartStore.addItem(this.product, this.currentVariant, this.qty);
      
      if (result.success) {
        if (this.$toast) {
          this.$toast.success(`Đã thêm ${this.qty} sản phẩm vào giỏ hàng thành công!`);
        } else {
          alert(`Đã thêm ${this.qty} sản phẩm vào giỏ hàng thành công!`);
        }
      } else {
        if (this.$toast) {
          this.$toast.error(result.message || "Không thể thêm vào giỏ hàng.");
        } else {
          alert(result.message || "Không thể thêm vào giỏ hàng.");
        }
      }
    },
    async buyNow() {
      if (!this.product || !this.currentVariant) return;
      if (this.qty < 1 || this.qty > this.currentVariant.so_luong_ton_kho) {
        if (this.$toast) {
          this.$toast.error("Số lượng mua không hợp lệ.");
        } else {
          alert("Số lượng mua không hợp lệ.");
        }
        return;
      }

      const image =
        this.product.hinh_anhs && this.product.hinh_anhs.length > 0
          ? this.product.hinh_anhs[0].duong_dan_anh
          : "";

      let attributesStr = "";
      if (this.currentVariant.thuoc_tinh) {
        const attrs = [];
        for (const [key, value] of Object.entries(this.currentVariant.thuoc_tinh)) {
          attrs.push(`${key}: ${value}`);
        }
        attributesStr = attrs.join(" - ");
      }

      const buyNowItem = {
        id_bien_the: this.currentVariant.id,
        id_san_pham: this.product.id,
        ten_san_pham: this.product.ten_san_pham,
        ten_bien_the: attributesStr,
        gia_ban: parseFloat(this.currentVariant.gia_ban),
        so_luong_ton_kho: this.currentVariant.so_luong_ton_kho,
        hinh_anh: image,
        so_luong: this.qty,
        isSelected: true,
      };

      sessionStorage.setItem("buy_now_checkout_item", JSON.stringify(buyNowItem));
      this.$router.push("/thanh-toan");
    },
  },
};
</script>

<style scoped>
/* ── Base ── */
.pd-page {
  background: #f8fafc;
  min-height: 100vh;
  padding-bottom: 80px;
}
.pd-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ── Breadcrumb ── */
.pd-breadcrumb {
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  padding: 12px 0;
  font-size: 13px;
}
.bc-link {
  color: #e11d48;
  text-decoration: none;
  font-weight: 500;
}
.bc-link:hover {
  text-decoration: underline;
}
.bc-sep {
  color: #94a3b8;
  margin: 0 8px;
}
.bc-current {
  color: #475569;
}

/* ── Main grid ── */
.pd-main {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  padding-top: 36px;
  padding-bottom: 48px;
}

/* ── Gallery ── */
.pd-main-img-wrap {
  position: relative;
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}
.pd-main-img-el {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.pd-badge-sale {
  position: absolute;
  top: 16px;
  left: 16px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 12px;
  color: #fff;
  background: #ef4444;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}
.pd-zoom-hint {
  position: absolute;
  bottom: 12px;
  right: 14px;
  font-size: 11px;
  color: #94a3b8;
}
.pd-thumbs {
  display: flex;
  gap: 10px;
  margin-top: 14px;
  flex-wrap: wrap;
}
.pd-thumb-el {
  width: 68px;
  height: 68px;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
}
.pd-thumb-el.active,
.pd-thumb-el:hover {
  border-color: #e11d48;
  box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
}
.pd-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* ── Info panel ── */
.pd-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.pd-brand {
  font-size: 12px;
  font-weight: 700;
  color: #e11d48;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.pd-title {
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.3;
  margin: 0;
}

/* Rating */
.pd-rating-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.pd-stars {
  display: flex;
  gap: 2px;
}
.star-on {
  color: #f59e0b;
}
.star-off {
  color: #d1d5db;
}
.pd-stars.lg span {
  font-size: 22px;
}
.pd-stars.sm span {
  font-size: 13px;
}
.pd-rating-count {
  font-size: 13px;
  color: #e11d48;
  cursor: pointer;
}
.pd-sold {
  font-size: 13px;
  color: #64748b;
}

/* Price */
.pd-price-box {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.pd-price {
  font-size: 32px;
  font-weight: 800;
  color: #e11d48;
}
.pd-price-orig {
  font-size: 18px;
  font-weight: 500;
  color: #94a3b8;
  text-decoration: line-through;
}
.pd-price-badge {
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, #e11d48, #be123c);
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 100px;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
  animation: pulseBadge 2s infinite;
}
@keyframes pulseBadge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.pd-description-short {
  font-size: 14.5px;
  line-height: 1.6;
  color: #475569;
}

/* Option groups */
.pd-option-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.pd-option-label {
  font-size: 14px;
  color: #475569;
}
.pd-colors,
.pd-sizes {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.pd-color-btn,
.pd-size-btn {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  color: #475569;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  transition: all 0.2s;
}
.pd-color-btn:hover,
.pd-size-btn:hover {
  border-color: #e11d48;
  color: #e11d48;
}
.pd-color-btn.active,
.pd-size-btn.active {
  border-color: #e11d48;
  background-color: #fff1f2;
  color: #e11d48;
  box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
}

.pd-qty-row {
  display: flex;
  align-items: center;
  gap: 16px;
}
.pd-qty {
  display: flex;
  align-items: center;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}
.pd-qty-btn {
  background: #f1f5f9;
  border: none;
  width: 38px;
  height: 38px;
  font-size: 18px;
  cursor: pointer;
  transition: background 0.15s;
}
.pd-qty-btn:hover {
  background: #e2e8f0;
}
.pd-qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.pd-qty-val {
  width: 44px;
  text-align: center;
  font-weight: 700;
  font-size: 15px;
  color: #0f172a;
}
.pd-stock {
  font-size: 13.5px;
  color: #10b981;
  font-weight: 600;
}
.pd-stock.out-of-stock {
  color: #ef4444;
}

/* Actions */
.pd-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.pd-btn-cart,
.pd-btn-buy {
  flex: 1;
  min-width: 140px;
  border: none;
  border-radius: 14px;
  padding: 15px 0;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s;
}
.pd-btn-cart {
  background: #fff;
  color: #e11d48;
  border: 2px solid #e11d48;
}
.pd-btn-cart:hover:not(:disabled) {
  background: #fff1f2;
}
.pd-btn-buy {
  background: linear-gradient(135deg, #e11d48, #be123c);
  color: #fff;
  box-shadow: 0 8px 24px rgba(225, 29, 72, 0.3);
}
.pd-btn-buy:hover:not(:disabled) {
  box-shadow: 0 12px 32px rgba(225, 29, 72, 0.4);
  transform: translateY(-1px);
}
.pd-btn-cart:disabled,
.pd-btn-buy:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #cbd5e1;
  color: #64748b;
  border-color: #cbd5e1;
  box-shadow: none !important;
  transform: none !important;
}

.pd-btn-wish {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  border: 2px solid #e2e8f0;
  background: #fff;
  font-size: 22px;
  cursor: pointer;
  transition: all 0.2s;
}
.pd-btn-wish.wished {
  border-color: #e11d48;
  background: #fff1f2;
}
.pd-btn-wish:hover {
  border-color: #e11d48;
}

/* Shipping */
.pd-shipping {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 14px;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pd-ship-row {
  font-size: 13.5px;
  color: #166534;
  display: flex;
  gap: 8px;
}

/* ── Tabs ── */
.pd-tabs-section {
  border-top: 1px solid #e2e8f0;
  padding-top: 40px;
}
.pd-tabs {
  display: flex;
  gap: 4px;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 20px;
}
.pd-tab {
  background: none;
  border: none;
  padding: 12px 22px;
  font-size: 14.5px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s;
}
.pd-tab.active {
  color: #e11d48;
  border-bottom-color: #e11d48;
}
.pd-tab:hover {
  color: #e11d48;
}
.pd-tab-content {
  max-width: 820px;
  color: #334155;
  line-height: 1.8;
  font-size: 15px;
}

/* Spec table */
.pd-spec-table {
  width: 100%;
  border-collapse: collapse;
}
.pd-spec-table tr {
  border-bottom: 1px solid #f1f5f9;
}
.pd-spec-table tr:nth-child(odd) {
  background: #f8fafc;
}
.pd-st-label {
  padding: 12px 16px;
  font-size: 13.5px;
  color: #64748b;
  font-weight: 600;
  width: 220px;
}
.pd-st-val {
  padding: 12px 16px;
  font-size: 14px;
  color: #0f172a;
}

/* Review summary */
.pd-review-summary {
  display: flex;
  align-items: center;
  gap: 24px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px 28px;
  margin-bottom: 20px;
}
.pd-rs-score {
  font-size: 56px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
}
.pd-rs-score span {
  font-size: 22px;
  color: #94a3b8;
}
.pd-rs-label {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}
.pd-review-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.pd-review-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px 22px;
}
.pd-rv-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 8px;
}
.pd-rv-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e11d48, #8b5cf6);
  color: #fff;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}
.pd-rv-name {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}
.pd-rv-date {
  margin-left: auto;
  font-size: 12px;
  color: #94a3b8;
}
.pd-rv-body {
  font-size: 14px;
  color: #475569;
  line-height: 1.6;
  margin: 0;
}

/* ── Related ── */
.pd-related {
  padding-top: 48px;
}
.pd-section-title {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 20px;
}
.pd-related-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.pd-related-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 22px;
  cursor: pointer;
  transition: all 0.25s;
  text-align: center;
}
.pd-related-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
  border-color: #e11d48;
}
.pd-rc-img-wrap {
  width: 100%;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  margin-bottom: 8px;
  background: #f8fafc;
  border-radius: 12px;
}
.pd-rc-img-el {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.3s;
}
.pd-related-card:hover .pd-rc-img-el {
  transform: scale(1.05);
}
.pd-rc-name {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 6px;
  line-height: 1.4;
  height: 36px;
  overflow: hidden;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.pd-rc-price {
  font-size: 16px;
  font-weight: 800;
  color: #e11d48;
  margin-bottom: 6px;
}
.pd-rc-stars {
  font-size: 13px;
}

/* Loading & Not Found */
.pd-loading,
.pd-not-found {
  text-align: center;
  padding: 80px 20px;
  font-size: 18px;
  color: #64748b;
  font-weight: 500;
}
.pd-loading i {
  margin-right: 8px;
  font-size: 24px;
  color: #e11d48;
}
.not-found-card {
  max-width: 500px;
  margin: 0 auto;
  background: #fff;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}
.not-found-card h2 {
  color: #0f172a;
  margin-top: 0;
  margin-bottom: 10px;
}
.not-found-card p {
  font-size: 14.5px;
  color: #64748b;
  margin-bottom: 20px;
}
.btn-back-home {
  display: inline-block;
  padding: 12px 28px;
  background: #e11d48;
  color: #fff;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  font-size: 14.5px;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
  transition: all 0.2s;
}
.btn-back-home:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(225, 29, 72, 0.35);
}

/* ── Responsive ── */
@media (max-width: 900px) {
  .pd-main {
    grid-template-columns: 1fr;
    gap: 28px;
  }
  .pd-related-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 600px) {
  .pd-st-label {
    width: 110px;
    padding: 10px 8px;
    font-size: 12.5px;
  }
  .pd-st-val {
    padding: 10px 8px;
    font-size: 13px;
  }
  .pd-review-summary {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
    gap: 16px;
    padding: 16px;
  }
  .pd-rs-score {
    font-size: 40px;
  }
}
@media (max-width: 520px) {
  .pd-title {
    font-size: 20px;
  }
  .pd-price {
    font-size: 24px;
  }
  .pd-related-grid {
    grid-template-columns: 1fr;
  }
}
</style>


