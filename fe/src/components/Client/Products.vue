<template>
  <div class="products-page-container">
    <!-- Header Section -->
    <div class="products-header-banner">
      <div class="banner-inner">
        <h1 class="products-page-title">Cửa Hàng BALAB</h1>
        <p class="products-page-subtitle">Khám phá bộ sưu tập mô hình 3D cao cấp được in tỉ mỉ với độ nét hoàn hảo.</p>
      </div>
    </div>

    <div class="products-layout-wrapper">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar" :class="{ 'mobile-open': isMobileFilterOpen }">
        <div class="sidebar-header">
          <h3>Bộ Lọc Sản Phẩm</h3>
          <button class="btn-close-sidebar" @click="isMobileFilterOpen = false">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Categories Filter -->
        <div class="filter-section">
          <label class="filter-label">Danh Mục</label>
          <div class="filter-list">
            <button 
              class="filter-list-item" 
              :class="{ active: localCategoryId === null }"
              @click="selectCategory(null)"
            >
              <span class="bullet"></span> Tất cả sản phẩm
            </button>
            <button 
              v-for="cat in categories" 
              :key="cat.id"
              class="filter-list-item" 
              :class="{ active: localCategoryId === cat.id }"
              @click="selectCategory(cat.id)"
            >
              <span class="bullet"></span> {{ cat.ten_danh_muc }}
            </button>
          </div>
        </div>

        <!-- Scale Filter -->
        <div class="filter-section">
          <label class="filter-label">Tỉ lệ (Scale)</label>
          <div class="scale-chips-wrapper">
            <button 
              v-for="sc in availableScales" 
              :key="sc" 
              class="scale-chip"
              :class="{ active: localScale === sc }"
              @click="selectScale(sc)"
            >
              {{ sc === '' ? 'Tất cả' : sc }}
            </button>
          </div>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-section">
          <label class="filter-label">Khoảng Giá (VND)</label>
          <div class="price-inputs">
            <input 
              type="number" 
              v-model.number="localMinPrice" 
              placeholder="Từ" 
              min="0"
              class="price-input"
              @keyup.enter="applyFilters"
            />
            <span class="price-separator">-</span>
            <input 
              type="number" 
              v-model.number="localMaxPrice" 
              placeholder="Đến" 
              min="0"
              class="price-input"
              @keyup.enter="applyFilters"
            />
          </div>
          <!-- Quick Price Options -->
          <div class="quick-prices">
            <button @click="setQuickPrice(0, 100000)">Dưới 100k</button>
            <button @click="setQuickPrice(100000, 500000)">100k - 500k</button>
            <button @click="setQuickPrice(500000, null)">Trên 500k</button>
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="filter-actions">
          <button class="btn-apply-filters" @click="applyFilters">
            <i class="fa-solid fa-filter"></i> Áp Dụng Lọc
          </button>
          <button class="btn-reset-filters" @click="clearFilters">
            Xóa Tất Cả Lọc
          </button>
        </div>
      </aside>

      <!-- Products Content Area -->
      <main class="products-content-area">
        <!-- Top Toolbar -->
        <div class="products-toolbar">
          <div class="toolbar-left">
            <button class="btn-toggle-mobile-filters" @click="isMobileFilterOpen = true">
              <i class="fa-solid fa-sliders"></i> Bộ Lọc
            </button>
            <div class="results-count" v-if="!loading">
              <strong>Tìm thấy các sản phẩm</strong>
            </div>
          </div>
          
          <div class="toolbar-right">
            <!-- Sort dropdown -->
            <div class="sort-selector-wrap">
              <span class="sort-label">Sắp xếp:</span>
              <select v-model="localSort" @change="applySort" class="sort-select">
                <option value="">Mặc định</option>
                <option value="price_asc">Giá: Thấp đến Cao</option>
                <option value="price_desc">Giá: Cao đến Thấp</option>
                <option value="newest">Mới nhất</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Active Filter Tags -->
        <div class="active-filters-tags" v-if="hasActiveFilters">
          <span class="tag-title">Đang lọc theo:</span>
          <span class="filter-tag" v-if="localSearch">
            Từ khóa: "{{ localSearch }}" <i class="fa-solid fa-xmark" @click="removeSearchTag"></i>
          </span>
          <span class="filter-tag" v-if="localCategoryId">
            Danh mục: {{ getCategoryName(localCategoryId) }} <i class="fa-solid fa-xmark" @click="removeCategoryTag"></i>
          </span>
          <span class="filter-tag" v-if="localScale">
            Tỉ lệ: {{ localScale }} <i class="fa-solid fa-xmark" @click="removeScaleTag"></i>
          </span>
          <span class="filter-tag" v-if="localMinPrice || localMaxPrice">
            Giá: {{ formatPriceRange(localMinPrice, localMaxPrice) }} <i class="fa-solid fa-xmark" @click="removePriceTag"></i>
          </span>
          <button class="btn-clear-tags" @click="clearFilters">Xóa hết</button>
        </div>

        <!-- Products Grid / Loading / Empty -->
        <div v-if="loading" class="products-loading-state">
          <i class="fa-solid fa-circle-notch fa-spin"></i>
          <p>Đang tải danh sách sản phẩm...</p>
        </div>

        <div v-else-if="products.length > 0">
          <div class="product-grid">
            <div 
              v-for="product in products" 
              :key="product.id" 
              class="product-card"
              @click="goToDetail(product)"
            >
              <!-- Badge -->
              <div class="product-badge badge-new" v-if="product.tinh_trang === 1">Đang bán</div>
              <div class="product-badge badge-sale" v-else-if="product.tinh_trang === 0">Hết hàng</div>
              <div class="product-badge badge-ltd" v-else-if="product.tinh_trang === 2">Ẩn</div>
              
              <div class="product-img-wrap">
                <img :src="getProductImage(product)" :alt="product.ten_san_pham" class="product-img" />
              </div>
              <div class="product-info">
                <div class="product-brand">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : 'Mô hình' }}</div>
                <div class="product-name truncate" :title="product.ten_san_pham">{{ product.ten_san_pham }}</div>
                <div class="product-rating">
                  <span class="stars">★★★★★</span>
                  <span>(5.0)</span>
                </div>
                <div class="product-footer">
                  <div class="product-price-group">
                    <span class="product-price">{{ formatPrice(product.gia_co_ban) }}</span>
                    <span class="product-price-orig" v-if="product.gia_goc && product.gia_goc > 0">
                      {{ formatPrice(product.gia_goc) }}
                    </span>
                  </div>
                  <button class="btn-view" @click.stop="goToDetail(product)">Xem</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="pagination-wrap" v-if="totalPages > 1">
            <button 
              class="page-btn page-arrow" 
              :disabled="currentPage === 1"
              @click="changePage(currentPage - 1)"
            >
              <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 11L2 6L7 1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            
            <template v-for="(page, index) in paginatedPages" :key="index">
              <span v-if="page === '...'" class="page-ellipsis">...</span>
              <button 
                v-else
                class="page-btn"
                :class="{ active: currentPage === page }"
                @click="changePage(page)"
              >
                {{ page }}
              </button>
            </template>
            
            <button 
              class="page-btn page-arrow" 
              :disabled="currentPage === totalPages"
              @click="changePage(currentPage + 1)"
            >
              <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 11L6 6L1 1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>

        <div v-else class="products-empty-state">
          <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
          <h3>Không tìm thấy sản phẩm nào</h3>
          <p>Hãy thử thay đổi điều kiện lọc hoặc từ khóa tìm kiếm khác nhé.</p>
          <button class="btn-reset-empty" @click="clearFilters">Xóa tất cả bộ lọc</button>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { getProductDetailUrl } from "../../router/utils";

