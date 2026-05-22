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
          
          <div class="pd-thumbs" v-if="allImages && allImages.length > 1">
            <!-- Grid layout when <= 4 images -->
            <div v-if="allImages.length <= 4" class="pd-thumbs-grid">
              <div
                v-for="(img, i) in allImages"
                :key="img.id"
                class="pd-thumb-el"
                :class="{ active: isThumbActive(img, i) }"
                @click="selectThumbnail(i)"
              >
                <img :src="getProductImageUrl(img.duong_dan_anh)" class="pd-thumb-img" />
              </div>
            </div>

            <!-- Slider layout when > 4 images -->
            <div v-else class="pd-thumbs-slider-wrapper">
              <button 
                type="button"
                class="pd-thumbs-btn pd-thumbs-btn-prev" 
                :class="{ hidden: !canScrollPrev }"
                @click="scrollThumbs('prev')"
              >
                ‹
              </button>
              
              <div 
                ref="thumbViewport" 
                class="pd-thumbs-viewport"
                @scroll="checkScroll"
              >
                <div class="pd-thumbs-track">
                  <div
                    v-for="(img, i) in allImages"
                    :key="img.id"
                    ref="thumbItems"
                    class="pd-thumb-el"
                    :class="{ active: isThumbActive(img, i) }"
                    @click="selectThumbnail(i)"
                  >
                    <img :src="getProductImageUrl(img.duong_dan_anh)" class="pd-thumb-img" />
                  </div>
                </div>
              </div>
              
              <button 
                type="button"
                class="pd-thumbs-btn pd-thumbs-btn-next" 
                :class="{ hidden: !canScrollNext }"
                @click="scrollThumbs('next')"
              >
                ›
              </button>
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
            <template v-if="displayPriceOrig && displayPriceOrig > displayPrice">
              <span class="pd-price-orig">{{ formatPrice(displayPriceOrig) }}</span>
              <span class="pd-price-badge">
                -{{ Math.round((1 - displayPrice / displayPriceOrig) * 100) }}%
              </span>
            </template>
          </div>

          <!-- Description snippet -->
          <div class="pd-description-short">
            <p>{{ product.mo_ta }}</p>
          </div>

          <!-- Dynamic Variant Selectors (tự động theo thuộc tính trong DB) -->
          <div class="pd-option-group" v-for="group in attributeGroups" :key="group.key">
            <div class="pd-option-label">{{ group.label }}: <strong>{{ selectedAttributes[group.key] }}</strong></div>
            <div class="pd-colors">
              <button
                v-for="value in group.values"
                :key="value"
                class="pd-color-btn"
                :class="{ active: selectedAttributes[group.key] === value }"
                @click="selectAttribute(group.key, value)"
              >
                {{ value }}
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
            <button class="pd-btn-wish" :class="{ wished: isWished }" @click="toggleWish" title="Yêu thích">
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
            v-for="tab in renderedTabs"
            :key="tab.key"
            class="pd-tab"
            :class="{ active: activeTab === tab.key }"
            @click="activeTab = tab.key"
          >{{ tab.label }}</button>
        </div>

        <!-- Description -->
        <div v-if="activeTab === 'desc'" class="pd-tab-content">
          <div v-safe-html="product.mo_ta" style="white-space: pre-line;"></div>
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
          <div v-if="reviewsLoading" class="pd-reviews-loading" style="text-align: center; padding: 40px 0; color: #64748b;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 8px;"></i>
            <p>Đang tải đánh giá...</p>
          </div>
          
          <div v-else>
            <!-- Review Summary Dashboard -->
            <div class="pd-review-summary-container" style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
              <!-- Score breakdown -->
              <div class="pd-review-summary-left" style="display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid #f1f5f9; padding-right: 24px;">
                <div class="pd-rs-score" style="font-size: 54px; font-weight: 800; color: #0f172a;">
                  {{ reviewSummary?.average || 0 }}<span style="font-size: 20px; color: #94a3b8;">/5</span>
                </div>
                <div class="pd-stars lg" style="color: #eab308; font-size: 20px; margin: 8px 0;">
                  <span v-for="s in 5" :key="s" :style="{ color: s <= Math.round(reviewSummary?.average || 0) ? '#eab308' : '#cbd5e1' }">★</span>
                </div>
                <div class="pd-rs-label" style="font-size: 13px; color: #64748b; font-weight: 500;">
                  {{ reviewSummary?.total || 0 }} đánh giá thực tế
                </div>
              </div>
              
              <!-- Percentages breakdown -->
              <div class="pd-review-summary-right" style="display: flex; flex-direction: column; gap: 8px; justify-content: center;">
                <div v-for="star in [5, 4, 3, 2, 1]" :key="star" style="display: flex; align-items: center; gap: 12px; font-size: 13px;">
                  <span style="width: 40px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 2px;">{{ star }} ★</span>
                  <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                    <div :style="{ width: getStarPercentage(star) + '%', background: '#eab308', height: '100%', borderRadius: '4px' }"></div>
                  </div>
                  <span style="width: 45px; text-align: right; color: #64748b; font-weight: 500;">{{ getStarCount(star) }} ({{ getStarPercentage(star) }}%)</span>
                </div>
              </div>
            </div>

            <!-- Filter Chips -->
            <div class="pd-review-filters" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
              <button 
                type="button"
                class="pd-filter-chip" 
                :class="{ active: activeReviewFilter === 'all' }"
                @click="applyReviewFilter('all')"
              >
                Tất cả
              </button>
              <button 
                v-for="star in [5, 4, 3, 2, 1]" 
                :key="star"
                type="button"
                class="pd-filter-chip"
                :class="{ active: activeReviewFilter === String(star) }"
                @click="applyReviewFilter(String(star))"
              >
                {{ star }} Sao ({{ reviewSummary?.stars[star] || 0 }})
              </button>
              <button 
                type="button"
                class="pd-filter-chip"
                :class="{ active: activeReviewFilter === 'images' }"
                @click="applyReviewFilter('images')"
              >
                Có hình ảnh ({{ reviewSummary?.count_with_images || 0 }})
              </button>
              <button 
                type="button"
                class="pd-filter-chip"
                :class="{ active: activeReviewFilter === 'comments' }"
                @click="applyReviewFilter('comments')"
              >
                Có bình luận ({{ reviewSummary?.count_with_comments || 0 }})
              </button>
            </div>

            <!-- Reviews List -->
            <div v-if="reviews.length === 0" style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 12px; border: 1px dashed #e2e8f0; color: #64748b;">
              Không tìm thấy đánh giá nào phù hợp với bộ lọc.
            </div>
            
            <div v-else class="pd-review-list">
              <div v-for="rv in reviews" :key="rv.id" class="pd-review-card">
                <div class="pd-rv-header">
                  <div class="pd-rv-avatar" :style="{ background: getRandomAvatarBg(rv.name) }">{{ rv.name[0] }}</div>
                  <div>
                    <div class="pd-rv-name" style="font-weight: 700; color: #0f172a;">{{ rv.name }}</div>
                    <div class="pd-stars sm" style="color: #eab308; font-size: 13px; margin-top: 2px;">
                      <span v-for="s in 5" :key="s" :style="{ color: s <= rv.rating ? '#eab308' : '#cbd5e1' }">★</span>
                    </div>
                  </div>
                  <div class="pd-rv-date">{{ rv.date }}</div>
                </div>
                
                <!-- Variant bought info -->
                <div style="font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 500;">
                  <i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 4px;"></i> Phân loại hàng: <span style="color: #475569;">{{ rv.variant }}</span>
                </div>
                
                <!-- Content text -->
                <p class="pd-rv-body" v-safe-html="rv.body || 'Khách hàng không để lại bình luận.'" style="white-space: pre-line; margin-bottom: 12px;"></p>
                
                <!-- Images Grid -->
                <div v-if="rv.images && rv.images.length > 0" class="pd-rv-images-grid" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                  <div 
                    v-for="(img, idx) in rv.images" 
                    :key="idx" 
                    class="pd-rv-image-item" 
                    style="width: 72px; height: 72px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; cursor: pointer;"
                    @click="openLightbox(img)"
                  >
                    <img :src="img" style="width: 100%; height: 100%; object-fit: cover;" />
                  </div>
                </div>

                <!-- Admin Response -->
                <div v-if="rv.reply" class="pd-rv-admin-reply" style="background: rgba(219, 39, 119, 0.03); border-left: 3px solid #db2777; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 12px;">
                  <div style="font-weight: 700; font-size: 13px; color: #db2777; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-store"></i> Phản hồi của Cửa hàng
                  </div>
                  <p style="font-size: 13.5px; color: #475569; margin: 0; line-height: 1.5;" v-safe-html="rv.reply"></p>
                </div>
              </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="reviewsPagination.last_page > 1" class="pagination-container" style="display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 24px;">
              <button 
                type="button"
                class="btn-page" 
                :disabled="reviewsPagination.current_page === 1"
                @click="fetchReviews(reviewsPagination.current_page - 1)"
                style="padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
              >
                ‹ Trước
              </button>
              <span class="page-info" style="font-size: 13px; color: #64748b; font-weight: 500;">Trang {{ reviewsPagination.current_page }} / {{ reviewsPagination.last_page }}</span>
              <button 
                type="button"
                class="btn-page" 
                :disabled="reviewsPagination.current_page === reviewsPagination.last_page"
                @click="fetchReviews(reviewsPagination.current_page + 1)"
                style="padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
              >
                Sau ›
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div class="pd-container pd-related" v-if="related.length > 0">
        <h2 class="pd-section-title">Sản phẩm tương tự</h2>
        <div class="pd-related-grid">
          <div v-for="rp in related" :key="rp.id" class="pd-related-card" @click="goToProductDetail(rp)">
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
    
    <!-- LIGHTBOX MODAL FOR REVIEW IMAGES -->
    <div v-if="activeLightboxImg" class="lightbox-overlay" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.85); display: flex; align-items: center; justify-content: center; z-index: 99999;" @click="activeLightboxImg = null">
      <button type="button" class="btn-close-lightbox" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #fff; font-size: 32px; cursor: pointer; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">✕</button>
      <img :src="activeLightboxImg" style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);" @click.stop />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../store/cartStore";
