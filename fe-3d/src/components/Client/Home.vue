<template>
  <!--  HERO  -->
 <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-eyebrow">Sản Phẩm In 3D Cao Cấp</div>
        <h1 class="hero-heading">
          Khám Phá Sản Phẩm<br />
          <em>Độc Đáo</em><br />
          Của Riêng Bạn
        </h1>
        <p class="hero-subtext">
          Tuyển tập các phôi in 3D FDM và Resin chất lượng cao, từ sa bàn kiến trúc đến robot mecha và các mẫu khớp động. Bề mặt láng mịn, độ nét hoàn hảo dành cho giới đam mê sáng tạo.
        </p>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-value">2<span>K</span>+</div>
            <div class="hero-stat-label">Mẫu phôi in</div>
          </div>
          <div>
            <div class="hero-stat-value">48<span>h</span></div>
            <div class="hero-stat-label">Giao hỏa tốc</div>
          </div>
          <div>
            <div class="hero-stat-value">4.9<span></span></div>
            <div class="hero-stat-label">Đánh giá sao</div>
          </div>
        </div>
      </div>
      
      <div class="hero-visual">
        <div class="model-scene">
          <div class="hero-badge"><i class="fa-solid fa-fire"></i> Xu Hướng Mới</div>
          
          <div class="model-float mf-1">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-dungeon"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Lâu Đài Sa Bàn</div>
              </div>
            </div>
          </div>
          
          <div class="model-float mf-2">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-dragon"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Rồng Khớp Động</div>
              </div>
            </div>
          </div>
          
          <div class="model-float mf-3">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-robot"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Mecha Chibi</div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </section>
  <!--  CATEGORIES SECTION  -->
  <div class="categories-section">
    <div class="categories-inner">
      <span class="categories-label">Danh mục sản phẩm:</span>
      <div class="cat-tabs">
        <button 
          class="cat-tab" 
          :class="{ active: selectedCategoryId === null }"
          @click="selectCategory(null)"
        >
          Tất cả
        </button>
        <button 
          v-for="cat in categories" 
          :key="cat.id"
          class="cat-tab"
          :class="{ active: selectedCategoryId === cat.id }"
          @click="selectCategory(cat.id)"
        >
          {{ cat.ten_danh_muc }}
        </button>
      </div>
    </div>
  </div>

  <!--  FEATURED MODELS  -->
  <div class="products-bg">
    <div class="section">
      <div class="section-header">
        <div class="section-title-wrap">
          <div class="section-eyebrow">Lựa chọn hàng đầu</div>
          <h2 class="section-title">Sản phẩm nổi bật</h2>
        </div>
        <a href="#" class="btn-view-all" @click.prevent="selectCategory(null)">Xem tất cả →</a>
      </div>

      <div v-if="loading" class="empty-products">
        <i class="fa-solid fa-circle-notch fa-spin"></i> &#272;ang t&#7843;i s&#7843;n ph&#7849;m...</div>
      
      <div v-else-if="products.length > 0">
        <div class="product-grid">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="product-card"
            @click="goToDetail(product.id)"
          >
            <!-- Badge -->
            <div class="product-badge badge-new" v-if="product.tinh_trang === 1">{{ '\u0110ang b\u00e1n' }}</div>
            <div class="product-badge badge-sale" v-else-if="product.tinh_trang === 0">{{ 'H\u1ebft h\u00e0ng' }}</div>
            <div class="product-badge badge-ltd" v-else-if="product.tinh_trang === 2">{{ '\u1ea8n' }}</div>
            
            <div class="product-img-wrap">
              <img :src="getProductImage(product)" :alt="product.ten_san_pham" class="product-img" />
            </div>
            <div class="product-info">
              <div class="product-brand">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : '\u004d\u00f4 h\u00ecnh' }}</div>
              <div class="product-name truncate" :title="product.ten_san_pham">{{ product.ten_san_pham }}</div>
              <div class="product-rating">
                <span class="stars"></span>
                <span>(5.0)</span>
              </div>
              <div class="product-footer">
                <div class="product-price-group">
                  <span class="product-price">{{ formatPrice(product.gia_co_ban) }}</span>
                  <span class="product-price-orig" v-if="product.gia_goc && product.gia_goc > 0">
                    {{ formatPrice(product.gia_goc) }}
                  </span>
                </div>
                <button class="btn-view" @click.stop="goToDetail(product.id)">View</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrap" v-if="totalPages > 1">
          <button 
            class="page-btn" 
            :disabled="currentPage === 1"
            @click="fetchProducts(currentPage - 1)"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button 
            v-for="page in totalPages" 
            :key="page"
            class="page-btn"
            :class="{ active: currentPage === page }"
            @click="fetchProducts(page)"
          >
            {{ page }}
          </button>
          <button 
            class="page-btn" 
            :disabled="currentPage === totalPages"
            @click="fetchProducts(currentPage + 1)"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>
      
      <div v-else class="empty-products">
        {{ 'Kh\u00f4ng t\u00ecm th\u1ea5y s\u1ea3n ph\u1ea9m n\u00e0o ph\u00f9 h\u1ee3p.' }}
      </div>
    </div>
  </div>
  <!--  FEATURES  -->
