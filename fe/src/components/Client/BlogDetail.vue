<template>
  <div class="blog-detail-container">
    <!-- Breadcrumbs -->
    <div class="blog-breadcrumbs">
      <div class="breadcrumbs-inner">
        <router-link to="/">Trang chủ</router-link>
        <span class="separator">/</span>
        <router-link to="/blog">Blog & Tin tức</router-link>
        <span class="separator">/</span>
        <span class="current">{{ post?.tieu_de || 'Chi tiết bài viết' }}</span>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="blog-detail-content-wrap">
      <!-- Loading state -->
      <div v-if="isLoading" class="blog-detail-loading">
        <div class="spinner"></div>
        <p>Đang tải nội dung bài viết...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="blog-detail-error">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h3>Không tìm thấy bài viết</h3>
        <p>{{ errorMessage }}</p>
        <router-link to="/blog" class="btn-back">Quay lại danh sách tin tức</router-link>
      </div>

      <!-- Article content -->
      <div v-else-if="post" class="blog-detail-layout">
        <!-- Left Side: Article Details -->
        <article class="article-card">
          <!-- Category + View Meta -->
          <div class="article-header-meta">
            <span class="article-cat-badge" :class="catClassMap[post.loai]">
              {{ catNameMap[post.loai] }}
            </span>
            <div class="article-meta-right">
              <span class="meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                {{ post.luot_xem }} lượt xem
              </span>
              <span class="meta-item-sep">•</span>
              <span class="meta-item">{{ formatDate(post.tao_luc) }}</span>
            </div>
          </div>

          <!-- Title -->
          <h1 class="article-title">{{ post.tieu_de }}</h1>

          <!-- Author Info -->
          <div class="article-author-bar">
            <div class="author-avatar">{{ (post.nguoi_dang?.ho_ten || 'A').substring(0,1).toUpperCase() }}</div>
            <div class="author-info">
              <span class="author-name">{{ post.nguoi_dang?.ho_ten || 'Hệ thống' }}</span>
              <span class="author-title">Biên tập viên BALAB</span>
            </div>
          </div>

          <!-- Cover Image -->
          <div v-if="post.anh_dai_dien" class="article-cover-wrap">
            <img :src="post.anh_dai_dien.startsWith('http') ? post.anh_dai_dien : '' + post.anh_dai_dien" :alt="post.tieu_de" />
          </div>

          <!-- Short Excerpt / Quote -->
          <div v-if="post.tom_tat" class="article-excerpt">
            {{ post.tom_tat }}
          </div>

          <!-- Content Body -->
          <div class="article-content-body" v-html="post.noi_dung"></div>
        </article>

        <!-- Right Side: Sidebar (Related posts) -->
        <aside class="blog-sidebar">
          <div class="sidebar-widget">
            <h3 class="widget-title">Bài viết liên quan</h3>
            
            <div v-if="relatedPosts.length === 0" class="no-related-posts">
              Không có bài viết liên quan nào khác.
            </div>
            
            <div v-else class="related-posts-list">
              <div class="related-post-card" v-for="rp in relatedPosts" :key="rp.id">
                <router-link :to="'/blog/' + rp.slug" class="rp-img-link">
                  <img 
                    v-if="rp.anh_dai_dien" 
                    :src="rp.anh_dai_dien.startsWith('http') ? rp.anh_dai_dien : '' + rp.anh_dai_dien" 
                    :alt="rp.tieu_de"
                  />
                  <div v-else class="rp-no-img">📷</div>
                </router-link>
                <div class="rp-info">
                  <h4>
                    <router-link :to="'/blog/' + rp.slug" :title="rp.tieu_de">
                      {{ rp.tieu_de }}
                    </router-link>
                  </h4>
                  <div class="rp-meta">
                    <span>{{ formatDate(rp.tao_luc) }}</span>
                    <span>•</span>
                    <span>{{ rp.luot_xem }} xem</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Static Advertisement / Promotion Widget -->
          <div class="sidebar-widget promo-widget">
            <h4>SẢN PHẨM CHẤT LƯỢNG CAO</h4>
            <p>Khám phá bộ sưu tập sản phẩm phong phú, xu hướng mới và chất lượng chính hãng tại BALAB Store.</p>
            <router-link to="/san-pham" class="btn-shop-now">Mua sắm ngay</router-link>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BlogDetail',
  data() {
    return {
      post: null,
      relatedPosts: [],
      isLoading: false,
      error: false,
      errorMessage: '',
      
      catNameMap: {
        tin_tuc: 'Tin tức & Sự kiện',
        huong_dan: 'Hướng dẫn sưu tầm',
        danh_gia: 'Đánh giá / Review'
      },

      catClassMap: {
        tin_tuc: 'c-news',
        huong_dan: 'c-guide',
        danh_gia: 'c-review'
      }
    };
  },
  watch: {
    // Watch for route changes to reload content (e.g. clicking related posts)
    '$route.params.id': {
      handler(newId) {
        if (newId) {
          this.fetchPostDetail(newId);
        }
      },
      immediate: true
    }
  },
  methods: {
    async fetchPostDetail(idOrSlug) {
      this.isLoading = true;
      this.error = false;
      this.errorMessage = '';
      try {
        const res = await axios.get(`/api/blog/${idOrSlug}`);
        if (res.data.status) {
          this.post = res.data.post;
          this.relatedPosts = res.data.related || [];
        } else {
          this.error = true;
          this.errorMessage = res.data.message || 'Không thể tìm thấy bài viết.';
        }
      } catch (err) {
        this.error = true;
        this.errorMessage = err.response?.data?.message || 'Lỗi hệ thống hoặc liên kết không hợp lệ.';
        console.error(err);
      } finally {
        this.isLoading = false;
      }
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
.blog-detail-container {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: 'DM Sans', sans-serif;
  color: #1e293b;
}

/* Breadcrumbs */
.blog-breadcrumbs {
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 14px 20px;
}

.breadcrumbs-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  flex-wrap: wrap;
}

.breadcrumbs-inner a {
  color: #64748b;
  text-decoration: none;
  transition: color 0.15s ease;
}

.breadcrumbs-inner a:hover {
  color: #D70018;
}

.separator {
  color: #cbd5e1;
}

.current {
  color: #0f172a;
  font-weight: 500;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Layout Wrapper */
.blog-detail-content-wrap {
  max-width: 1200px;
  margin: 30px auto 70px;
  padding: 0 20px;
}

.blog-detail-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 30px;
  align-items: start;
}

@media (max-width: 992px) {
  .blog-detail-layout {
    grid-template-columns: 1fr;
  }
}

/* Loading state */
.blog-detail-loading {
  text-align: center;
  padding: 80px 20px;
  color: #64748b;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 4px solid rgba(215, 0, 24, 0.1);
  border-top-color: #D70018;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error State */
.blog-detail-error {
  background: #ffffff;
  border-radius: 16px;
  padding: 60px 20px;
  text-align: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  max-width: 600px;
  margin: 40px auto;
}

.blog-detail-error svg {
  margin-bottom: 16px;
}

.blog-detail-error h3 {
  font-size: 18px;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.blog-detail-error p {
  font-size: 14px;
  color: #64748b;
  margin: 0 0 24px 0;
}

.btn-back {
  display: inline-block;
  padding: 10px 24px;
  background-color: #D70018;
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-back:hover {
  background-color: #b0001a;
  box-shadow: 0 4px 12px rgba(215,0,24,0.2);
}

/* Article Card */
.article-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  border: 1px solid #e2e8f0;
}

@media (max-width: 576px) {
  .article-card {
    padding: 20px;
  }
}

.article-header-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}

.article-cat-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
}

.c-news {
  background-color: rgba(215, 0, 24, 0.1);
  color: #D70018;
}

.c-guide {
  background-color: rgba(2, 132, 199, 0.1);
  color: #0284c7;
}

.c-review {
  background-color: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}

.article-meta-right {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.meta-item-sep {
  opacity: 0.5;
}

.article-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 34px;
  font-weight: 900;
  line-height: 1.25;
  color: #0f172a;
  margin: 0 0 20px 0;
  text-transform: uppercase;
}

@media (max-width: 576px) {
  .article-title {
    font-size: 26px;
  }
}

.article-author-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 30px;
}