export default {
  name: "Products",
  data() {
    return {
      products: [],
      categories: [],
      availableScales: ["", "1:6", "1:12", "1:18", "1:24", "1:64"],
      
      // Filters models
      localSearch: "",
      localCategoryId: null,
      localScale: "",
      localMinPrice: null,
      localMaxPrice: null,
      localSort: "",
      
      // Pagination states
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      
      loading: false,
      isMobileFilterOpen: false,
    };
  },
  computed: {
    hasActiveFilters() {
      return !!(
        this.localSearch || 
        this.localCategoryId !== null || 
        this.localScale !== "" || 
        this.localMinPrice !== null || 
        this.localMaxPrice !== null
      );
    },
    paginatedPages() {
      const current = this.currentPage;
      const total = this.totalPages;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      }
      if (current <= 4) {
        return [1, 2, 3, 4, 5, "...", total];
      }
      if (current >= total - 3) {
        return [1, "...", total - 4, total - 3, total - 2, total - 1, total];
      }
      return [1, "...", current - 1, current, current + 1, "...", total];
    }
  },
  watch: {
    "$route.query": {
      handler() {
        this.syncFromUrlAndFetch();
      },
      deep: true,
    }
  },
  mounted() {
    this.fetchCategories().then(() => {
      this.syncFromUrlAndFetch();
    });
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get("/api/danh-muc");
        this.categories = response.data;
      } catch (error) {
        console.error("Lỗi khi tải danh mục:", error);
      }
    },
    async fetchProducts() {
      this.loading = true;
      try {
        const params = {
          page: this.currentPage,
          per_page: 20,
        };

        if (this.localSearch) {
          params.search = this.localSearch;
        }
        if (this.localCategoryId !== null) {
          params.id_danh_muc = this.localCategoryId;
        }
        if (this.localScale) {
          params.scale = this.localScale;
        }
        if (this.localMinPrice !== null) {
          params.min_price = this.localMinPrice;
        }
        if (this.localMaxPrice !== null) {
          params.max_price = this.localMaxPrice;
        }
        if (this.localSort) {
          params.sort = this.localSort;
        }

        const response = await axios.get("/api/san-pham", { params });
        const data = response.data;

        this.products = data.data || [];
        this.totalPages = data.last_page || 1;
        this.totalItems = data.total || 0;
      } catch (error) {
        console.error("Lỗi khi tải sản phẩm:", error);
        this.products = [];
        this.totalPages = 1;
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    syncFromUrlAndFetch() {
      const query = this.$route.query || {};
      
      this.localSearch = query.search || "";
      this.localCategoryId = query.id_danh_muc ? Number(query.id_danh_muc) : null;
      this.localScale = query.scale || "";
      this.localMinPrice = query.min_price ? Number(query.min_price) : null;
      this.localMaxPrice = query.max_price ? Number(query.max_price) : null;
      this.localSort = query.sort || "";
      this.currentPage = query.page ? Number(query.page) : 1;

      this.fetchProducts();
    },
    applyFilters() {
      this.isMobileFilterOpen = false;
      const query = {};

      if (this.localSearch) query.search = this.localSearch;
      if (this.localCategoryId !== null) query.id_danh_muc = this.localCategoryId;
      if (this.localScale) query.scale = this.localScale;
      if (this.localMinPrice !== null) query.min_price = this.localMinPrice;
      if (this.localMaxPrice !== null) query.max_price = this.localMaxPrice;
      if (this.localSort) query.sort = this.localSort;
      
      // When filters change, reset page to 1
      this.$router.push({
        path: "/san-pham",
        query,
      });
    },
    applySort() {
      this.applyFilters();
    },
    selectCategory(catId) {
      this.localCategoryId = catId;
      this.applyFilters();
    },
    selectScale(scale) {
      this.localScale = scale;
      this.applyFilters();
    },
    setQuickPrice(min, max) {
      this.localMinPrice = min;
      this.localMaxPrice = max;
      this.applyFilters();
    },
    clearFilters() {
      this.localSearch = "";
      this.localCategoryId = null;
      this.localScale = "";
      this.localMinPrice = null;
      this.localMaxPrice = null;
      this.localSort = "";
      this.currentPage = 1;
      this.isMobileFilterOpen = false;

      this.$router.push({
        path: "/san-pham",
      });
    },
    changePage(page) {
      if (page < 1 || page > this.totalPages) return;
      
      const query = { ...this.$route.query };
      query.page = page;

      this.$router.push({
        path: "/san-pham",
        query,
      });
    },
    removeSearchTag() {
      this.localSearch = "";
      this.applyFilters();
    },
    removeCategoryTag() {
      this.localCategoryId = null;
      this.applyFilters();
    },
    removeScaleTag() {
      this.localScale = "";
      this.applyFilters();
    },
    removePriceTag() {
      this.localMinPrice = null;
      this.localMaxPrice = null;
      this.applyFilters();
    },
    getCategoryName(id) {
      const cat = this.categories.find((c) => c.id === id);
      return cat ? cat.ten_danh_muc : "";
    },
    formatPriceRange(min, max) {
      if (min !== null && max !== null) {
        return `${this.formatPrice(min)} - ${this.formatPrice(max)}`;
      }
      if (min !== null) {
        return `Trên ${this.formatPrice(min)}`;
      }
      if (max !== null) {
        return `Dưới ${this.formatPrice(max)}`;
      }
      return "";
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
    formatPrice(value) {
      if (!value) return "0 đ";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },
    goToDetail(product) {
      this.$router.push(getProductDetailUrl(product));
    },
  },
};
</script>

<style scoped>
.products-page-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 14px 16px 50px 16px;
}