import { useWishlistStore } from "../../store/wishlistStore";
import { slugify, getProductDetailUrl } from "../../router/utils";

export default {
  name: "ProductDetail",
  data() {
    return {
      product: null,
      loading: true,
      activeImg: 0,
      showVariantImage: true,
      selectedAttributes: {}, // { mau: 'Set Doreamon', kich_thuoc: 'S', ... } - tự động theo thuộc tính
      qty: 1,
      isWished: false,
      activeTab: "desc",
      reviews: [],
      reviewSummary: null,
      reviewsLoading: false,
      reviewsPagination: { current_page: 1, last_page: 1 },
      reviewFilters: { sao: null, co_anh: 0, co_binh_luan: 0 },
      activeReviewFilter: 'all',
      activeLightboxImg: null,
      related: [],
      canScrollPrev: false,
      canScrollNext: false,
    };
  },
  mounted() {
    this.loadProduct();
  },
  watch: {
    "$route.params.id": function () {
      this.loadProduct();
    },
    "$route.query.variant_id": function (newVal) {
      this.syncVariantFromUrl(newVal);
    },
    allImages() {
      this.$nextTick(() => {
        this.checkScroll();
      });
    },
    activeThumbIndex(newVal) {
      this.scrollActiveThumbIntoView(newVal);
    },
    currentVariant(newVariant) {
      this.updateUrlQuery(newVariant);
    },
  },
  computed: {
    renderedTabs() {
      const count = this.reviewSummary?.total ?? 0;
      return [
        { key: "desc", label: "Mô tả sản phẩm" },
        { key: "specs", label: "Thông số kỹ thuật" },
        { key: "reviews", label: `Đánh giá (${count})` },
      ];
    },
    // Trả về tất cả nhóm thuộc tính từ biến thể (hoàn toàn động, không hardcode tên key)
    attributeGroups() {
      if (!this.product || !this.product.bien_thes || this.product.bien_thes.length === 0) return [];
      const groupsMap = new Map();
      this.product.bien_thes.forEach(v => {
        if (!v.thuoc_tinh) return;
        const tt = typeof v.thuoc_tinh === 'string' ? JSON.parse(v.thuoc_tinh) : v.thuoc_tinh;
        if (!tt) return;
        Object.entries(tt).forEach(([key, val]) => {
          if (!groupsMap.has(key)) groupsMap.set(key, { values: [], label: this.prettifyKey(key) });
          const group = groupsMap.get(key);
          if (!group.values.includes(val)) group.values.push(val);
        });
      });
      return Array.from(groupsMap.entries()).map(([key, { values, label }]) => ({ key, label, values }));
    },
    currentVariant() {
      if (!this.product || !this.product.bien_thes || this.product.bien_thes.length === 0) return null;
      // Nếu không có thuộc tính nào được chọn, trả biến thể đầu tiên
      if (Object.keys(this.selectedAttributes).length === 0) return this.product.bien_thes[0];
      // Tìm biến thể khớp tất cả thuộc tính đã chọn
      const found = this.product.bien_thes.find(v => {
        if (!v.thuoc_tinh) return false;
        const tt = typeof v.thuoc_tinh === 'string' ? JSON.parse(v.thuoc_tinh) : v.thuoc_tinh;
        if (!tt) return false;
        return Object.entries(this.selectedAttributes).every(([key, val]) => tt[key] === val);
      });
      // Fallback: tìm biến thể khớp một phần
      if (found) return found;
      const firstKey = Object.keys(this.selectedAttributes)[0];
      const firstVal = this.selectedAttributes[firstKey];
      const partial = this.product.bien_thes.find(v => {
        if (!v.thuoc_tinh) return false;
        const tt = typeof v.thuoc_tinh === 'string' ? JSON.parse(v.thuoc_tinh) : v.thuoc_tinh;
        return tt && tt[firstKey] === firstVal;
      });
      return partial || this.product.bien_thes[0];
    },
    displayPrice() {
      if (this.currentVariant) {
        return this.currentVariant.gia_ban;
      }
      return this.product ? this.product.gia_co_ban : 0;
    },
    displayPriceOrig() {
      // Ưu tiên gia_goc của biến thể đang chọn, fallback về gia_goc sản phẩm gốc
      if (this.currentVariant && this.currentVariant.gia_goc && this.currentVariant.gia_goc > 0) {
        return this.currentVariant.gia_goc;
      }
      return this.product && this.product.gia_goc ? this.product.gia_goc : null;
    },
    displayStock() {
      if (this.currentVariant) {
        return this.currentVariant.so_luong_ton_kho;
      }
      return this.product ? 0 : 0;
    },
    allImages() {
      if (!this.product) return [];
      const list = [];
      
      // 1. Thêm ảnh từ album chung của sản phẩm
      if (this.product.hinh_anhs && this.product.hinh_anhs.length > 0) {
        this.product.hinh_anhs.forEach(img => {
          list.push({
            id: `general-${img.id}`,
            duong_dan_anh: img.duong_dan_anh,
            isVariant: false
          });
        });
      }
      
      // 2. Thêm ảnh từ các biến thể (nếu có ảnh và chưa trùng đường dẫn)
      if (this.product.bien_thes && this.product.bien_thes.length > 0) {
        this.product.bien_thes.forEach(vt => {
          if (vt.hinh_anh) {
            const exists = list.some(item => item.duong_dan_anh === vt.hinh_anh);
            if (!exists) {
              list.push({
                id: `variant-${vt.id}`,
                duong_dan_anh: vt.hinh_anh,
                isVariant: true,
                variant: vt
              });
            }
          }
        });
      }
      
      return list;
    },
    mainImageUrl() {
      if (this.showVariantImage && this.currentVariant && this.currentVariant.hinh_anh) {
        return this.getProductImageUrl(this.currentVariant.hinh_anh);
      }
      // Fallback về danh sách ảnh chung + biến thể
      if (this.allImages && this.allImages.length > 0) {
        const imgObj = this.allImages[this.activeImg] || this.allImages[0];
        return this.getProductImageUrl(imgObj.duong_dan_anh);
      }
      return "https://via.placeholder.com/500?text=No+Image";
    },
    activeThumbIndex() {
      if (this.showVariantImage && this.currentVariant && this.currentVariant.hinh_anh) {
        const idx = this.allImages.findIndex(img => img.duong_dan_anh === this.currentVariant.hinh_anh);
        if (idx !== -1) return idx;
      }
      return this.activeImg;
    },
  },
  methods: {
    async loadProduct() {
      this.loading = true;
      this.qty = 1;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      const id = this.$route.params.id;
      try {
        const response = await axios.get(`/api/san-pham/${id}`);
        const prod = response.data;
        this.product = prod;

        // Redirect to canonical SEO URL path if it does not match
        const correctSlug = slugify(prod.ten_san_pham || "san-pham");
        const correctPath = `/${correctSlug}-i.${prod.id}`;
        if (this.$route.path !== correctPath) {
          this.$router.replace({
            path: correctPath,
            query: this.$route.query
          }).catch(err => {
            if (err.name !== 'NavigationDuplicated') console.error(err);
          });
        }

        // Restore state from URL query parameter ?variant_id=...
        const urlVariantId = this.$route.query.variant_id;
        let matchedVariant = null;
        if (urlVariantId && prod.bien_thes && prod.bien_thes.length > 0) {
          matchedVariant = prod.bien_thes.find(v => String(v.id) === String(urlVariantId));
        }

        if (matchedVariant) {
          const tt = typeof matchedVariant.thuoc_tinh === 'string' ? JSON.parse(matchedVariant.thuoc_tinh) : matchedVariant.thuoc_tinh;
          this.selectedAttributes = tt ? { ...tt } : {};
        } else {
          // If URL param is invalid or empty, default to first variant
          if (prod.bien_thes && prod.bien_thes.length > 0) {
            const firstTt = prod.bien_thes[0].thuoc_tinh;
            const tt = typeof firstTt === 'string' ? JSON.parse(firstTt) : firstTt;
            this.selectedAttributes = tt ? { ...tt } : {};
            
            // Lặng lẽ update URL to default variant ID
            this.$nextTick(() => {
              this.updateUrlQuery(prod.bien_thes[0]);
            });
          } else {
            this.selectedAttributes = {};
            this.$nextTick(() => {
              this.updateUrlQuery(null);
            });
          }
        }

        this.activeImg = 0;
        this.showVariantImage = true;

        // Tải sản phẩm tương tự
        this.loadRelatedProducts(prod.id_danh_muc, prod.id);

        // Tải đánh giá sản phẩm
        this.resetReviewFilters();
        this.fetchReviews(1);

        // Khởi tạo trạng thái yêu thích & Thêm vào đã xem gần đây
        const wishlistStore = useWishlistStore();
        this.isWished = wishlistStore.isWished(prod.id);
        wishlistStore.addRecentlyViewed(prod);
      } catch (error) {
        console.error("Lỗi khi tải chi tiết sản phẩm:", error);
      } finally {
        this.loading = false;
        this.$nextTick(() => {
          this.checkScroll();
          this.scrollActiveThumbIntoView(this.activeThumbIndex);
        });
      }
    },
    async toggleWish() {
      if (!this.product) return;
      const wishlistStore = useWishlistStore();
      const res = await wishlistStore.toggleWishlist(this.product);
      this.isWished = res;
      if (this.$toast) {
        if (res) {
          this.$toast.success("Đã thêm vào danh sách yêu thích!");
        } else {
          this.$toast.info("Đã xóa khỏi danh sách yêu thích.");
        }
      }
    },
    updateUrlQuery(variant) {
      if (variant && variant.id) {
        if (this.$route.query.variant_id !== String(variant.id)) {
          this.$router.replace({
            path: this.$route.path,
            query: { ...this.$route.query, variant_id: variant.id }
          }).catch(err => {
            if (err.name !== 'NavigationDuplicated') console.error(err);
          });
        }
      } else {
        if (this.$route.query.variant_id) {
          const newQuery = { ...this.$route.query };
          delete newQuery.variant_id;
          this.$router.replace({ path: this.$route.path, query: newQuery }).catch(() => {});
        }
      }
    },
    syncVariantFromUrl(urlVariantId) {
      if (!this.product || !this.product.bien_thes || this.product.bien_thes.length === 0) return;
      
      let matchedVariant = null;
      if (urlVariantId) {
        matchedVariant = this.product.bien_thes.find(v => String(v.id) === String(urlVariantId));
      }

      if (matchedVariant) {
        const tt = typeof matchedVariant.thuoc_tinh === 'string' ? JSON.parse(matchedVariant.thuoc_tinh) : matchedVariant.thuoc_tinh;
        if (JSON.stringify(this.selectedAttributes) !== JSON.stringify(tt)) {
          this.selectedAttributes = tt ? { ...tt } : {};
        }
      } else {
        // Fallback to first variant if URL parameter is invalid
        const firstVariant = this.product.bien_thes[0];
        const tt = typeof firstVariant.thuoc_tinh === 'string' ? JSON.parse(firstVariant.thuoc_tinh) : firstVariant.thuoc_tinh;
        if (JSON.stringify(this.selectedAttributes) !== JSON.stringify(tt)) {
          this.selectedAttributes = tt ? { ...tt } : {};
        }
        this.updateUrlQuery(firstVariant);
      }
    },
    checkScroll() {
      const el = this.$refs.thumbViewport;
      if (el) {
        this.canScrollPrev = el.scrollLeft > 5;
        this.canScrollNext = el.scrollLeft + el.clientWidth < el.scrollWidth - 5;
      }
    },
    scrollThumbs(direction) {
      const el = this.$refs.thumbViewport;
      if (!el) return;
      const scrollAmount = 120; // width of thumbnail (110px) + gap (10px)
      if (direction === 'prev') {
        el.scrollBy({ left: -scrollAmount * 2, behavior: 'smooth' });
      } else {
        el.scrollBy({ left: scrollAmount * 2, behavior: 'smooth' });
      }
    },
    scrollActiveThumbIntoView(idx) {
      this.$nextTick(() => {
        const el = this.$refs.thumbViewport;
        const items = this.$refs.thumbItems;
        if (el && items && items[idx]) {
          const item = items[idx];
          const containerWidth = el.clientWidth;
          const itemLeft = item.offsetLeft;
          const itemWidth = item.clientWidth;
          
          if (itemLeft < el.scrollLeft) {
            el.scrollTo({ left: itemLeft, behavior: 'smooth' });
          } else if (itemLeft + itemWidth > el.scrollLeft + containerWidth) {
            el.scrollTo({ left: itemLeft + itemWidth - containerWidth, behavior: 'smooth' });
          }
        }
      });
    },
    goToProductDetail(product) {
      this.$router.push(getProductDetailUrl(product));
    },
    async loadRelatedProducts(categoryId, currentProductId) {
      try {
        const response = await axios.get("/api/san-pham", {
          params: { id_danh_muc: categoryId },
        });
        const list = response.data.data || [];
        this.related = list.filter((p) => p.id !== currentProductId).slice(0, 4);
      } catch (error) {
        console.error("Lỗi khi tải sản phẩm tương tự:", error);
      }
    },
    // Chọn giá trị thuộc tính (tự động cập nhật selectedAttributes)
    selectAttribute(key, value) {
      this.selectedAttributes = { ...this.selectedAttributes, [key]: value };
      this.showVariantImage = true;
    },
    selectThumbnail(i) {
      this.activeImg = i;
      const img = this.allImages[i];
      if (img && img.isVariant && img.variant) {
        const vt = img.variant;
        const tt = typeof vt.thuoc_tinh === 'string' ? JSON.parse(vt.thuoc_tinh) : vt.thuoc_tinh;
        if (tt) {
          this.selectedAttributes = { ...tt };
        }
        this.showVariantImage = true;
      } else {
        this.showVariantImage = false;
      }
    },
    isThumbActive(img, i) {
      if (this.showVariantImage && this.currentVariant && this.currentVariant.hinh_anh) {
        return img.duong_dan_anh === this.currentVariant.hinh_anh;
      }
      return this.activeImg === i;
    },
    // Chuyển đổi key của thuộc tính sang nhãn hiển thị
    prettifyKey(key) {
      const map = {
        'mau': 'Màu sắc', 'mau_sac': 'Màu sắc', 'color': 'Màu sắc', 'mau_se': 'Màu sắc',
        'kich_thuoc': 'Kích thước', 'size': 'Kích thước', 'kich_co': 'Kích cỡ',
        'chat_lieu': 'Chất liệu', 'phien_ban': 'Phiên bản', 'bo_suu_tap': 'Bộ sưu tập',
        'tyle': 'Tỷ lệ', 'scale': 'Tỷ lệ', 'loai': 'Loại', 'thuong_hieu': 'Thương hiệu',
      };
      return map[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    },
    getProductImageUrl(imagePath) {
      if (!imagePath) return "https://via.placeholder.com/500?text=No+Image";
      if (imagePath.startsWith("http")) {
        return imagePath;
      }
      return "" + (imagePath.startsWith("/") ? "" : "/") + imagePath;
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
          let friendlyKey = key;
          if (key === 'mau_sac' || key === 'color' || key === 'Color' || key === 'Màu sắc') friendlyKey = 'Màu sắc';
          if (key === 'kich_thuoc' || key === 'size' || key === 'Size' || key === 'Kích thước') friendlyKey = 'Kích thước';
          attrs.push(`${friendlyKey}: ${value}`);
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
    async fetchReviews(page = 1) {
      const id = this.$route.params.id;
      if (!id) return;
      this.reviewsLoading = true;
      try {
        const params = {
          page: page,
        };
        if (this.reviewFilters.sao) {
          params.sao = this.reviewFilters.sao;
        }
        if (this.reviewFilters.co_anh) {
          params.co_anh = 1;
        }
        if (this.reviewFilters.co_binh_luan) {
          params.co_binh_luan = 1;
        }
        const res = await axios.get(`/api/san-pham/${id}/danh-gia`, { params });
        if (res.data.status) {
          this.reviews = res.data.data.reviews.data || [];
          this.reviewSummary = res.data.data.summary || null;
          this.reviewsPagination = {
            current_page: res.data.data.reviews.current_page || 1,
            last_page: res.data.data.reviews.last_page || 1,
          };
        }
      } catch (err) {
        console.error("Lỗi khi tải đánh giá:", err);
      } finally {
        this.reviewsLoading = false;
      }
    },
    resetReviewFilters() {
      this.reviewFilters = {
        sao: null,
        co_anh: 0,
        co_binh_luan: 0
      };
      this.activeReviewFilter = 'all';
    },
    applyReviewFilter(filterType) {
      this.activeReviewFilter = filterType;
      this.reviewFilters = {
        sao: null,
        co_anh: 0,
        co_binh_luan: 0
      };
      if (filterType === 'all') {
        // do nothing, already null
      } else if (['5', '4', '3', '2', '1'].includes(filterType)) {
        this.reviewFilters.sao = parseInt(filterType);
      } else if (filterType === 'images') {
        this.reviewFilters.co_anh = 1;
      } else if (filterType === 'comments') {
        this.reviewFilters.co_binh_luan = 1;
      }
      this.fetchReviews(1);
    },
    getStarPercentage(star) {
      if (!this.reviewSummary || !this.reviewSummary.total) return 0;
      const count = this.reviewSummary.stars[star] || 0;
      return Math.round((count / this.reviewSummary.total) * 100);
    },
    getStarCount(star) {
      return this.reviewSummary?.stars[star] || 0;
    },
    openLightbox(imgUrl) {
      this.activeLightboxImg = imgUrl;
    },
    getRandomAvatarBg(name) {
      if (!name) return "linear-gradient(135deg, #e11d48, #8b5cf6)";
      const colors = [
        "linear-gradient(135deg, #3b82f6, #1d4ed8)",
        "linear-gradient(135deg, #10b981, #047857)",
        "linear-gradient(135deg, #f59e0b, #b45309)",
        "linear-gradient(135deg, #ef4444, #b91c1c)",
        "linear-gradient(135deg, #8b5cf6, #5b21b6)",
        "linear-gradient(135deg, #ec4899, #be185d)"
      ];
      let sum = 0;
      for (let i = 0; i < name.length; i++) {
        sum += name.charCodeAt(i);
      }
      return colors[sum % colors.length];
    }
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
  padding-top: 15px;
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
  margin-top: 14px;
  width: 100%;
}
.pd-thumbs-grid {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}
.pd-thumbs-slider-wrapper {
  position: relative;
  width: 550px; /* 4 * 80px + 3 * 10px = 350px */
  margin: 14px auto 0 auto;
}
.pd-thumbs-viewport {
  overflow-x: auto;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
  width: 100%;
  border-radius: 12px;
}
.pd-thumbs-viewport::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}
.pd-thumbs-track {
  display: flex;
  gap: 10px;
}
.pd-thumb-el {
  width: 110px;
  height: 110px;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.pd-thumb-el:hover {
  border-color: #e11d48;
}
.pd-thumb-el.active {
  border-color: #e11d48;
  border-width: 2.5px;
  box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
}
.pd-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.pd-thumbs-btn {
  position: absolute;
  top: 0;
  height: 110px; /* Matches thumbnail height */
  width: 24px;  /* Narrower overlay for better image visibility */
  background: rgba(58, 57, 57, 0.4);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 24px;
  line-height: 1;
  padding-bottom: 4px;
  cursor: pointer;
  z-index: 10;
  transition: opacity 0.25s ease, background 0.25s ease;
  user-select: none;
}
.pd-thumbs-btn:hover {
  background: rgba(0, 0, 0, 0.65);
}
.pd-thumbs-btn.hidden {
  opacity: 0;
  pointer-events: none;
}
.pd-thumbs-btn-prev {
  left: 0;
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
}
.pd-thumbs-btn-next {
  right: 0;
  border-top-right-radius: 12px;
  border-bottom-right-radius: 12px;
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
  margin-bottom: 10px;
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
  margin-bottom: 10px;
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
  margin-bottom: 10px;
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

/* Custom Review Filters and Styles */
.pd-filter-chip {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}
.pd-filter-chip:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.pd-filter-chip.active {
  background: #d70018;
  border-color: #d70018;
  color: #fff;
  box-shadow: 0 2px 8px rgba(215, 0, 24, 0.2);
}
</style>