<div class="features-bg">
    <div style="max-width: 1280px; margin: 0 auto">
      <div class="features-grid">
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
          <div>
            <div class="feature-title">Miễn Phí Giao Hàng</div>
            <div class="feature-desc">
              Áp dụng cho đơn hàng từ 500.000 ₫. Hỗ trợ giao hỏa tốc nội thành.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-box-open"></i></div>
          <div>
            <div class="feature-title">Đóng Gói An Toàn</div>
            <div class="feature-desc">
              Bọc chống sốc kỹ lưỡng, đảm bảo phôi in không gãy vỡ.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
          <div>
            <div class="feature-title">Chất Lượng Đảm Bảo</div>
            <div class="feature-desc">
              Sử dụng nhựa in theo yêu cầu, đảm bảo độ bền và tính thẩm mỹ cao.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-rotate-left"></i></div>
          <div>
            <div class="feature-title">Hỗ Trợ Đổi Trả</div>
            <div class="feature-desc">
              Đổi mới hoặc in lại trong 7 ngày nếu có lỗi cong vênh từ xưởng.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
  <!--  PROMO BANNER  -->
 <div class="banner-strip">
    <div class="banner-strip-inner">
      <div class="banner-content-left">
        <span class="banner-tag">Bộ Sưu Tập In 3D</span>
        <h2 class="banner-title">Nghệ Thuật<br /><em>In 3D</em><br />Đỉnh Cao</h2>
        <p class="banner-desc">
          Khám phá các mẫu mô hình được chế tác bằng công nghệ in FDM và Resin tiên tiến. Bề mặt láng mịn, chi tiết siêu nét, hoàn hảo để trưng bày hoặc tự tay sơn phết sáng tạo.
        </p>
        <button class="btn-banner">Khám Phá Ngay</button>
      </div>
      <div class="banner-visual">
        <div class="banner-deco"></div>
        <div class="banner-deco banner-deco-2"></div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-dragon"></i></span>
          <div class="banner-model-name">Rồng Khớp Động</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-robot"></i></span>
          <div class="banner-model-name">Mecha Gundam</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-cube"></i></span>
          <div class="banner-model-name">Sa Bàn Diorama</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { getProductDetailUrl } from "../../router/utils";

