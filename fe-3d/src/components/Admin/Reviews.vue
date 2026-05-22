<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý đánh giá</h1>
        <p class="page-sub">Xem, lọc, kiểm duyệt và phản hồi ý kiến đánh giá từ khách hàng</p>
      </div>
    </div>

    <!-- Stats Dashboard Dashboard -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon reviews-icon">
          <i class="fa-solid fa-comments"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Tổng số đánh giá</p>
          <h3 class="stat-value">{{ stats.total }}</h3>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon star-icon">
          <i class="fa-solid fa-star"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Điểm trung bình</p>
          <h3 class="stat-value">
            {{ stats.average }} <span class="star-small">★</span>
          </h3>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon pending-icon">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Đang chờ duyệt</p>
          <h3 class="stat-value text-amber">{{ stats.pending }}</h3>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon love-icon">
          <i class="fa-solid fa-heart"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Tỷ lệ hài lòng</p>
          <h3 class="stat-value text-green">{{ stats.satisfactionRate }}%</h3>
        </div>
      </div>
    </div>

    <!-- Filter and Search Toolbar -->
    <div class="card toolbar-card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input 
            type="text" 
            v-model="filters.search" 
            @input="debounceSearch" 
            placeholder="Tìm theo sản phẩm, khách hàng..." 
          />
        </div>
        <div class="toolbar-right">
          <!-- Stars Filter -->
          <div class="filter-select-wrap">
            <div class="custom-select" :class="{ open: starsDropdownOpen }" ref="starsSelect">
              <div class="custom-select-trigger" @click="toggleStarsDropdown">
                <span v-if="filters.sao === ''">Tất cả sao</span>
                <span v-else class="selected-stars-display">
                  <span class="stars-gold">{{ getStarsString(filters.sao) }}</span>
                  <span class="stars-text">({{ filters.sao }} sao)</span>
                </span>
                <i class="fa-solid fa-chevron-down trigger-arrow"></i>
              </div>
              <div class="custom-select-options" v-if="starsDropdownOpen">
                <div class="custom-option" @click="selectStars('')" :class="{ active: filters.sao === '' }">
                  Tất cả sao
                </div>
                <div 
                  v-for="s in [5, 4, 3, 2, 1]" 
                  :key="s" 
                  class="custom-option" 
                  @click="selectStars(s)"
                  :class="{ active: filters.sao === String(s) || filters.sao === s }"
                >
                  <span class="stars-gold">{{ getStarsString(s) }}</span>
                  <span class="stars-text">({{ s }} sao)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Filter -->
          <div class="filter-select-wrap">
            <span class="select-label">Trạng thái:</span>
            <select class="sel" v-model="filters.trang_thai" @change="fetchReviews(1)">
              <option value="">Tất cả trạng thái</option>
              <option value="hien_thi">Đã hiển thị</option>
              <option value="cho_duyet">Chờ duyệt</option>
              <option value="an">Đã ẩn</option>
            </select>
          </div>

          <!-- Reset Button -->
          <button class="btn-reset" @click="resetFilters" title="Làm mới bộ lọc">
            <i class="fa-solid fa-arrow-rotate-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Reviews Table Card -->
    <div class="card table-card table-wrap-relative">
      <div v-if="initialLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải danh sách đánh giá...</p>
      </div>

      <div v-else>
        <!-- Overlay spinner when updating -->
        <div v-if="updating" class="table-updating-overlay">
          <div class="spinner-sm"></div>
        </div>

        <div class="table-responsive" :class="{ 'table-updating': updating }">
          <table class="data-table">
          <thead>
            <tr>
              <th>Khách hàng</th>
              <th>Sản phẩm & Phân loại</th>
              <th class="text-center">Đánh giá</th>
              <th>Nội dung nhận xét & Phản hồi</th>
              <th>Thời gian</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in reviews" :key="r.id" class="review-row">
              <!-- Customer Column -->
              <td class="customer-col">
                <div class="customer-cell">
                  <div class="cus-avatar" :style="{ background: getRandomAvatarBg(r.customer) }">
                    {{ r.customer ? r.customer.charAt(0).toUpperCase() : '?' }}
                  </div>
                  <div class="cus-details">
                    <p class="cus-name">{{ r.customer }}</p>
                    <span class="role-badge">Khách mua hàng</span>
                  </div>
                </div>
              </td>

              <!-- Product Column -->
              <td class="product-col">
                <p class="product-name">{{ r.product }}</p>
                <div class="variant-badge" v-if="r.variant">
                  <i class="fa-solid fa-tags"></i> {{ r.variant }}
                </div>
              </td>

              <!-- Stars Column -->
              <td class="stars-col text-center">
                <div class="stars-wrap">
                  <span 
                    v-for="i in 5" 
                    :key="i" 
                    class="star-icon" 
                    :class="{ filled: i <= r.stars }"
                  >★</span>
                </div>
              </td>

              <!-- Content Column -->
              <td class="content-col">
                <div class="review-content">
                  <p class="comment-text" :class="{ 'no-comment': !r.content }">
                    {{ r.content || 'Không có bình luận văn bản.' }}
                  </p>
                  
                  <!-- Review Images -->
                  <div v-if="r.images && r.images.length > 0" class="review-images">
                    <div 
                      v-for="(img, idx) in r.images" 
                      :key="idx" 
                      class="img-container"
                      @click="openLightbox(r.images, idx)"
                    >
                      <img :src="img" alt="Review Photo" class="review-thumb" />
                      <div class="img-overlay">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                      </div>
                    </div>
                  </div>

                  <!-- Shop Reply Box -->
                  <div v-if="r.reply" class="shop-reply-box">
                    <div class="reply-header">
                      <span class="reply-avatar"><i class="fa-solid fa-store"></i></span>
                      <strong>Phản hồi của shop:</strong>
                    </div>
                    <p class="reply-text">{{ r.reply }}</p>
                  </div>
                </div>
              </td>

              <!-- Date Column -->
              <td class="date-col">
                <div class="date-time">
                  <span class="date-str">{{ r.date }}</span>
                </div>
              </td>

              <!-- Status Column -->
              <td class="status-col text-center">
                <span class="status-pill" :class="'s-' + r.status">
                  {{ r.statusLabel }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="actions-col text-center">
                <div class="action-buttons-wrap">
                  <!-- Reply Action -->
                  <button 
                    class="btn-action btn-reply" 
                    @click="openReplyModal(r)"
                    title="Phản hồi đánh giá"
                  >
                    <i class="fa-solid fa-reply"></i>
                  </button>

                  <!-- Visibility Actions -->
                  <button 
                    v-if="r.status !== 'hien_thi'" 
                    class="btn-action btn-show" 
                    @click="updateStatus(r.id, 'hien_thi')"
                    title="Duyệt hiển thị công khai"
                  >
                    <i class="fa-solid fa-eye-slash"></i>
                  </button>
                  
                  <button 
                    v-if="r.status === 'hien_thi'" 
                    class="btn-action btn-hide" 
                    @click="updateStatus(r.id, 'an')"
                    title="Ẩn đánh giá này"
                  >
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="reviews.length === 0">
              <td colspan="7" class="empty-table-state">
                <i class="fa-solid fa-box-open empty-icon"></i>
                <p>Không tìm thấy đánh giá nào phù hợp với bộ lọc.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      </div>

      <!-- Pagination Footer -->
      <div class="table-footer" v-if="pagination.last_page > 1">
        <div class="pagination-info">
          Hiển thị trang {{ pagination.current_page }} trên tổng số {{ pagination.last_page }} trang
        </div>
        <div class="pagination-buttons">
          <button 
            class="pg-btn" 
            :disabled="pagination.current_page === 1" 
            @click="fetchReviews(pagination.current_page - 1)"
          >
            <i class="fa-solid fa-chevron-left"></i> Trước
          </button>
          
          <button 
            v-for="page in visiblePages" 
            :key="page" 
            class="pg-btn num-btn" 
            :class="{ active: page === pagination.current_page }"
            @click="fetchReviews(page)"
          >
            {{ page }}
          </button>

          <button 
            class="pg-btn" 
            :disabled="pagination.current_page === pagination.last_page" 
            @click="fetchReviews(pagination.current_page + 1)"
          >
            Sau <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Reply Dialog Modal -->
    <div class="modal-overlay" v-if="showReplyModal" @click.self="closeReplyModal">
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <i class="fa-solid fa-reply title-icon"></i>
            <h2>Phản hồi đánh giá khách hàng</h2>
          </div>
          <button class="modal-close" @click="closeReplyModal">✕</button>
        </div>
        
        <div class="modal-body" v-if="selectedReview">
          <!-- Review Detail Card -->
          <div class="review-detail-card">
            <div class="rd-header">
              <span class="rd-customer">{{ selectedReview.customer }}</span>
              <div class="rd-stars">
                <span v-for="i in 5" :key="i" class="star" :class="{ active: i <= selectedReview.stars }">★</span>
              </div>
            </div>
            <p class="rd-product"><strong>Sản phẩm:</strong> {{ selectedReview.product }}</p>
            <p class="rd-comment">"{{ selectedReview.content || '(Không có bình luận văn bản)' }}"</p>
          </div>

          <!-- Form Control -->
          <div class="form-group">
            <label class="form-label">Nội dung phản hồi từ Cửa hàng <span class="required">*</span></label>
            <textarea 
              v-model="replyText" 
              rows="5" 
              placeholder="Cảm ơn khách hàng và phản hồi lịch sự..." 
              class="form-textarea"
              maxlength="1000"
            ></textarea>
            <div class="char-count">{{ replyText.length }}/1000 ký tự</div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="closeReplyModal" :disabled="submitting">Hủy bỏ</button>
          <button 
            class="btn-submit" 
            @click="submitReply" 
            :disabled="submitting || !replyText.trim()"
          >
            <span v-if="submitting" class="mini-spinner"></span>
            {{ submitting ? 'Đang gửi...' : 'Gửi phản hồi' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div class="lightbox-overlay" v-if="lightbox.open" @click="closeLightbox">
      <button class="lightbox-btn lb-close" @click.stop="closeLightbox">✕</button>
      <button 
        class="lightbox-btn lb-prev" 
        v-if="lightbox.images.length > 1" 
        @click.stop="prevLightboxImg"
      >
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      
      <div class="lightbox-content" @click.stop>
        <img :src="lightbox.images[lightbox.activeIndex]" alt="Enlarged review photo" class="lightbox-img" />
        <div class="lightbox-indicator">
          Ảnh {{ lightbox.activeIndex + 1 }} / {{ lightbox.images.length }}
        </div>
      </div>

      <button 
        class="lightbox-btn lb-next" 
        v-if="lightbox.images.length > 1" 
        @click.stop="nextLightboxImg"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReviewsManagement',
  data() {
    return {
      initialLoading: false,
      updating: false,
      submitting: false,
      reviews: [],
      stats: {
        total: 0,
        average: 0,
        pending: 0,
        satisfactionRate: 100,
      },
      filters: {
        search: '',
        sao: '',
        trang_thai: '',
      },
      pagination: {
        current_page: 1,
        last_page: 1,
      },
      showReplyModal: false,
      selectedReview: null,
      replyText: '',
      searchTimeout: null,
       starsDropdownOpen: false,
      // Lightbox properties
      lightbox: {
        open: false,
        images: [],
        activeIndex: 0,
      },
      
      // Avatar color palette
      avatarBgColors: [
        'linear-gradient(135deg, #f43f5e, #fb7185)',
        'linear-gradient(135deg, #3b82f6, #60a5fa)',
        'linear-gradient(135deg, #10b981, #34d399)',
        'linear-gradient(135deg, #f59e0b, #fbbf24)',
        'linear-gradient(135deg, #8b5cf6, #a78bfa)',
        'linear-gradient(135deg, #ec4899, #f472b6)',
        'linear-gradient(135deg, #06b6d4, #22d3ee)',
        'linear-gradient(135deg, #14b8a6, #2dd4bf)',
      ]
    };
  },
  computed: {
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.pagination.current_page - 2);
      const end = Math.min(this.pagination.last_page, this.pagination.current_page + 2);
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  mounted() {
    this.fetchReviews(1, true);
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleOutsideClick);
  },
  methods: {
    getAdminConfig() {
      const token = localStorage.getItem('token_admin');
      return {
        headers: {
          Authorization: `Bearer ${token}`
        }
      };
    },
    debounceSearch() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchReviews(1);
      }, 400);
    },
    resetFilters() {
      this.filters = {
        search: '',
        sao: '',
        trang_thai: '',
      };
      this.starsDropdownOpen = false;
      this.fetchReviews(1);
    },
    toggleStarsDropdown() {
      this.starsDropdownOpen = !this.starsDropdownOpen;
    },
    closeStarsDropdown() {
      this.starsDropdownOpen = false;
    },
    selectStars(sao) {
      this.filters.sao = sao;
      this.starsDropdownOpen = false;
      this.fetchReviews(1);
    },
    getStarsString(sao) {
      const count = Number(sao);
      if (!count || isNaN(count)) return '';
      return '★'.repeat(count) + '☆'.repeat(5 - count);
    },
    handleOutsideClick(e) {
      if (this.starsDropdownOpen && this.$refs.starsSelect && !this.$refs.starsSelect.contains(e.target)) {
        this.starsDropdownOpen = false;
      }
    },
    async fetchReviews(page = 1, isInitial = false) {
      if (isInitial) {
        this.initialLoading = true;
      } else {
        this.updating = true;
      }
      try {
        const params = {
          page: page,
          search: this.filters.search,
          sao: this.filters.sao,
          trang_thai: this.filters.trang_thai
        };
        const response = await axios.get('/api/quan-ly/danh-gia', {
          params,
          ...this.getAdminConfig()
        });

        if (response.data.status) {
          const paginatedData = response.data.data;
          this.reviews = paginatedData.data || [];
          this.pagination = {
            current_page: paginatedData.current_page || 1,
            last_page: paginatedData.last_page || 1
          };
          if (response.data.stats) {
            this.stats = response.data.stats;
          }
        }
      } catch (err) {
        console.error('Lỗi khi lấy danh sách đánh giá:', err);
        if (this.$toast) {
          this.$toast.error('Lỗi kết nối máy chủ khi lấy dữ liệu đánh giá');
        }
      } finally {
        this.initialLoading = false;
        this.updating = false;
      }
    },
    async updateStatus(id, newStatus) {
      const review = this.reviews.find(r => r.id === id);
      if (!review) return;

      const oldStatus = review.status;
      const oldStatusLabel = review.statusLabel;

      // Optimistically update local review status
      review.status = newStatus;
      const statusLabels = {
        'cho_duyet': 'Chờ duyệt',
        'hien_thi': 'Đã duyệt',
        'an': 'Đã ẩn'
      };
      review.statusLabel = statusLabels[newStatus] || 'Không xác định';

      // Optimistically update statistics
      let statsAdjusted = null;
      if (oldStatus === 'cho_duyet' && newStatus !== 'cho_duyet') {
        this.stats.pending = Math.max(0, this.stats.pending - 1);
        statsAdjusted = 'decrement';
      } else if (oldStatus !== 'cho_duyet' && newStatus === 'cho_duyet') {
        this.stats.pending += 1;
        statsAdjusted = 'increment';
      }

      // Filter out if table is currently filtered to a different status
      const originalReviews = [...this.reviews];
      if (this.filters.trang_thai && this.filters.trang_thai !== newStatus) {
        this.reviews = this.reviews.filter(r => r.id !== id);
      }

      try {
        const response = await axios.post(`/api/quan-ly/danh-gia/${id}/trang-thai`, {
          trang_thai: newStatus
        }, this.getAdminConfig());

        if (response.data.status) {
          if (this.$toast) {
            this.$toast.success(newStatus === 'hien_thi' ? 'Đã duyệt hiển thị công khai!' : 'Đã ẩn đánh giá khỏi cửa hàng!');
          }
        } else {
          throw new Error('API error');
        }
      } catch (err) {
        console.error('Lỗi cập nhật trạng thái:', err);
        if (this.$toast) {
          this.$toast.error('Không thể cập nhật trạng thái đánh giá.');
        }
        // Rollback on failure
        this.reviews = originalReviews;
        const rev = this.reviews.find(r => r.id === id);
        if (rev) {
          rev.status = oldStatus;
          rev.statusLabel = oldStatusLabel;
        }
        if (statsAdjusted === 'decrement') {
          this.stats.pending += 1;
        } else if (statsAdjusted === 'increment') {
          this.stats.pending = Math.max(0, this.stats.pending - 1);
        }
      }
    },
    openReplyModal(review) {
      this.selectedReview = review;
      this.replyText = review.reply || '';
      this.showReplyModal = true;
    },
    closeReplyModal() {
      this.showReplyModal = false;
      this.selectedReview = null;
      this.replyText = '';
    },
    async submitReply() {
      if (!this.replyText.trim()) return;
      this.submitting = true;
      const reviewId = this.selectedReview.id;
      const originalReplyText = this.selectedReview.reply;

      // Optimistically update reply
      const review = this.reviews.find(r => r.id === reviewId);
      if (review) {
        review.reply = this.replyText;
      }

      try {
        const response = await axios.post(`/api/quan-ly/danh-gia/${reviewId}/phan-hoi`, {
          phan_hoi_admin: this.replyText
        }, this.getAdminConfig());

        if (response.data.status) {
          if (this.$toast) {
            this.$toast.success('Gửi phản hồi đánh giá thành công!');
          }
          this.closeReplyModal();
        } else {
          throw new Error('API error');
        }
      } catch (err) {
        console.error('Lỗi gửi phản hồi:', err);
        if (this.$toast) {
          this.$toast.error('Có lỗi xảy ra khi gửi phản hồi.');
        }
        // Rollback on failure
        if (review) {
          review.reply = originalReplyText;
        }
      } finally {
        this.submitting = false;
      }
    },
    
    // Lightbox actions
    openLightbox(images, index) {
      this.lightbox.images = images;
      this.lightbox.activeIndex = index;
      this.lightbox.open = true;
      document.body.style.overflow = 'hidden'; // Lock background scrolling
    },
    closeLightbox() {
      this.lightbox.open = false;
      this.lightbox.images = [];
      this.lightbox.activeIndex = 0;
      document.body.style.overflow = ''; // Unlock background scrolling
    },
    prevLightboxImg() {
      if (this.lightbox.activeIndex > 0) {
        this.lightbox.activeIndex--;
      } else {
        this.lightbox.activeIndex = this.lightbox.images.length - 1;
      }
    },
    nextLightboxImg() {
      if (this.lightbox.activeIndex < this.lightbox.images.length - 1) {
        this.lightbox.activeIndex++;
      } else {
        this.lightbox.activeIndex = 0;
      }
    },
    
    // Helper helper: dynamic gradient avatar
    getRandomAvatarBg(name) {
      if (!name) return this.avatarBgColors[0];
      let sum = 0;
      for (let i = 0; i < name.length; i++) {
        sum += name.charCodeAt(i);
      }
      const index = sum % this.avatarBgColors.length;
      return this.avatarBgColors[index];
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/reviews.css";
</style>