.products-header-banner {
  background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
  border-radius: 16px;
  padding: 20px 32px;
  margin-bottom: 14px;
  border-left: 5px solid var(--red);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.products-page-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 36px;
  font-weight: 800;
  color: #ffffff;
  text-transform: uppercase;
  margin-bottom: 0px;
  letter-spacing: 0.05em;
}

.products-page-subtitle {
  color: #9ca3af;
  font-size: 15px;
  max-width: 600px;
}

.products-layout-wrapper {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 20px;
  align-items: start;
}

/* Sidebar Filters */
.filter-sidebar {
  background: var(--surface);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 16px rgba(0,0,0,0.02);
  position: sticky;
  top: 90px;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid var(--border);
}

.sidebar-header h3 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 20px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text);
  letter-spacing: 0.02em;
}

.btn-close-sidebar {
  display: none;
  background: none;
  border: none;
  color: var(--muted);
  font-size: 20px;
  cursor: pointer;
}

.filter-section {
  margin-bottom: 24px;
}

.filter-label {
  display: block;
  font-weight: 700;
  font-size: 13px;
  text-transform: uppercase;
  color: var(--text);
  margin-bottom: 12px;
  letter-spacing: 0.05em;
}

/* Search input styling */
.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper input {
  width: 100%;
  padding: 10px 40px 10px 14px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 14px;
  background: var(--bg);
  color: var(--text);
  transition: all 0.2s;
}

