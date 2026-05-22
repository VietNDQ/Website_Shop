<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý bài viết</h1>
      </div>
      <button class="btn-primary" @click="openModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Viết bài mới
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="mini-stats">
      <div class="mini-stat" v-for="s in miniStats" :key="s.label" :style="{ background: s.bg }">
        <div class="ms-icon-side" :style="{ color: s.color }">
          <svg v-if="s.icon === 'book'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
          </svg>
          <svg v-else-if="s.icon === 'globe'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
          <svg v-else-if="s.icon === 'file'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <svg v-else-if="s.icon === 'eye'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </div>
        <div class="ms-content">
          <p class="ms-val" :style="{ color: s.color }">{{ s.val }}</p>
          <p class="ms-label" :style="{ color: s.color }">{{ s.label }}</p>
        </div>
      </div>
    </div>

    <!-- Filters + Table Grid -->
    <div class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="filters.q" type="text" placeholder="Tìm theo tiêu đề, tóm tắt..." @input="debouncedFetch" />
        </div>
        <div class="toolbar-right">
          <select v-model="filters.loai" class="sel" @change="fetchPosts">
            <option value="">Tất cả thể loại</option>
            <option value="tin_tuc">Tin tức</option>
            <option value="huong_dan">Hướng dẫn</option>
            <option value="danh_gia">Đánh giá</option>
          </select>
          <select v-model="filters.trang_thai" class="sel" @change="fetchPosts">
            <option value="">Tất cả trạng thái</option>
            <option value="true">Công khai</option>
            <option value="false">Nháp</option>
          </select>
        </div>
      </div>

      <div v-if="isLoading" class="table-loading-state">
        <div class="spinner"></div>
        <p>Đang tải danh sách bài viết...</p>
      </div>

      <div v-else-if="posts.length === 0" class="table-empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <p class="empty-title">Không tìm thấy bài viết</p>
        <p class="empty-subtitle">Hãy thử thay đổi điều kiện lọc hoặc thêm bài viết mới</p>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th style="color: #080616; width: 60px;">#</th>
            <th style="color: #080616; width: 90px;">Ảnh bìa</th>
            <th style="color: #080616;">Tiêu đề bài viết</th>
            <th style="color: #080616; width: 140px;">Thể loại</th>
            <th style="color: #080616; width: 150px;">Người viết</th>
            <th style="color: #080616; width: 100px; text-align: center;">Lượt xem</th>
            <th style="color: #080616; width: 120px;">Trạng thái</th>
            <th style="color: #080616; width: 120px;">Ngày tạo</th>
            <th style="color: #080616; width: 130px; text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, index) in posts" :key="p.id">
            <td style="color: #080616">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td>
              <img v-if="p.anh_dai_dien" :src="p.anh_dai_dien.startsWith('http') ? p.anh_dai_dien : '' + p.anh_dai_dien" class="blog-thumb-cell" alt="Cover" />
              <div v-else class="blog-thumb-cell" style="display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 11px;">Không ảnh</div>
            </td>
            <td>
              <div class="blog-title-cell" :title="p.tieu_de">{{ p.tieu_de }}</div>
              <div class="blog-summary-cell" :title="p.tom_tat">{{ p.tom_tat || 'Chưa có tóm tắt...' }}</div>
            </td>
            <td>
              <span class="blog-cat-badge" :class="catClassMap[p.loai]">
                {{ catNameMap[p.loai] }}
              </span>
            </td>
            <td>
              <div class="blog-author-info">
                <div class="blog-author-avatar">{{ (p.nguoi_dang?.ho_ten || 'A').substring(0, 1).toUpperCase() }}</div>
                <span>{{ p.nguoi_dang?.ho_ten || 'Hệ thống' }}</span>
              </div>
            </td>
            <td style="text-align: center; font-weight: 600; color: #475569;">{{ p.luot_xem }}</td>
            <td>
              <span class="status-pill" :class="p.trang_thai ? 's-active' : 's-hidden'">
                {{ p.trang_thai ? 'Công khai' : 'Nháp' }}
              </span>
            </td>
            <td style="color: #64748b; font-size: 12.5px;">{{ formatDate(p.tao_luc) }}</td>
            <td>
              <div class="action-btns" style="justify-content: center;">
                <button class="act-btn view" @click="openViewModal(p)" title="Xem bài đăng">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
                <button class="act-btn edit" @click="openModal('edit', p)" title="Sửa bài">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="act-btn del" @click="confirmDelete(p)" title="Xoá bài">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Footer -->
      <div class="table-footer" v-if="posts.length > 0 && !isLoading">
        <span class="table-count">Hiển thị {{ pagination.from || 0 }} - {{ pagination.to || 0 }} trong tổng số {{ pagination.total || 0 }} bài viết</span>
        <div class="pagination" v-if="pagination.last_page > 1">
          <button class="pg-btn" :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">&lt;</button>
          <button 
            v-for="p in pagination.last_page" 
            :key="p" 
            class="pg-btn" 
            :class="{ active: pagination.current_page === p }"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
          <button class="pg-btn" :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">&gt;</button>
        </div>
      </div>
    </div>

    <!-- Modal Thêm/Sửa bài viết -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-box modal-lg">
        <div class="modal-header">
          <h2>{{ modalMode === 'add' ? 'Viết bài mới' : 'Chỉnh sửa bài viết' }}</h2>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="savePost">
            <div class="form-grid">
              <!-- Left Side: Basic Info -->
              <div class="form-group span-2">
                <label>Tiêu đề bài viết <span class="req">*</span></label>
                <input type="text" v-model="form.tieu_de" @input="updateSlug" required placeholder="VD: Đánh giá chi tiết sản phẩm mới nhất..." />
                <p v-if="form.slug" style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                  Đường dẫn tĩnh gợi ý: <span style="color: #64748b; font-weight: 500;">blog/{{ form.slug }}</span>
                </p>
              </div>

              <div class="form-group">
                <label>Thể loại <span class="req">*</span></label>
                <select v-model="form.loai" required>
                  <option value="" disabled>-- Chọn thể loại --</option>
                  <option value="tin_tuc">Tin tức & Sự kiện</option>
                  <option value="huong_dan">Hướng dẫn sưu tầm</option>
                  <option value="danh_gia">Đánh giá / Review</option>
                </select>
              </div>

              <div class="form-group">
                <label>Trạng thái</label>
                <div class="toggle-wrap" style="height: 38px;">
                  <button type="button" class="toggle" :class="{ on: form.trang_thai }" @click="form.trang_thai = !form.trang_thai"></button>
                  <span style="font-size: 13.5px; font-weight: 600;" :style="{ color: form.trang_thai ? '#16a34a' : '#64748b' }">
                    {{ form.trang_thai ? 'Công khai (Hiển thị)' : 'Nháp (Tạm ẩn)' }}
                  </span>
                </div>
              </div>

              <div class="form-group span-2">
                <label>Tóm tắt ngắn</label>
                <textarea v-model="form.tom_tat" rows="2" maxlength="500" placeholder="Tóm tắt nội dung bài viết để hiển thị trên danh sách (không quá 500 ký tự)..."></textarea>
              </div>

              <!-- Cover Image Upload -->
              <div class="form-group span-2">
                <label>Hình ảnh đại diện</label>
                <div class="blog-cover-preview">
                  <input type="file" ref="fileInput" @change="onImageChange" accept="image/*" style="display: none;" />
                  
                  <div v-if="!form.image" class="blog-cover-placeholder" @click="$refs.fileInput.click()">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                      <circle cx="8.5" cy="8.5" r="1.5"/>
                      <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Click để chọn hoặc kéo thả ảnh bìa bài viết tại đây</span>
                    <p style="font-size: 11px; margin: 0; color: #cbd5e1;">Hỗ trợ định dạng JPG, PNG, WEBP. Tối đa 2MB.</p>
                  </div>
                  
                  <div v-else style="width: 100%; height: 100%; position: relative;">
                    <img :src="form.image" alt="Preview cover image" />
                    <button type="button" class="btn-remove-cover" @click="removeCoverImage" title="Xóa ảnh bìa">✕</button>
                  </div>
                </div>
              </div>

              <!-- Main Content Text Area -->
              <div class="form-group span-2">
                <label>Nội dung bài viết (Hỗ trợ định dạng HTML) <span class="req">*</span></label>
                <textarea 
                  v-model="form.noi_dung" 
                  class="blog-editor-textarea" 
                  required 
                  placeholder="Nhập nội dung bài viết đầy đủ tại đây. Bạn có thể sử dụng các thẻ HTML cơ bản (như <p>, <b>, <i>, <img>, <h3>...) để trình bày bài viết phong phú."
                ></textarea>
              </div>
            </div>

            <!-- Action buttons inside modal footer -->
            <div class="modal-footer" style="padding-right: 0; padding-bottom: 0;">
              <button type="button" class="btn-ghost" @click="closeModal">Đóng</button>
              <button type="submit" class="btn-primary">
                {{ modalMode === 'add' ? 'Đăng bài viết' : 'Lưu cập nhật' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Xem chi tiết bài viết (Read-only public preview) -->
    <div class="modal-overlay" v-if="showViewModal" @click.self="closeViewModal">
      <div class="modal-box modal-lg" style="max-height: 85vh;">
        <div class="modal-header">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span class="blog-cat-badge" :class="catClassMap[selectedPost?.loai]">
              {{ catNameMap[selectedPost?.loai] }}
            </span>
            <span style="font-size: 12.5px; color: #94a3b8;">Lượt xem: {{ selectedPost?.luot_xem || 0 }}</span>
          </div>
          <button class="modal-close" @click="closeViewModal">✕</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
          <div v-if="selectedPost">
            <h1 style="font-family: 'Barlow Condensed', sans-serif; font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 12px; line-height: 1.3;">
              {{ selectedPost.tieu_de }}
            </h1>
            
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
              <div class="blog-author-info">
                <div class="blog-author-avatar">{{ (selectedPost.nguoi_dang?.ho_ten || 'A').substring(0, 1).toUpperCase() }}</div>
                <span style="font-weight: 600; font-size: 13px; color: #334155;">{{ selectedPost.nguoi_dang?.ho_ten || 'Hệ thống' }}</span>
              </div>
              <span style="font-size: 12.5px; color: #94a3b8;">{{ formatDate(selectedPost.tao_luc) }}</span>
              <span class="status-pill" :class="selectedPost.trang_thai ? 's-active' : 's-hidden'" style="margin-left: auto;">
                {{ selectedPost.trang_thai ? 'Công khai' : 'Nháp' }}
              </span>
            </div>

            <div v-if="selectedPost.anh_dai_dien" style="width: 100%; border-radius: 12px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
              <img 
                :src="selectedPost.anh_dai_dien.startsWith('http') ? selectedPost.anh_dai_dien : '' + selectedPost.anh_dai_dien" 
                style="width: 100%; max-height: 300px; object-fit: cover; display: block;" 
                alt="Post cover"
              />
            </div>

            <p style="font-weight: 500; font-size: 15px; color: #475569; line-height: 1.6; background: #f8fafc; padding: 14px 18px; border-left: 4px solid #D70018; border-radius: 4px; margin-bottom: 20px;">
              {{ selectedPost.tom_tat }}
            </p>

            <div 
              class="blog-content-preview-body"
              v-html="selectedPost.noi_dung"
              style="line-height: 1.7; color: #334155; font-size: 14px;"
            ></div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="closeViewModal">Đóng xem trước</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminBlog',
  data() {
    return {
      posts: [],
      isLoading: false,
      showModal: false,
      showViewModal: false,
      modalMode: 'add', // add | edit
      selectedPost: null,
      hasSubmitted: false,
      
      filters: {
        q: '',
        loai: '',
        trang_thai: ''
      },

      pagination: {
        current_page: 1,
        per_page: 15,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      },

      form: {
        id: null,
        tieu_de: '',
        slug: '',
        loai: '',
        trang_thai: true,
        tom_tat: '',
        noi_dung: '',
        image: '', // data URL or public URL for previewing
        imageFile: null, // File object to upload
        deleteImage: false // flag to delete existing image
      },

      catNameMap: {
        tin_tuc: 'Tin tức & Sự kiện',
        huong_dan: 'Hướng dẫn sưu tầm',
        danh_gia: 'Đánh giá / Review'
      },

      catClassMap: {
        tin_tuc: 'cat-news',
        huong_dan: 'cat-guide',
        danh_gia: 'cat-review'
      },

      debounceTimer: null
    };
  },
  computed: {
    totalPostsCount() {
      return this.pagination.total || 0;
    },
    publicPostsCount() {
      // computed based on data fetched, but let's approximate or just track totals from stats
      return this.posts.filter(p => p.trang_thai).length; 
    },
    totalViews() {
      return this.posts.reduce((sum, p) => sum + (p.luot_xem || 0), 0);
    },
    miniStats() {
      // Find category totals if available, otherwise just use mock/approximate stats
      const total = this.pagination.total || 0;
      return [
        { val: total, label: 'Tổng bài viết', icon: 'book', bg: 'linear-gradient(135deg, #0f172a, #334155)', color: '#ffffff' },
        { val: this.posts.filter(p => p.trang_thai).length, label: 'Đang công khai', icon: 'globe', bg: 'linear-gradient(135deg, #10b981, #059669)', color: '#ffffff' },
        { val: this.posts.filter(p => !p.trang_thai).length, label: 'Bài viết nháp', icon: 'file', bg: 'linear-gradient(135deg, #f59e0b, #d97706)', color: '#ffffff' },
        { val: this.posts.reduce((sum, p) => sum + (p.luot_xem || 0), 0), label: 'Lượt xem', icon: 'eye', bg: 'linear-gradient(135deg, #6366f1, #4f46e5)', color: '#ffffff' },
      ];
    }
  },
  mounted() {
    this.fetchPosts();
  },
  methods: {
    getConfig() {
      return {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('token_admin')
        }
      };
    },
    debouncedFetch() {
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
          q: this.filters.q,
          loai: this.filters.loai,
          trang_thai: this.filters.trang_thai
        };
        const config = {
          ...this.getConfig(),
          params
        };
        const res = await axios.get('/api/staff/blog', config);
        if (res.data.status) {
          const paginated = res.data.data;
          this.posts = paginated.data || [];
          this.pagination.current_page = paginated.current_page;
          this.pagination.last_page = paginated.last_page;
          this.pagination.total = paginated.total;
          this.pagination.per_page = paginated.per_page;
          this.pagination.from = paginated.from;
          this.pagination.to = paginated.to;
        } else {
          this.showToast('Không lấy được danh sách bài viết!', 'error');
        }
      } catch (error) {
        this.showToast('Lỗi khi tải danh sách bài viết!', 'error');
        console.error(error);
      } finally {
        this.isLoading = false;
      }
    },
    changePage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.fetchPosts();
    },
    updateSlug() {
      this.form.slug = this.slugify(this.form.tieu_de);
    },
    slugify(text) {
      if (!text) return '';
      const unicode = {
        'a': 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd': 'đ',
        'e': 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i': 'í|ì|ỉ|ĩ|ị',
        'o': 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u': 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y': 'ý|ỳ|ỷ|ỹ|ỵ',
        'A': 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'D': 'Đ',
        'E': 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I': 'Í|Ì|Ỉ|Ĩ|Ị',
        'O': 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'U': 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'Y': 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
      };
      let str = text;
      for (let nonUnicode in unicode) {
        let uni = unicode[nonUnicode];
        str = str.replace(new RegExp(uni, 'gi'), nonUnicode);
      }
      return str.toLowerCase()
        .replace(/[^a-z0-9\-]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      if (isNaN(date.getTime())) return dateStr;
      return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    showToast(message, type = 'success') {
      if (type === 'success') this.$toast.success(message);
      else if (type === 'error' || type === 'danger') this.$toast.error(message);
      else if (type === 'warning') this.$toast.warning(message);
      else this.$toast.info(message);
    },
    openModal(mode, post = null) {
      this.modalMode = mode;
      this.hasSubmitted = false;
      
      if (mode === 'add') {
        this.form = {
          id: null,
          tieu_de: '',
          slug: '',
          loai: '',
          trang_thai: true,
          tom_tat: '',
          noi_dung: '',
          image: '',
          imageFile: null,
          deleteImage: false
        };
      } else if (mode === 'edit' && post) {
        this.form = {
          id: post.id,
          tieu_de: post.tieu_de,
          slug: post.slug,
          loai: post.loai,
          trang_thai: !!post.trang_thai,
          tom_tat: post.tom_tat || '',
          noi_dung: post.noi_dung,
          image: post.anh_dai_dien ? (post.anh_dai_dien.startsWith('http') ? post.anh_dai_dien : '' + post.anh_dai_dien) : '',
          imageFile: null,
          deleteImage: false
        };
      }
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.form = {
        id: null,
        tieu_de: '',
        slug: '',
        loai: '',
        trang_thai: true,
        tom_tat: '',
        noi_dung: '',
        image: '',
        imageFile: null,
        deleteImage: false
      };
    },
    onImageChange(e) {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          this.showToast('Kích thước ảnh không vượt quá 2MB!', 'warning');
          return;
        }
        this.form.imageFile = file;
        this.form.deleteImage = false;
        
        const reader = new FileReader();
        reader.onload = (e) => {
          this.form.image = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    removeCoverImage() {
      this.form.image = '';
      this.form.imageFile = null;
      this.form.deleteImage = true;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    async savePost() {
      this.hasSubmitted = true;
      if (!this.form.tieu_de || !this.form.noi_dung || !this.form.loai) {
        this.showToast('Vui lòng điền đầy đủ các thông tin bắt buộc!', 'warning');
        return;
      }

      try {
        const config = {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
            'Content-Type': 'multipart/form-data'
          }
        };

        const formData = new FormData();
        formData.append('tieu_de', this.form.tieu_de);
        formData.append('noi_dung', this.form.noi_dung);
        formData.append('loai', this.form.loai);
        formData.append('trang_thai', this.form.trang_thai ? '1' : '0');
        formData.append('tom_tat', this.form.tom_tat || '');
        
        if (this.form.imageFile) {
          formData.append('anh_dai_dien_file', this.form.imageFile);
        }

        let res;
        if (this.modalMode === 'add') {
          res = await axios.post('/api/staff/blog', formData, config);
        } else {
          formData.append('xoa_anh_dai_dien', this.form.deleteImage ? '1' : '0');
          res = await axios.post(`/api/staff/blog/${this.form.id}/update`, formData, config);
        }

        if (res.data.status) {
          this.showToast(res.data.message || 'Lưu bài viết thành công!', 'success');
          this.closeModal();
          this.fetchPosts();
        } else {
          this.showToast(res.data.message || 'Có lỗi xảy ra!', 'error');
        }
      } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          Object.keys(errors).forEach(key => {
            errors[key].forEach(msg => this.showToast(msg, 'error'));
          });
        } else {
          this.showToast('Lỗi khi lưu bài viết!', 'error');
          console.error(error);
        }
      }
    },
    confirmDelete(post) {
      window.Swal.fire({
        title: 'XÓA BÀI VIẾT NÀY?',
        html: `Bạn có chắc chắn muốn xóa bài viết <b style="color: #D70018;">"${post.tieu_de}"</b>?<br><span style="font-size: 13px; color: #64748b;">Hành động này không thể hoàn tác và bài viết sẽ biến mất khỏi trang chủ khách hàng.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#D70018',
        cancelButtonColor: '#f1f5f9',
        confirmButtonText: 'Xác nhận xóa',
        cancelButtonText: 'Hủy bỏ',
        background: '#ffffff',
        color: '#0f172a',
        customClass: {
          popup: 'swal-premium-popup',
          title: 'swal-premium-title',
          htmlContainer: 'swal-premium-text',
          confirmButton: 'btn-swal-confirm',
          cancelButton: 'btn-swal-cancel'
        },
        buttonsStyling: false
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await axios.delete(`/api/staff/blog/${post.id}`, this.getConfig());
            if (res.data.status) {
              this.showToast('Xóa bài viết thành công!', 'success');
              this.fetchPosts();
            } else {
              this.showToast(res.data.message || 'Xóa thất bại!', 'error');
            }
          } catch (error) {
            this.showToast('Không thể kết nối đến máy chủ để xóa bài viết!', 'error');
            console.error(error);
          }
        }
      });
    },
    openViewModal(post) {
      this.selectedPost = post;
      this.showViewModal = true;
    },
    closeViewModal() {
      this.selectedPost = null;
      this.showViewModal = false;
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/blog.css";

.table-loading-state,
.table-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  color: #64748b;
}

.table-loading-state p {
  margin-top: 12px;
  font-size: 14.5px;
  font-weight: 500;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3.5px solid rgba(215, 0, 24, 0.1);
  border-top-color: #D70018;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.table-empty-state svg {
  margin-bottom: 16px;
}

.empty-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.empty-subtitle {
  font-size: 13px;
  color: #94a3b8;
  margin: 4px 0 0 0;
}

.blog-content-preview-body :deep(p) {
  margin-bottom: 1.2em;
  line-height: 1.7;
}

.blog-content-preview-body :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 16px 0;
}

.blog-content-preview-body :deep(h2), 
.blog-content-preview-body :deep(h3) {
  font-family: 'Barlow Condensed', sans-serif;
  color: #0f172a;
  font-weight: 800;
  margin-top: 24px;
  margin-bottom: 12px;
  text-transform: uppercase;
}
</style>