export default {
  name: "Home",
  data() {
    return {
      products: [],
      categories: [],
      selectedCategoryId: null,
      searchQuery: "",
      selectedScale: "",
      selectedSort: "featured",
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      loading: false,
      suggestions: [],
      showSuggestions: false,
      suggestDebounceTimer: null,
      suggestCache: {},
    };
  },
  mounted() {
    this.fetchCategories();
    this.applyRouteFilters();
  },
  beforeUnmount() {
    if (this.suggestDebounceTimer) clearTimeout(this.suggestDebounceTimer);
  },
  watch: {
    "$route.query": {
      handler() {
        this.applyRouteFilters();
      },
      deep: true,
    },
    searchQuery() {
      this.debouncedSuggest();
    },
  },
  methods: {
    debouncedSuggest() {
      if (this.suggestDebounceTimer) clearTimeout(this.suggestDebounceTimer);
      this.suggestDebounceTimer = setTimeout(() => {
        this.fetchSuggestions();
      }, 400);
    },
    async fetchSuggestions() {
      const keyword = this.searchQuery.trim();
      if (keyword.length < 2) {
        this.suggestions = [];
        return;
      }

      const cacheKey = `${keyword}|${this.selectedCategoryId || ""}`;
      if (this.suggestCache[cacheKey]) {
        this.suggestions = this.suggestCache[cacheKey];
        return;
      }

      try {
        const res = await axios.get("/api/tim-kiem/goi-y", {
          params: {
            q: keyword,
            id_danh_muc: this.selectedCategoryId || "",
            limit: 8,
          },
        });
        const list = res.data?.suggestions || [];
        this.suggestCache[cacheKey] = list;
        this.suggestions = list;
      } catch {
        this.suggestions = [];
      }
    },
    hideSuggestionsWithDelay() {
      setTimeout(() => {
        this.showSuggestions = false;
      }, 150);
    },
    async selectSuggestion(item) {
      this.searchQuery = item.label || "";
      this.showSuggestions = false;
      await this.trackSuggestionClick(item);
      this.handleSearch();
    },
    async trackSuggestionClick(item) {
      try {
        await axios.post("/api/tim-kiem/track", {
          keyword: this.searchQuery,
          suggestion: item.label || "",
          type: item.type || "product",
          id_san_pham: item.type === "product" ? item.id : null,
          id_danh_muc: item.id_danh_muc || null,
        });
      } catch {}
    },
    applyRouteFilters() {
      const query = this.$route.query || {};
      this.searchQuery = query.search ? String(query.search) : "";
      this.selectedCategoryId = query.id_danh_muc ? Number(query.id_danh_muc) : null;
      this.fetchProducts(1);
    },
    async fetchCategories() {
      try {
        const response = await axios.get("/api/danh-muc");
        this.categories = response.data;
      } catch (error) {
        console.error("L\u1ed7i khi t\u1ea3i danh m\u1ee5c:", error);
      }
    },
    async fetchProducts(page = 1) {
      this.loading = true;
      this.currentPage = page;
      try {
        const params = {
          page: this.currentPage,
          per_page: 10,
        };
        
        if (this.selectedCategoryId) {
          params.id_danh_muc = this.selectedCategoryId;
        }
        
        if (this.searchQuery.trim()) {
          params.search = this.searchQuery.trim();
        }
        
        if (this.selectedScale) {
          params.scale = this.selectedScale;
        }
        
        if (this.selectedSort && this.selectedSort !== "featured") {
          params.sort = this.selectedSort;
        }
        
        const response = await axios.get("/api/san-pham", { params });
        const data = response.data;
        
        this.products = data.data;
        this.totalPages = data.last_page;
        this.totalItems = data.total;
      } catch (error) {
        console.error("L\u1ed7i khi t\u1ea3i s\u1ea3n ph\u1ea9m:", error);
      } finally {
        this.loading = false;
      }
    },
    selectCategory(id) {
      this.selectedCategoryId = id;
      this.searchQuery = "";
      this.$router.push({
        path: "/san-pham",
        query: {
          ...(this.selectedCategoryId ? { id_danh_muc: this.selectedCategoryId } : {}),
        },
      });
    },
    handleSearch() {
      this.showSuggestions = false;
      this.$router.push({
        path: "/san-pham",
        query: {
          ...(this.searchQuery.trim() ? { search: this.searchQuery.trim() } : {}),
          ...(this.selectedCategoryId ? { id_danh_muc: this.selectedCategoryId } : {}),
        },
      });
    },
    handleFilterChange() {
      this.fetchProducts(1);
    },
    getProductImage(product) {
      if (product.hinh_anhs && product.hinh_anhs.length > 0) {
        const mainImg = product.hinh_anhs.find((img) => img.la_anh_dai_dien) || product.hinh_anhs[0];
        const path = mainImg.duong_dan_anh;
        if (path.startsWith("http")) {
          return path;
        }
        return "" + (path.startsWith("/") ? "" : "/") + path;
      }
      return "https://via.placeholder.com/300?text=No+Image";
    },
    getProductScaleOrSize(product) {
      const desc = product.mo_ta || "";
      const match = desc.match(/1:\d+/);
      if (match) return "T\u1ec9 l\u1ec7 " + match[0];

      if (product.bien_thes && product.bien_thes.length > 0) {
        const firstVariant = product.bien_thes[0];
        if (firstVariant.thuoc_tinh && firstVariant.thuoc_tinh.size) {
          return "K\u00edch th\u01b0\u1edbc: " + firstVariant.thuoc_tinh.size;
        }
      }
      return "K\u00edch th\u01b0\u1edbc: Ti\u00eau chu\u1ea9n";
    },
    formatPrice(value) {
      if (!value) return "0 \u0111";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },
    goToDetail(productOrId) {
      if (typeof productOrId === 'object' && productOrId !== null) {
        this.$router.push(getProductDetailUrl(productOrId));
      } else {
        const p = this.products.find(x => x.id === productOrId);
        if (p) {
          this.$router.push(getProductDetailUrl(p));
        } else {
          this.$router.push("/product/" + productOrId);
        }
      }
    },
  },
};
</script>
<style scoped>
.search-field-wrap {
  position: relative;
}

.suggestions-box {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  z-index: 1200;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1);
  max-height: 320px;
  overflow-y: auto;
}

.suggestion-item {
  width: 100%;
  border: none;
  background: #fff;
  text-align: left;
  padding: 10px 12px;
  cursor: pointer;
  font-size: 14px;
  color: #111827;
}

.suggestion-item:hover {
  background: #f9fafb;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-img {
  transform: scale(1.06);
}

.empty-products {
  text-align: center;
  padding: 48px;
  font-size: 16px;
  color: #6b7280;
  font-weight: 500;
  width: 100%;
}

.empty-products i {
  margin-right: 8px;
  font-size: 20px;
}

.pagination-wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 40px;
  width: 100%;
}

.page-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background-color: var(--surface);
  color: var(--text);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--red);
  color: var(--red);
  background-color: #fff;
}

.page-btn.active {
  background-color: var(--red);
  color: #fff;
  border-color: var(--red);
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.product-price-group {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}
.product-price-orig {
  font-size: 12px;
  color: #9ca3af;
  text-decoration: line-through;
  font-weight: 500;
}
</style>