.search-input-wrapper input:focus {
  outline: none;
  border-color: var(--red);
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.1);
}

.btn-clear-search {
  position: absolute;
  right: 36px;
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  padding: 4px;
}

.btn-clear-search:hover {
  color: var(--red);
}

.btn-search-icon {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
}

.btn-search-icon:hover {
  color: var(--red);
}

/* Categories filter list */
.filter-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-list-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  text-align: left;
  border: none;
  background: none;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 14px;
  color: var(--muted);
  cursor: pointer;
  transition: all 0.2s;
}

.filter-list-item .bullet {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--border);
  transition: all 0.2s;
}

.filter-list-item:hover {
  background: rgba(215, 0, 24, 0.04);
  color: var(--red);
}

.filter-list-item:hover .bullet {
  background: var(--red);
}

.filter-list-item.active {
  background: rgba(215, 0, 24, 0.08);
  color: var(--red);
  font-weight: 600;
}

.filter-list-item.active .bullet {
  background: var(--red);
  transform: scale(1.3);
}

/* Scale chips */
.scale-chips-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.scale-chip {
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid var(--border);
  font-size: 13px;
  background: none;
  color: var(--text);
  cursor: pointer;
  transition: all 0.2s;
}

.scale-chip:hover {
  border-color: var(--red);
  color: var(--red);
  background: rgba(215, 0, 24, 0.02);
}

.scale-chip.active {
  background: var(--red);
  color: #ffffff;
  border-color: var(--red);
  box-shadow: 0 4px 10px rgba(215, 0, 24, 0.2);
}

/* Price Range Inputs */
.price-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13.5px;
  background: var(--bg);
  color: var(--text);
  -moz-appearance: textfield;
  appearance: textfield;
}

.price-input::-webkit-outer-spin-button,
.price-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

.price-input:focus {
  outline: none;
  border-color: var(--red);
  background: #ffffff;
}

.price-separator {
  color: var(--muted);
  font-size: 14px;
}

.quick-prices {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}

.quick-prices button {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: none;
  color: var(--muted);
  cursor: pointer;
  transition: all 0.15s;
}

.quick-prices button:hover {
  background: var(--border);
  color: var(--text);
}

/* Actions buttons */
.filter-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}

.btn-apply-filters {
  width: 100%;
  padding: 11px;
  background: var(--red);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-apply-filters:hover {
  background: var(--red-dark);
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.25);
}

