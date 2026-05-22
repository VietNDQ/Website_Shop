<template>
  <div class="blog-page-container">
    <!-- Hero Banner -->
    <div class="blog-hero">
      <div class="blog-hero-overlay"></div>
      <div class="blog-hero-content">
        <h1>TIN TỨC & CHIA SẺ</h1>
        <p>Cập nhật tin tức mới nhất, mẹo mua sắm hữu ích và những bài viết chia sẻ kinh nghiệm bổ ích.</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="blog-content-wrap">
      <!-- Toolbar: Search + Category Tabs -->
      <div class="blog-toolbar">
        <div class="cat-tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.value" 
            class="tab-btn" 
            :class="{ active: activeTab === tab.value }"
            @click="selectTab(tab.value)"
          >
            {{ tab.name }}
          </button>
        </div>

        <div class="blog-search-wrap">
          <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tìm kiếm bài viết..." 
            @input="debouncedSearch"
          />
        </div>
      </div>

      <!-- Loading skeleton states -->
      <div v-if="isLoading" class="blog-grid">
        <div class="blog-card skeleton-card" v-for="n in 6" :key="n">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-badge"></div>
            <div class="skeleton-title"></div>
            <div class="skeleton-title short"></div>
            <div class="skeleton-text"></div>
            <div class="skeleton-footer"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="posts.length === 0" class="blog-empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <h3>Chưa có bài viết nào</h3>
        <p>Không tìm thấy bài viết nào phù hợp với từ khóa hoặc bộ lọc đã chọn.</p>
        <button class="btn-reset" @click="resetFilters">Hiển thị tất cả</button>
      </div>

      <!-- Blog Grid -->
      <div v-else>
        <div class="blog-grid">
          <div class="blog-card" v-for="post in posts" :key="post.id">
            <div class="blog-card-img-wrap">
              <router-link :to="'/blog/' + post.slug">
                <img 
                  v-if="post.anh_dai_dien" 
                  :src="post.anh_dai_dien.startsWith('http') ? post.anh_dai_dien : '' + post.anh_dai_dien" 
                  :alt="post.tieu_de" 
                  class="blog-card-img"
                />
                <div v-else class="blog-card-placeholder">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
              </router-link>
              <span class="blog-card-badge" :class="catClassMap[post.loai]">
                {{ catNameMap[post.loai] }}
              </span>
            </div>

            <div class="blog-card-body">
              <h2 class="blog-card-title">
                <router-link :to="'/blog/' + post.slug" :title="post.tieu_de">
                  {{ post.tieu_de }}
                </router-link>
              </h2>
              
              <p class="blog-card-excerpt">
                {{ post.tom_tat || 'Xem chi tiết bài viết để tìm hiểu thêm...' }}
              </p>

              <div class="blog-card-footer">
                <div class="blog-card-author">
                  <div class="author-avatar">{{ (post.nguoi_dang?.ho_ten || 'A').substring(0,1).toUpperCase() }}</div>
                  <span class="author-name">{{ post.nguoi_dang?.ho_ten || 'Hệ thống' }}</span>
                </div>
                <div class="blog-card-meta">
                  <span class="meta-item" title="Lượt xem">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{ post.luot_xem }}
                  </span>
                  <span class="meta-item">
                    {{ formatDate(post.tao_luc) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="blog-pagination" v-if="pagination.last_page > 1">
          <button 
            class="pg-btn" 
            :disabled="pagination.current_page === 1" 
            @click="changePage(pagination.current_page - 1)"
          >
            &lt; Trước
          </button>
          
          <button 
            v-for="p in pagination.last_page" 
            :key="p" 
            class="pg-btn" 
            :class="{ active: pagination.current_page === p }"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
          
          <button 
            class="pg-btn" 
            :disabled="pagination.current_page === pagination.last_page" 
            @click="changePage(pagination.current_page + 1)"
          >
            Sau &gt;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BlogList',
  data() {
    return {
      posts: [],
      isLoading: false,
      searchQuery: '',
      activeTab: '',
      
      tabs: [
        { name: 'Tất cả bài viết', value: '' },
        { name: 'Tin tức & Sự kiện', value: 'tin_tuc' },
        { name: 'Hướng dẫn sưu tầm', value: 'huong_dan' },
        { name: 'Đánh giá / Review', value: 'danh_gia' }
      ],

      catNameMap: {
        tin_tuc: 'Tin tức & Sự kiện',
        huong_dan: 'Hướng dẫn sưu tầm',
        danh_gia: 'Đánh giá / Review'
      },

      catClassMap: {
        tin_tuc: 'c-news',
        huong_dan: 'c-guide',
        danh_gia: 'c-review'
      },

      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 9,
        total: 0
      },

      debounceTimer: null
    };
  },
  mounted() {
    this.fetchPosts();
  },
  methods: {
    debouncedSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.pagination.current_page = 1;
        this.fetchPosts();
      }, 400);
    },
    async fetchPosts() {
      this.isLoading = true;
      try {
        const params = {
          page: this.pagination.current_page,
          limit: this.pagination.per_page,
          q: this.searchQuery,
          loai: this.activeTab
        };
        const res = await axios.get('/api/blog', { params });
        if (res.data.status) {
          const paginated = res.data.data;
          this.posts = paginated.data || [];
          this.pagination.current_page = paginated.current_page;
          this.pagination.last_page = paginated.last_page;
          this.pagination.total = paginated.total;
        }
      } catch (err) {
        console.error('Lỗi khi tải blog:', err);
      } finally {
        this.isLoading = false;
      }
    },
    selectTab(tabValue) {
      this.activeTab = tabValue;
      this.pagination.current_page = 1;
      this.fetchPosts();
    },
    changePage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.fetchPosts();
    },
    resetFilters() {
      this.searchQuery = '';
      this.activeTab = '';
      this.pagination.current_page = 1;
      this.fetchPosts();
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      if (isNaN(date.getTime())) return dateStr;
      return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      }).format(date);
    }
  }
};
</script>