.author-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #475569;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  border: 1px solid #e2e8f0;
}

.author-info {
  display: flex;
  flex-direction: column;
}

.author-name {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}

.author-title {
  font-size: 11.5px;
  color: #94a3b8;
}

.article-cover-wrap {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 30px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.04);
}

.article-cover-wrap img {
  width: 100%;
  max-height: 480px;
  object-fit: cover;
  display: block;
}

.article-excerpt {
  font-size: 16px;
  font-weight: 500;
  line-height: 1.6;
  color: #475569;
  padding: 18px 24px;
  background-color: #f8fafc;
  border-left: 4px solid #D70018;
  border-radius: 4px;
  margin-bottom: 30px;
}

/* Article Body Content styling */
.article-content-body {
  font-size: 15px;
  line-height: 1.8;
  color: #334155;
}

.article-content-body :deep(p) {
  margin-bottom: 1.5em;
}

.article-content-body :deep(h2),
.article-content-body :deep(h3),
.article-content-body :deep(h4) {
  font-family: 'Barlow Condensed', sans-serif;
  color: #0f172a;
  font-weight: 800;
  margin-top: 36px;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.article-content-body :deep(h2) { font-size: 24px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
.article-content-body :deep(h3) { font-size: 20px; }
.article-content-body :deep(h4) { font-size: 17px; }

.article-content-body :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 10px;
  margin: 24px auto;
  display: block;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}

.article-content-body :deep(ul),
.article-content-body :deep(ol) {
  margin: 0 0 1.5em 20px;
}

.article-content-body :deep(li) {
  margin-bottom: 0.5em;
}

.article-content-body :deep(blockquote) {
  background: #f8fafc;
  border-left: 4px solid #cbd5e1;
  padding: 14px 20px;
  margin: 24px 0;
  font-style: italic;
  color: #475569;
  border-radius: 4px;
}

/* Sidebar Styling */
.blog-sidebar {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.sidebar-widget {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  border: 1px solid #e2e8f0;
}

.widget-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  margin: 0 0 20px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid #f1f5f9;
  position: relative;
}

.widget-title::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 40px;
  height: 2px;
  background-color: #D70018;
}