.btn-reset-filters {
  width: 100%;
  padding: 9px;
  background: none;
  color: var(--muted);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-reset-filters:hover {
  background: rgba(0, 0, 0, 0.03);
  color: var(--text);
}

/* Right Content Area */
.products-content-area {
  min-width: 0;
}

/* Toolbar */
.products-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  background: var(--surface);
  padding: 8px 15px;
  border-radius: 12px;
  border: 1px solid var(--border);
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-toggle-mobile-filters {
  display: none;
  padding: 8px 16px;
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: var(--text);
  cursor: pointer;
}

.results-count {
  font-size: 14px;
  color: var(--muted);
}

.results-count strong {
  color: var(--text);
}

/* Sort Selector */
.sort-selector-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-label {
  font-size: 13.5px;
  color: var(--muted);
}

.sort-select {
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background-color: var(--surface);
  color: var(--text);
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  outline: none;
}

.sort-select:focus {
  border-color: var(--red);
}

/* Active Tags */
.active-filters-tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 24px;
}

.tag-title {
  font-size: 13px;
  color: var(--muted);
  font-weight: 500;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 12.5px;
  color: var(--text);
  font-weight: 500;
}

.filter-tag i {
  color: var(--muted);
  cursor: pointer;
  font-size: 11px;
}

.filter-tag i:hover {
  color: var(--red);
}

.btn-clear-tags {
  border: none;
  background: none;
  color: var(--red);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
}

.btn-clear-tags:hover {
  text-decoration: underline;
}

/* Loading & Empty States */
.products-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  color: var(--muted);
}

.products-loading-state i {
  font-size: 32px;
  color: var(--red);
  margin-bottom: 16px;
}

.products-empty-state {
  text-align: center;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 60px 40px;
}

.empty-icon {
  font-size: 48px;
  color: var(--muted);
  margin-bottom: 16px;
}

.products-empty-state h3 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 24px;
  color: var(--text);
  margin-bottom: 8px;
}

.products-empty-state p {
  color: var(--muted);
  font-size: 14.5px;
  margin-bottom: 24px;
}

.btn-reset-empty {
  padding: 10px 20px;
  background: var(--red);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-reset-empty:hover {
  background: var(--red-dark);
}

/* Product Cards Custom Tweaks in Products grid */
.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-img {
  transform: scale(1.06);
}

.product-price-group {
  display: flex;
  align-items: baseline;
  gap: 6px;
  flex-wrap: wrap;
}

.product-price-orig {
  font-size: 11px;
  color: #9ca3af;
  text-decoration: line-through;
  font-weight: 500;
}

/* Responsive sidebar */
@media (max-width: 992px) {
  .products-layout-wrapper {
    grid-template-columns: 1fr;
  }
  
  .filter-sidebar {
    position: fixed;
    top: 0;
    left: -320px;
    width: 300px;
    height: 100vh;
    z-index: 2000;
    border-radius: 0;
    overflow-y: auto;
    transition: left 0.3s ease;
    box-shadow: 10px 0 30px rgba(0,0,0,0.15);
  }
  
  .filter-sidebar.mobile-open {
    left: 0;
  }
  
  .btn-close-sidebar {
    display: block;
  }
  
  .btn-toggle-mobile-filters {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
}

/* Pagination screenshot style */
.pagination-wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 30px;
  margin-top: 40px;
  width: 100%;
}

.page-btn {
  min-width: 40px;
  height: 30px;
  border-radius: 2px;
  border: none;
  background: transparent;
  color: rgba(0, 0, 0, 0.4);
  font-size: 14.5px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  font-weight: 400;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, color 0.2s;
  padding: 0 5px;
}

.page-btn:hover:not(:disabled):not(.active) {
  color: #ee4d2d;
}

.page-btn.active {
  background-color: #ee4d2d;
  color: #fff;
  font-weight: 500;
  box-shadow: none;
}

.page-arrow {
  color: rgba(0, 0, 0, 0.26);
}

.page-arrow:hover:not(:disabled) {
  color: rgba(0, 0, 0, 0.54);
}

.page-btn:disabled {
  color: rgba(0, 0, 0, 0.09);
  cursor: not-allowed;
}

.page-ellipsis {
  color: rgba(0, 0, 0, 0.4);
  font-size: 14.5px;
  width: 40px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
</style>