<style scoped>
.blog-page-container {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: 'DM Sans', sans-serif;
  color: #1e293b;
}

/* Hero Banner */
.blog-hero {
  position: relative;
  background: linear-gradient(135deg, #1e1b4b 0%, #311018 100%);
  padding: 65px 20px;
  text-align: center;
  color: #ffffff;
  overflow: hidden;
}

.blog-hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: radial-gradient(circle at 30% 30%, rgba(215, 0, 24, 0.15), transparent 60%);
  z-index: 1;
}

.blog-hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.blog-hero-content h1 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 46px;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.blog-hero-content p {
  font-size: 16px;
  color: #cbd5e1;
  max-width: 620px;
  margin: 0 auto;
}

/* Content Wrap */
.blog-content-wrap {
  max-width: 1200px;
  margin: -40px auto 70px;
  padding: 0 20px;
  position: relative;
  z-index: 10;
}

/* Toolbar */
.blog-toolbar {
  background: #ffffff;
  border-radius: 16px;
  padding: 16px 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 30px;
}

.cat-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.tab-btn {
  padding: 8px 16px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: transparent;
  color: #64748b;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.tab-btn:hover {
  border-color: #cbd5e1;
  color: #0f172a;
}

.tab-btn.active {
  background-color: #D70018;
  border-color: #D70018;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.15);
}

.blog-search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 0 14px;
  height: 38px;
  width: 280px;
  max-width: 100%;
  color: #94a3b8;
  transition: all 0.2s ease;
}

.blog-search-wrap:focus-within {
  border-color: #D70018;
  color: #475569;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.06);
}

.blog-search-wrap input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  color: #0f172a;
  width: 100%;
  font-family: inherit;
}

.blog-search-wrap input::placeholder {
  color: #94a3b8;
}

/* Blog Grid */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
}

@media (max-width: 992px) {
  .blog-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .blog-grid {
    grid-template-columns: 1fr;
  }
}

/* Blog Card */
.blog-card {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.blog-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  border-color: rgba(215, 0, 24, 0.15);
}

.blog-card-img-wrap {
  position: relative;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
  background: #f1f5f9;
  overflow: hidden;
}

.blog-card-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.blog-card:hover .blog-card-img {
  transform: scale(1.06);
}

.blog-card-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e1;
}

.blog-card-badge {
  position: absolute;
  top: 14px;
  left: 14px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
  z-index: 1;
}

.c-news {
  background-color: #D70018;
  color: #ffffff;
}

.c-guide {
  background-color: #0284c7;
  color: #ffffff;
}

.c-review {
  background-color: #8b5cf6;
  color: #ffffff;
}

.blog-card-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.blog-card-title {
  font-size: 17px;
  font-weight: 700;
  line-height: 1.4;
  margin: 0 0 12px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 48px;
}

.blog-card-title a {
  color: #0f172a;
  text-decoration: none;
  transition: color 0.15s ease;
}

.blog-card-title a:hover {
  color: #D70018;
}

.blog-card-excerpt {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.6;
  margin: 0 0 20px 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

.blog-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  font-size: 12px;
}

.blog-card-author {
  display: flex;
  align-items: center;
  gap: 8px;
}

.author-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #475569;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  border: 1px solid #e2e8f0;
}

.author-name {
  font-weight: 600;
  color: #475569;
}

.blog-card-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #94a3b8;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Pagination */
.blog-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  margin-top: 40px;
}

.pg-btn {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: inherit;
}

.pg-btn:hover:not(:disabled) {
  border-color: #D70018;
  color: #D70018;
}

.pg-btn.active {
  background-color: #D70018;
  border-color: #D70018;
  color: #ffffff;
}

.pg-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Empty State */
.blog-empty-state {
  background: #ffffff;
  border-radius: 16px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  color: #64748b;
}

.blog-empty-state svg {
  color: #cbd5e1;
  margin-bottom: 16px;
}

.blog-empty-state h3 {
  font-size: 18px;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.blog-empty-state p {
  font-size: 14px;
  margin: 0 0 20px 0;
}

.btn-reset {
  padding: 10px 24px;
  background: #0f172a;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.btn-reset:hover {
  background: #D70018;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}

/* Skeletons */
.skeleton-card {
  pointer-events: none;
}

.skeleton-img {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  padding-top: 56.25%;
}

.skeleton-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-badge {
  width: 80px;
  height: 18px;
  border-radius: 4px;
  background: #f1f5f9;
}

.skeleton-title {
  height: 20px;
  background: #f1f5f9;
  border-radius: 4px;
  width: 100%;
}

.skeleton-title.short {
  width: 60%;
}

.skeleton-text {
  height: 48px;
  background: #f1f5f9;
  border-radius: 4px;
}

.skeleton-footer {
  height: 24px;
  border-top: 1px solid #f1f5f9;
  margin-top: 10px;
  background: #f1f5f9;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