.no-related-posts {
  color: #94a3b8;
  font-size: 13px;
  text-align: center;
  padding: 10px 0;
}

.related-posts-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.related-post-card {
  display: flex;
  gap: 12px;
  align-items: center;
}

.rp-img-link {
  width: 76px;
  height: 52px;
  border-radius: 6px;
  overflow: hidden;
  background-color: #f1f5f9;
  flex-shrink: 0;
  border: 1px solid #e2e8f0;
  display: block;
}

.rp-img-link img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.rp-img-link:hover img {
  transform: scale(1.08);
}

.rp-no-img {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #cbd5e1;
}

.rp-info {
  flex: 1;
}

.rp-info h4 {
  font-size: 13.5px;
  font-weight: 600;
  line-height: 1.35;
  margin: 0 0 4px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.rp-info h4 a {
  color: #1e293b;
  text-decoration: none;
  transition: color 0.15s ease;
}

.rp-info h4 a:hover {
  color: #D70018;
}

.rp-meta {
  display: flex;
  gap: 6px;
  font-size: 11px;
  color: #94a3b8;
}

/* Promo widget styling */
.promo-widget {
  background: linear-gradient(135deg, #1e1b4b 0%, #311018 100%);
  color: #ffffff;
  border: none;
  text-align: center;
  padding: 30px 24px;
}

.promo-widget h4 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 20px;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin: 0 0 10px 0;
}

.promo-widget p {
  font-size: 13px;
  color: #cbd5e1;
  line-height: 1.5;
  margin: 0 0 20px 0;
}

.btn-shop-now {
  display: inline-block;
  width: 100%;
  padding: 10px;
  background-color: #D70018;
  color: #ffffff;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
  text-transform: uppercase;
}

.btn-shop-now:hover {
  background-color: #b0001a;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
  transform: translateY(-1px);
}
</style>
