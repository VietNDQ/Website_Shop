<template>
  <!--  HERO  -->
 <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-eyebrow">Cửa Hàng Trực Tuyến Uy Tín</div>
        <h1 class="hero-heading">
          Mua Bán Mọi Thứ
          <em>Dễ Dàng</em>
          Cùng BALAB
        </h1>
        <p class="hero-subtext">
          BALAB - Cửa hàng trực tuyến cung cấp đa dạng sản phẩm chất lượng cao. Khám phá các bộ sưu tập phong phú từ đồ công nghệ, thời trang, phụ kiện đến các món quà tặng trang trí độc đáo được tuyển chọn kỹ lưỡng.
        </p>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-value">100%</div>
            <div class="hero-stat-label">Chất lượng đảm bảo</div>
          </div>
          <div>
            <div class="hero-stat-value">Nhanh</div>
            <div class="hero-stat-label">Giao toàn quốc</div>
          </div>
          <div>
            <div class="hero-stat-value">24/7</div>
            <div class="hero-stat-label">Hỗ trợ khách hàng</div>
          </div>
        </div>
      </div>
      
      <div class="hero-visual">
        <div class="model-scene">
          <div class="hero-badge"><i class="fa-solid fa-fire"></i> Săn Deal Hot</div>
          
          <div class="model-float mf-1">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-laptop"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Đồ Công Nghệ</div>
              </div>
            </div>
          </div>
          
          <div class="model-float mf-2">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-shirt"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Thời Trang & Lifestyle</div>
              </div>
            </div>
          </div>
          
          <div class="model-float mf-3">
            <div class="model-float-inner">
              <div class="model-float-img"><i class="fa-solid fa-gift"></i></div>
              <div class="model-float-info">
                <div class="model-float-name">Trang Trí & Quà Tặng</div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </section>
  <!-- CATEGORY ICON SHORTCUTS -->
  <div class="category-shortcuts-section">
    <div class="category-shortcuts-inner">
      <div 
        class="shortcut-item" 
        :class="{ active: selectedCategoryId === null }"
        @click="changeTab(null)"
      >
        <div class="shortcut-icon-wrap" style="background: linear-gradient(135deg, #0984e3, #00cec9)">
          <i class="fa-solid fa-border-all"></i>
        </div>
        <div class="shortcut-label">Tất cả</div>
      </div>
      <div 
        v-for="cat in categories" 
        :key="cat.id" 
        class="shortcut-item" 
        :class="{ active: selectedCategoryId === cat.id }"
        @click="changeTab(cat.id)"
      >
        <div class="shortcut-icon-wrap" :style="{ background: getCategoryIconInfo(cat.ten_danh_muc).bg }">
          <i :class="getCategoryIconInfo(cat.ten_danh_muc).icon"></i>
        </div>
        <div class="shortcut-label">{{ cat.ten_danh_muc.split(' (')[0] }}</div>
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
        <router-link to="/san-pham" class="btn-view-all">Xem tất cả →</router-link>
      </div>

      <!-- Skeleton Loading Grid -->
      <div v-if="loading" class="skeleton-grid">
        <div v-for="i in 5" :key="i" class="skeleton-card skeleton-pulse">
          <div class="skeleton-image"></div>
          <div class="skeleton-text"></div>
          <div class="skeleton-text short"></div>
          <div class="skeleton-text price"></div>
        </div>
      </div>
      
      <div v-else-if="products.length > 0">
        <div class="product-grid">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="product-card"
            @click="goToDetail(product.id)"
          >
            <!-- Badge -->
            <div class="product-badge badge-new" v-if="product.tinh_trang === 1">Đang bán</div>
            <div class="product-badge badge-sale" v-else-if="product.tinh_trang === 0">Hết hàng</div>
            <div class="product-badge badge-ltd" v-else-if="product.tinh_trang === 2">Ẩn</div>
            
            <div class="product-img-wrap">
              <img :src="getProductImage(product)" :alt="product.ten_san_pham" class="product-img" />
            </div>
            <div class="product-info">
              <div class="product-brand">{{ product.danh_muc ? product.danh_muc.ten_danh_muc : 'Sản phẩm' }}</div>
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
                <button class="btn-view" @click.stop="goToDetail(product.id)">Xem</button>
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

        <!-- View More Link to dedicated Search page -->
        <div class="view-more-container" v-if="selectedCategoryId">
          <router-link :to="'/san-pham?id_danh_muc=' + selectedCategoryId" class="btn-view-more-tabs">
            Xem tất cả {{ getCategoryName(selectedCategoryId) }} →
          </router-link>
        </div>
      </div>
      
      <div v-else class="empty-products">
        Không tìm thấy sản phẩm nào phù hợp.
      </div>
    </div>
  </div>

  <!-- FEATURED CATEGORY SLIDER (ANIME) -->
  <div class="featured-slider-section" v-if="animeProducts.length > 0">
    <div class="featured-slider-inner">
      
      <!-- Banner -->
      <div class="slider-banner">
        <div class="slider-banner-top">
          <span class="slider-banner-tag">HOT TREND</span>
          <h3 class="slider-banner-title">Mô Hình<br/><em>Anime & Figure</em></h3>
          <p class="slider-banner-desc">Bộ sưu tập mô hình nhân vật hoạt hình Anime, Figure sắc nét, chất lượng cao cực kỳ được săn đón.</p>
        </div>
        <router-link to="/san-pham?id_danh_muc=1" class="slider-banner-btn">Xem Ngay</router-link>
      </div>

      <!-- Slider Main -->
      <div class="slider-main">
        <!-- Left Arrow -->
        <button class="slider-arrow left" @click="scrollSlider('left')">
          <i class="fa-solid fa-chevron-left"></i>
        </button>

        <!-- Container -->
        <div class="slider-container" ref="animeSlider">
          <div 
            v-for="product in animeProducts" 
            :key="product.id" 
            class="product-card"
            @click="goToDetail(product.id)"
          >
            <div class="product-badge badge-new" v-if="product.tinh_trang === 1">Mới</div>
            <div class="product-img-wrap">
              <img :src="getProductImage(product)" :alt="product.ten_san_pham" class="product-img" />
            </div>
            <div class="product-info">
              <div class="product-brand">Anime & Figure</div>
              <div class="product-name truncate" :title="product.ten_san_pham">{{ product.ten_san_pham }}</div>
              <div class="product-rating">
                <span class="stars">★★★★★</span>
                <span>(5.0)</span>
              </div>
              <div class="product-footer">
                <div class="product-price-group">
                  <span class="product-price">{{ formatPrice(product.gia_co_ban) }}</span>
                </div>
                <button class="btn-view" @click.stop="goToDetail(product.id)">Xem</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Arrow -->
        <button class="slider-arrow right" @click="scrollSlider('right')">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>

    </div>
  </div>

  <!-- GUNDAM DARK SHOWCASE SECTION -->
  <div class="gundam-showcase-section" v-if="gundamProducts.length > 0">
    <div class="gundam-showcase-inner">
      <div class="section-header">
        <div class="section-title-wrap">
          <div class="section-eyebrow">Bộ sưu tập robot đỉnh cao</div>
          <h2 class="section-title">Mô Hình Lắp Ráp Gundam</h2>
        </div>
        <router-link to="/san-pham?id_danh_muc=2" class="btn-view-all">Xem tất cả →</router-link>
      </div>

      <div class="gundam-grid">
        <div 
          v-for="product in gundamProducts" 
          :key="product.id" 
          class="dark-product-card"
          @click="goToDetail(product.id)"
        >
          <div class="product-badge badge-sale">Hot Deal</div>
          <div class="product-img-wrap">
            <img :src="getProductImage(product)" :alt="product.ten_san_pham" class="product-img" />
          </div>
          <div class="product-info">
            <div class="product-brand">Gundam / Plamo</div>
            <div class="product-name truncate" :title="product.ten_san_pham">{{ product.ten_san_pham }}</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(5.0)</span>
            </div>
            <div class="product-footer">
              <div class="product-price-group">
                <span class="product-price">{{ formatPrice(product.gia_co_ban) }}</span>
              </div>
              <button class="btn-view" @click.stop="goToDetail(product.id)">Xem</button>
            </div>
          </div>
        </div>
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
            <div class="feature-title">Giao Hàng Nhanh Chóng</div>
            <div class="feature-desc">
              Hỗ trợ giao hàng trên toàn quốc. Đóng gói cẩn thận, an toàn đến tận tay bạn.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
          <div>
            <div class="feature-title">Cam Kết Chất Lượng</div>
            <div class="feature-desc">
              Đảm bảo chất lượng sản phẩm như mô tả, hoàn tiền hoặc đổi mới nếu có lỗi từ sản xuất.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-medal"></i></div>
          <div>
            <div class="feature-title">Uy Tín Hàng Đầu</div>
            <div class="feature-desc">
              Mỗi sản phẩm đều được kiểm tra kỹ lưỡng trước khi giao đến tay khách hàng.
            </div>
          </div>
        </div>
        
        <div class="feature-item">
          <div style="margin-top: 10px;" class="feature-icon"><i class="fa-solid fa-rotate-left"></i></div>
          <div>
            <div class="feature-title">Đổi Trả Dễ Dàng</div>
            <div class="feature-desc">
              Chính sách đổi trả linh hoạt lên tới 7 ngày nếu sản phẩm không đúng yêu cầu.
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
        <span class="banner-tag">Ưu Đãi Đặc Biệt</span>
        <h2 class="banner-title">Mua Sắm Ngay<br /><em>Nhận Nhiều</em><br />Ưu Đãi Lớn</h2>
        <p class="banner-desc">
          Khám phá những bộ sưu tập sản phẩm mới nhất được cập nhật liên tục mỗi tuần. Cam kết chất lượng, bảo hành chu đáo và nhiều chương trình ưu đãi độc quyền dành riêng cho bạn.
        </p>
        <button class="btn-banner" @click="$router.push('/san-pham')">Mua Sắm Ngay</button>
      </div>
      <div class="banner-visual">
        <div class="banner-deco"></div>
        <div class="banner-deco banner-deco-2"></div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-shirt"></i></span>
          <div class="banner-model-name">Thời Trang</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-mobile-button"></i></span>
          <div class="banner-model-name">Điện Tử</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji"><i class="fa-solid fa-couch"></i></span>
          <div class="banner-model-name">Đồ Gia Dụng</div>
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
      animeProducts: [],
      loadingAnimeProducts: false,
      gundamProducts: [],
      loadingGundamProducts: false,
    };
  },
  mounted() {
    this.fetchCategories();
    this.applyRouteFilters();
    this.fetchAnimeProducts();
    this.fetchGundamProducts();
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
    async fetchAnimeProducts() {
      this.loadingAnimeProducts = true;
      try {
        const res = await axios.get("/api/san-pham", {
          params: { id_danh_muc: 1, per_page: 8 }
        });
        this.animeProducts = res.data?.data || [];
      } catch (err) {
        console.error("Lỗi khi tải sản phẩm Anime:", err);
      } finally {
        this.loadingAnimeProducts = false;
      }
    },
    async fetchGundamProducts() {
      this.loadingGundamProducts = true;
      try {
        const res = await axios.get("/api/san-pham", {
          params: { id_danh_muc: 2, per_page: 5 }
        });
        this.gundamProducts = res.data?.data || [];
      } catch (err) {
        console.error("Lỗi khi tải sản phẩm Gundam:", err);
      } finally {
        this.loadingGundamProducts = false;
      }
    },
    scrollSlider(direction) {
      const container = this.$refs.animeSlider;
      if (container) {
        const scrollAmount = 320;
        if (direction === 'left') {
          container.scrollLeft -= scrollAmount;
        } else {
          container.scrollLeft += scrollAmount;
        }
      }
    },
    changeTab(id) {
      this.selectedCategoryId = id;
      this.fetchProducts(1);
    },
    getCategoryIconInfo(catName) {
      const name = catName.toLowerCase();
      if (name.includes('anime') || name.includes('figure')) {
        return { icon: 'fa-solid fa-mask', bg: 'linear-gradient(135deg, #ff9f43, #ff5252)' };
      }
      if (name.includes('gundam') || name.includes('lắp ráp') || name.includes('plamo')) {
        return { icon: 'fa-solid fa-robot', bg: 'linear-gradient(135deg, #a55eea, #fd79a8)' };
      }
      if (name.includes('xe') || name.includes('quân sự')) {
        return { icon: 'fa-solid fa-plane-up', bg: 'linear-gradient(135deg, #0984e3, #00cec9)' };
      }
      if (name.includes('in 3d') || name.includes('3d')) {
        return { icon: 'fa-solid fa-cubes', bg: 'linear-gradient(135deg, #20bf6b, #05c46b)' };
      }
      if (name.includes('kiến trúc') || name.includes('diorama') || name.includes('sa bàn')) {
        return { icon: 'fa-solid fa-monument', bg: 'linear-gradient(135deg, #f1c40f, #e67e22)' };
      }
      if (name.includes('dụng cụ') && name.includes('cắt')) {
        return { icon: 'fa-solid fa-screwdriver-wrench', bg: 'linear-gradient(135deg, #3f51b5, #9c27b0)' };
      }
      if (name.includes('sơn') || name.includes('hóa chất')) {
        return { icon: 'fa-solid fa-palette', bg: 'linear-gradient(135deg, #e91e63, #ff6090)' };
      }
      return { icon: 'fa-solid fa-box-open', bg: 'linear-gradient(135deg, #607d8b, #cfd8dc)' };
    },
    getCategoryName(id) {
      const cat = this.categories.find(x => x.id === id);
      return cat ? cat.ten_danh_muc : 'sản phẩm';
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

