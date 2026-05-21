<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý sản phẩm</h1>
        <p class="page-sub">Kiểm soát toàn bộ vòng đời hàng hóa trên website</p>
      </div>
      <button class="btn-primary" @click="openModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm sản phẩm
      </button>
    </div>

    <!-- Stats Row -->
    <div class="mini-stats">
      <div class="mini-stat" v-for="s in miniStats" :key="s.label" :style="{ background: s.bg }">
        <div class="ms-icon-side" :style="{ color: s.color }">
          <svg v-if="s.icon === 'package'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
            <polygon points="12 22.08 12 12 3 6.92 3 17.08 12 22.08"/>
            <polygon points="12 12 21 6.92 21 17.08 12 22.08"/>
            <polygon points="12 2 3 6.92 12 12 21 6.92 12 2"/>
            <line x1="12" y1="22.08" x2="12" y2="12"/>
          </svg>
          <svg v-else-if="s.icon === 'alert'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <svg v-else-if="s.icon === 'x-circle'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
          </svg>
          <svg v-else-if="s.icon === 'eye-off'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
          </svg>
        </div>
        <div class="ms-content">
          <p class="ms-val" :style="{ color: s.color }">{{ s.val }}</p>
          <p class="ms-label" :style="{ color: s.color }">{{ s.label }}</p>
        </div>
      </div>
    </div>

    <!-- Filters + Table -->
    <div class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="search" type="text" placeholder="Tìm theo tên, SKU..." />
        </div>
        <div class="toolbar-right">
          <select v-model="filterCategory" class="sel">
            <option value="">Tất cả danh mục</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
          </select>
          <select v-model="filterStatus" class="sel">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Đang bán</option>
            <option value="out">Hết hàng</option>
            <option value="hidden">Ẩn</option>
          </select>
          <button class="btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Xuất Excel
          </button>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th style="color: #080616">#</th>
            <th style="color: #080616">Sản phẩm</th>
            <th class="whitespace-nowrap" style="color: #080616">Danh mục</th>
            <th style="color: #080616">Giá bán</th>
            <th class="whitespace-nowrap" style="color: #080616">Tồn kho</th>
            <th class="whitespace-nowrap" style="color: #080616">Trạng thái</th>
            <th style="color: #080616">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, index) in filteredProducts" :key="p.id">
            <td style="color: #080616">{{ index + 1 }}</td>
            <td>
              <div class="product-cell">
                <div class="product-thumb">
                  <img v-if="p.image" :src="p.image && p.image.startsWith('http') ? p.image : 'http://127.0.0.1:8000' + p.image" class="prod-img-preview" />
                  <span v-else>{{ p.emoji || '📦' }}</span>
                </div>
                <div>
                  <p class="prod-name">{{ p.name }}</p>
                  <p class="prod-brand">{{ p.brand }}</p>
                </div>
              </div>
            </td>
            <td>{{ p.category }}</td>
            <td>
              <div>
                <span class="price-main">{{ formatVND(p.price) }}</span>
                <span v-if="p.priceOrig" class="price-orig">{{ formatVND(p.priceOrig) }}</span>
              </div>
            </td>
            <td>
              <span class="stock-badge" :class="p.stock < 10 ? 'low' : 'ok'">{{ p.stock }}</span>
            </td>
            <td>
              <span class="status-pill" :class="'s-' + p.status">{{ statusMap[p.status] }}</span>
            </td>
            <td>
              <div class="action-btns">
                <button class="act-btn view" @click="openViewModal(p)" title="Xem chi tiết" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.2);">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
                <button class="act-btn edit" @click="openModal('edit', p)" title="Sửa">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="act-btn del" @click="confirmDelete(p)" title="Xoá">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-count">Hiển thị {{ filteredProducts.length }} / {{ products.length }} sản phẩm</span>
        <div class="pagination">
          <button class="pg-btn">&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn">2</button>
          <button class="pg-btn">3</button>
          <button class="pg-btn">&gt;</button>
        </div>
      </div>
    </div>

    <!-- Modal Thêm/Sửa sản phẩm (Media rich design) -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-box modal-lg">
        <div class="modal-header">
          <h2>{{ modalMode === 'add' ? 'Thêm sản phẩm mới' : 'Chỉnh sửa sản phẩm' }}</h2>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveProduct">
            <div class="product-modal-grid">
              <!-- Left Column: Fields -->
              <div class="modal-form-left">
                <div class="form-grid">
                  <div class="form-group span-2">
                    <label>Tên sản phẩm <span class="req">*</span></label>
                    <input type="text" v-model="form.name" required placeholder="VD: Gundam RX-78-2 Master Grade" />
                  </div>
                  <div class="form-group">
                    <label>Danh mục <span class="req">*</span></label>
                    <select v-model="form.id_danh_muc" required>
                      <option value="" disabled>-- Chọn danh mục --</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Thương hiệu</label>
                    <input type="text" v-model="form.brand" placeholder="VD: Bandai" />
                  </div>
                  <div class="form-group">
                    <label>Giá bán (đ) <span class="req">*</span></label>
                    <input
                      type="text"
                      inputmode="numeric"
                      :value="formatInputVND(form.price)"
                      @input="form.price = parseVNDInput($event.target.value); $event.target.value = formatInputVND(form.price)"
                      required
                      placeholder="0"
                      autocomplete="off"
                    />
                  </div>
                  <div class="form-group">
                    <label>Giá gốc (nếu giảm giá)</label>
                    <input
                      type="text"
                      inputmode="numeric"
                      :value="formatInputVND(form.priceOrig)"
                      @input="form.priceOrig = parseVNDInput($event.target.value); $event.target.value = formatInputVND(form.priceOrig)"
                      placeholder="0"
                      autocomplete="off"
                    />
                  </div>
                  <div class="form-group">
                    <label>SKU <span class="req">*</span></label>
                    <input type="text" v-model="form.sku" required placeholder="GD-001" />
                  </div>
                  <div class="form-group">
                    <label>Số lượng tồn kho <span class="req">*</span></label>
                    <input type="number" v-model.number="form.stock" required placeholder="0" />
                  </div>
                  <div class="form-group span-2">
                    <label>Mô tả sản phẩm</label>
                    <textarea v-model="form.desc" rows="3" placeholder="Mô tả chi tiết về sản phẩm..."></textarea>
                  </div>
                  <div class="form-group span-2">
                    <label>Trạng thái</label>
                    <div class="radio-group">
                      <label class="radio-opt"><input type="radio" v-model="form.status" value="active" /> Đang bán</label>
                      <label class="radio-opt"><input type="radio" v-model="form.status" value="hidden" /> Ẩn</label>
                      <label class="radio-opt"><input type="radio" v-model="form.status" value="out" /> Hết hàng</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Media Upload -->
              <div class="modal-media-right">
                <!-- Primary Image Upload -->
                <div class="media-card">
                  <label class="media-label">Ảnh đại diện sản phẩm</label>
                  <div class="primary-upload-box" :class="{ 'has-img': form.image }">
                    <input type="file" ref="primaryFileInput" @change="onPrimaryImageChange" accept="image/*" class="file-input-hidden" />
                    <div v-if="!form.image" class="upload-trigger" @click="$refs.primaryFileInput.click()">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                      </svg>
                      <span>Tải ảnh đại diện</span>
                      <p class="upload-tip">Hỗ trợ JPG, PNG, WEBP</p>
                    </div>
                    <div v-else class="primary-preview-wrap">
                      <img :src="form.image" class="primary-preview-img" />
                      <button type="button" class="btn-remove-media" @click="removePrimaryImage" title="Xóa ảnh">✕</button>
                    </div>
                  </div>
                </div>

                <!-- Multiple Gallery Images Upload -->
                <div class="media-card">
                  <label class="media-label">Album ảnh chi tiết (Thêm nhiều ảnh)</label>
                  <div class="gallery-upload-grid">
                    <!-- Image list -->
                    <div class="gallery-item-preview" v-for="(img, idx) in form.gallery" :key="idx">
                      <img :src="img" class="gallery-img" />
                      <button type="button" class="btn-remove-gallery" @click="removeGalleryImage(idx)" title="Xóa">✕</button>
                    </div>
                    <!-- Add box -->
                    <div class="gallery-add-box" @click="$refs.galleryFileInput.click()">
                      <input type="file" ref="galleryFileInput" @change="onGalleryImageChange" accept="image/*" multiple class="file-input-hidden" />
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                      </svg>
                      <span style="font-size: 10px; font-weight: 600; margin-top: 4px;">Thêm ảnh</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer Buttons -->
            <div class="modal-footer">
              <button type="button" class="btn-ghost" @click="closeModal">Hủy</button>
              <button type="submit" class="btn-primary">{{ modalMode === 'add' ? 'Tạo sản phẩm' : 'Lưu thay đổi' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Xem Chi Tiết Sản Phẩm (Premium Detail view) -->
    <div class="modal-overlay" v-if="showViewModal" @click.self="closeViewModal">
      <div class="modal-box modal-lg view-detail-modal">
        <div class="modal-header">
          <h2>Chi tiết sản phẩm</h2>
          <button class="modal-close" @click="closeViewModal">✕</button>
        </div>
        <div class="modal-body" v-if="selectedProduct">
          <div class="product-detail-grid">
            <!-- Left panel: Media view -->
            <div class="detail-media-panel">
              <div class="main-image-wrap">
                <img v-if="selectedProduct.image" :src="selectedProduct.image.startsWith('http') ? selectedProduct.image : 'http://127.0.0.1:8000' + selectedProduct.image" class="detail-main-img" />
                <span class="no-img-placeholder" v-else>
                  <span class="emoji-big">{{ selectedProduct.emoji || '📦' }}</span>
                </span>
              </div>
              <div class="gallery-thumbs" v-if="selectedProduct.gallery && selectedProduct.gallery.length > 0">
                <div class="thumb-item" v-for="(img, idx) in selectedProduct.gallery" :key="idx">
                  <img :src="img.startsWith('http') ? img : 'http://127.0.0.1:8000' + img" class="thumb-img" />
                </div>
              </div>
            </div>

            <!-- Right panel: Information -->
            <div class="detail-info-panel">
              <div class="info-header-block">
                <span class="detail-cat-badge">{{ selectedProduct.category }}</span>
                <h3 class="detail-name">{{ selectedProduct.name }}</h3>
                <div class="detail-sku-row">
                  <span class="sku-label">SKU:</span>
                  <span class="sku-val">{{ selectedProduct.sku }}</span>
                </div>
              </div>

              <div class="info-price-card">
                <div class="detail-price-row">
                  <div class="detail-price-main">{{ formatVND(selectedProduct.price) }}</div>
                  <div class="detail-price-orig" v-if="selectedProduct.priceOrig && selectedProduct.priceOrig > 0">
                    {{ formatVND(selectedProduct.priceOrig) }}
                    <span class="sale-pct" v-if="selectedProduct.priceOrig > selectedProduct.price">
                      -{{ Math.round((1 - selectedProduct.price / selectedProduct.priceOrig) * 100) }}%
                    </span>
                  </div>
                </div>
              </div>

              <div class="info-spec-list">
                <div class="spec-item">
                  <span class="spec-lbl">Kho hàng:</span>
                  <span class="spec-val">
                    <span class="stock-badge" :class="selectedProduct.stock < 10 ? 'low' : 'ok'">
                      {{ selectedProduct.stock }} cái
                    </span>
                  </span>
                </div>
                <div class="spec-item">
                  <span class="spec-lbl">Trạng thái:</span>
                  <span class="spec-val">
                    <span class="status-pill" :class="'s-' + selectedProduct.status">
                      {{ statusMap[selectedProduct.status] }}
                    </span>
                  </span>
                </div>
                <div class="spec-item" v-if="selectedProduct.brand">
                  <span class="spec-lbl">Thương hiệu:</span>
                  <span class="spec-val font-semibold">{{ selectedProduct.brand }}</span>
                </div>
              </div>

              <div class="detail-desc-block">
                <h4>Mô tả sản phẩm:</h4>
                <div class="desc-content-scroll">
                  <p v-if="selectedProduct.desc">{{ selectedProduct.desc }}</p>
                  <p class="no-desc" v-else>Chưa có mô tả cho sản phẩm này.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="closeViewModal">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminProducts',
  data() {
    return {
      search: '',
      filterCategory: '',
      filterStatus: '',
      showModal: false,
      showViewModal: false,
      selectedProduct: null,
      modalMode: 'add',
      form: {
        id: null,
        name: '',
        category: '',
        id_danh_muc: null,
        brand: '',
        price: '',
        priceOrig: '',
        sku: '',
        stock: 10,
        desc: '',
        status: 'active',
        emoji: '🧸',
        image: '',
        imageFile: null,
        gallery: [],       // preview URLs (string)
        galleryFiles: [],  // File objects to upload
        deletedGalleryPaths: [], // paths of existing gallery images to delete
        deletePrimaryImage: false // flag to delete primary image
      },
      statusMap: { active: 'Đang bán', out: 'Hết hàng', hidden: 'Ẩn' },
      products: [],
      categories: [],
    };
  },
  computed: {
    totalProducts() {
      return this.products.length;
    },
    lowStockProducts() {
      return this.products.filter(p => p.stock > 0 && p.stock < 10).length;
    },
    outOfStockProducts() {
      return this.products.filter(p => p.stock === 0).length;
    },
    hiddenProducts() {
      return this.products.filter(p => p.status === 'hidden').length;
    },
    miniStats() {
      return [
        { val: this.totalProducts, label: 'Tổng sản phẩm', icon: 'package', bg: 'linear-gradient(135deg, #6366f1, #8b5cf6)', color: '#ffffff' },
        { val: this.lowStockProducts, label: 'Sắp hết hàng', icon: 'alert', bg: 'linear-gradient(135deg, #f59e0b, #fbbf24)', color: '#ffffff' },
        { val: this.outOfStockProducts, label: 'Hết hàng', icon: 'x-circle', bg: 'linear-gradient(135deg, #f43f5e, #ff4d5e)', color: '#ffffff' },
        { val: this.hiddenProducts, label: 'Đang ẩn', icon: 'eye-off', bg: 'linear-gradient(135deg, #22c55e, #10b981)', color: '#ffffff' },
      ];
    },
    filteredProducts() {
      return this.products.filter(p => {
        const matchSearch = !this.search || 
          p.name.toLowerCase().includes(this.search.toLowerCase()) || 
          (p.sku && p.sku.toLowerCase().includes(this.search.toLowerCase()));
        const matchCat = !this.filterCategory || p.category === this.filterCategory;
        const matchStatus = !this.filterStatus || p.status === this.filterStatus;
        return matchSearch && matchCat && matchStatus;
      });
    },
  },
  mounted() {
    this.fetchProducts();
    this.fetchCategories();
  },
  methods: {
    getConfig() {
      return {
        headers: { 
          Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
        }
      };
    },
    async fetchProducts() {
      try {
        const res = await axios.get('http://127.0.0.1:8000/api/quan-ly/san-pham/data', this.getConfig());
        this.products = res.data.data || [];
      } catch (err) {
        this.showToast('Lỗi tải danh sách sản phẩm', 'error');
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get('http://127.0.0.1:8000/api/quan-ly/danh-muc/data', this.getConfig());
        if (res.data.status) {
          this.categories = res.data.data || [];
        }
      } catch (err) {
        this.showToast('Lỗi tải danh sách danh mục', 'error');
      }
    },
    formatPrice(value) {
      if (value === null || value === undefined || value === '') return '';
      if (typeof value === 'string') return value;
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    formatVND(number) {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(number);
    },
    // Format số thành chuỗi có dấu chấm ngàn để hiển thị trong input (không có ký hiệu đồng)
    formatInputVND(value) {
      if (!value && value !== 0) return '';
      const num = Math.round(parseFloat(value));
      if (isNaN(num) || num === 0) return '';
      return num.toLocaleString('vi-VN');
    },
    // Parse chuỗi input (có thể chứa dấu chấm ngàn) thành số nguyên
    parseVNDInput(str) {
      if (!str) return '';
      const num = parseInt(str.replace(/\./g, '').replace(/\D/g, ''), 10);
      return isNaN(num) ? '' : num;
    },
    showToast(message, type = "success") {
      if (type === "success") {
        this.$toast.success(message);
      } else if (type === "danger" || type === "error") {
        this.$toast.error(message);
      } else if (type === "warning") {
        this.$toast.warning(message);
      } else {
        this.$toast.info(message);
      }
    },
    onPrimaryImageChange(e) {
      const file = e.target.files[0];
      if (file) {
        // Validate max size 2MB
        if (file.size > 9 * 1024 * 1024) {
          this.showToast('Hình ảnh không được vượt quá 9MB!', 'error');
          return;
        }
        this.form.imageFile = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
          this.form.image = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    removePrimaryImage() {
      // Nếu ảnh hiện tại là ảnh từ server (không phải base64 mới chọn), đánh dấu xóa
      if (this.form.image && !this.form.image.startsWith('data:')) {
        this.form.deletePrimaryImage = true;
      }
      this.form.image = '';
      this.form.imageFile = null;
      if (this.$refs.primaryFileInput) {
        this.$refs.primaryFileInput.value = '';
      }
    },
    onGalleryImageChange(e) {
      const files = Array.from(e.target.files);
      files.forEach(file => {
        if (file.size > 9 * 1024 * 1024) {
          this.showToast(`Ảnh "${file.name}" vượt quá 9MB!`, 'error');
          return;
        }
        // Lưu File object để upload
        this.form.galleryFiles.push(file);
        // Tạo preview URL
        const reader = new FileReader();
        reader.onload = (ev) => {
          this.form.gallery.push(ev.target.result);
        };
        reader.readAsDataURL(file);
      });
      // Reset input để có thể chọn lại ảnh cũ
      e.target.value = '';
    },
    removeGalleryImage(index) {
      const imgUrl = this.form.gallery[index];
      // Nếu ảnh là URL từ server (không phải base64 mới thêm), lưu path để gửi backend xóa
      if (imgUrl && !imgUrl.startsWith('data:')) {
        // Trích xuất path tương đối (bỏ domain)
        const path = imgUrl.replace('http://127.0.0.1:8000', '');
        this.form.deletedGalleryPaths.push(path);
      } else {
        // Ảnh mới thêm (base64), tìm index tương ứng trong galleryFiles
        // Đếm số ảnh server trước index này để tính đúng index trong galleryFiles
        let serverImgCount = 0;
        for (let i = 0; i < index; i++) {
          if (!this.form.gallery[i].startsWith('data:')) {
            serverImgCount++;
          }
        }
        const fileIndex = index - serverImgCount;
        if (fileIndex >= 0 && fileIndex < this.form.galleryFiles.length) {
          this.form.galleryFiles.splice(fileIndex, 1);
        }
      }
      this.form.gallery.splice(index, 1);
    },
    openViewModal(product) {
      this.selectedProduct = product;
      this.showViewModal = true;
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedProduct = null;
    },
    openModal(mode, product = null) {
      this.modalMode = mode;
      if (product) {
        this.form = { 
          id: product.id,
          name: product.name,
          category: product.category,
          id_danh_muc: product.id_danh_muc,
          brand: product.brand || '',
          price: product.price,
          priceOrig: product.priceOrig || '',
          sku: product.sku,
          stock: product.stock,
          desc: product.desc || '',
          status: product.status,
          emoji: product.emoji || '📦',
          image: product.image || '',
          imageFile: null,
          gallery: product.gallery ? product.gallery.map(img => img.startsWith('http') ? img : 'http://127.0.0.1:8000' + img) : [],
          galleryFiles: [],  // galleryFiles luôn rỗng khi mở modal sửa
          deletedGalleryPaths: [],
          deletePrimaryImage: false
        };
        // Ghép domain cho ảnh từ storage
        if (this.form.image && !this.form.image.startsWith('http') && !this.form.image.startsWith('data:')) {
            this.form.image = 'http://127.0.0.1:8000' + this.form.image;
        }
      } else {
        const nextSku = 'SP-' + String(this.products.length + 1).padStart(5, '0');
        this.form = {
          id: null,
          name: '',
          category: '',
          id_danh_muc: this.categories.length > 0 ? this.categories[0].id : null,
          brand: '',
          price: '',
          priceOrig: '',
          sku: nextSku,
          stock: 10,
          desc: '',
          status: 'active',
          emoji: '🧸',
          image: '',
          imageFile: null,
          gallery: [],
          galleryFiles: [],
          deletedGalleryPaths: [],
          deletePrimaryImage: false
        };
      }
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveProduct() {
      try {
        const formData = new FormData();
        formData.append('ten_san_pham', this.form.name);
        formData.append('gia_co_ban', this.form.price);
        if (this.form.priceOrig && this.form.priceOrig > 0) {
          formData.append('gia_goc', this.form.priceOrig);
        }
        formData.append('sku', this.form.sku);
        formData.append('so_luong_ton_kho', this.form.stock);
        formData.append('tinh_trang', this.form.status);
        if (this.form.id_danh_muc) {
          formData.append('id_danh_muc', this.form.id_danh_muc);
        }
        if (this.form.desc) formData.append('mo_ta', this.form.desc);
        
        if (this.form.imageFile) {
          formData.append('hinh_anh', this.form.imageFile);
        }
        // Gửi nhiều ảnh gallery
        if (this.form.galleryFiles && this.form.galleryFiles.length > 0) {
          this.form.galleryFiles.forEach(file => {
            formData.append('hinh_anh_phu[]', file);
          });
        }

        // Gửi danh sách ảnh gallery cần xóa
        if (this.form.deletedGalleryPaths && this.form.deletedGalleryPaths.length > 0) {
          this.form.deletedGalleryPaths.forEach(path => {
            formData.append('xoa_hinh_anh_phu[]', path);
          });
        }

        // Gửi flag xóa ảnh đại diện
        if (this.form.deletePrimaryImage) {
          formData.append('xoa_anh_dai_dien', '1');
        }

        const config = {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
            'Content-Type': 'multipart/form-data'
          }
        };

        if (this.modalMode === 'add') {
          const res = await axios.post('http://127.0.0.1:8000/api/quan-ly/san-pham/create', formData, config);
          if (res.data.status) {
            this.showToast(res.data.message, 'success');
            this.fetchProducts();
            window.dispatchEvent(new CustomEvent("orderStatusUpdated"));
            this.closeModal();
          } else {
            this.showToast(res.data.message, 'error');
          }
        } else {
          formData.append('id', this.form.id);
          const res = await axios.post('http://127.0.0.1:8000/api/quan-ly/san-pham/update', formData, config);
          if (res.data.status) {
            this.showToast(res.data.message, 'success');
            this.fetchProducts();
            window.dispatchEvent(new CustomEvent("orderStatusUpdated"));
            this.closeModal();
          } else {
            this.showToast(res.data.message, 'error');
          }
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errorData = error.response.data.errors;
          Object.keys(errorData).forEach((field) => {
            const messages = errorData[field];
            messages.forEach((msg) => {
              this.showToast(msg, 'error');
            });
          });
        } else if (error.response && error.response.data && error.response.data.message) {
          this.showToast(error.response.data.message, 'error');
        } else {
          this.showToast("Có lỗi hệ thống xảy ra. Vui lòng thử lại!", 'error');
        }
      }
    },
    confirmDelete(product) {
      window.Swal.fire({
        title: 'XÓA SẢN PHẨM NÀY?',
        html: `Bạn có chắc chắn muốn xóa sản phẩm <b style="color: #D70018;">"${product.name}"</b>?<br><span style="font-size: 13px; color: #64748b;">Hành động này không thể hoàn tác và sẽ xóa bỏ sản phẩm khỏi hệ thống.</span>`,
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
            const res = await axios.post(`http://127.0.0.1:8000/api/quan-ly/san-pham/delete`, { id: product.id }, this.getConfig());
            if (res.data.status) {
              this.showToast(res.data.message, "success");
              this.fetchProducts();
              window.dispatchEvent(new CustomEvent("orderStatusUpdated"));
            } else {
              this.showToast(res.data.message, "error");
            }
          } catch (err) {
            this.showToast("Xóa thất bại", "error");
          }
        }
      });
    }
  }
};
</script>

<style scoped>
@import "/style_admin/products.css";

/* Modal Width adjustment */
.modal-box.modal-lg {
  max-width: 880px !important;
  width: 95% !important;
}

/* Double-column Modal Layout */
.product-modal-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 24px;
  align-items: start;
}
.modal-form-left {
  display: flex;
  flex-direction: column;
}
.modal-media-right {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Media Card Styling */
.media-card {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: all 0.2s;
}
.media-card:hover {
  border-color: #94a3b8;
  background: #f1f5f9;
}
.media-label {
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 2px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.primary-upload-box {
  height: 165px;
  border-radius: 8px;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.primary-upload-box.has-img {
  border: none;
}
.upload-trigger {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  width: 100%;
  height: 100%;
  justify-content: center;
}
.upload-trigger span {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
}
.upload-tip {
  font-size: 10px;
  color: #94a3b8;
  margin: 0;
}
.primary-preview-wrap {
  width: 100%;
  height: 100%;
  position: relative;
}
.primary-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.btn-remove-media {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.75);
  color: #ffffff;
  border: none;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  backdrop-filter: blur(4px);
  z-index: 5;
}
.btn-remove-media:hover {
  background: #D70018;
}

/* Gallery Upload Grid */
.gallery-upload-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}
.gallery-item-preview {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #ffffff;
}
.gallery-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.btn-remove-gallery {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.75);
  color: #ffffff;
  border: none;
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  z-index: 5;
}
.btn-remove-gallery:hover {
  background: #D70018;
}
.gallery-add-box {
  aspect-ratio: 1;
  border-radius: 8px;
  border: 1px dashed #cbd5e1;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}
.gallery-add-box:hover {
  border-color: #94a3b8;
  background: #f8fafc;
}
.file-input-hidden {
  display: none !important;
}

/* Image preview inside data table */
.prod-img-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 6px;
}
/* Product Detail Modal Styles */
.view-detail-modal {
  max-width: 820px !important;
}
.product-detail-grid {
  display: grid;
  grid-template-columns: 1.1fr 1.3fr;
  gap: 30px;
  align-items: start;
}
.detail-media-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.main-image-wrap {
  width: 100%;
  aspect-ratio: 4/3;
  border-radius: 12px;
  overflow: hidden;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.detail-main-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.no-img-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
}
.emoji-big {
  font-size: 64px;
}
.gallery-thumbs {
  display: flex;
  gap: 3px;
  flex-wrap: wrap;
  padding-bottom: 6px;
}
.thumb-item {
  width: 89px;
  height: 89px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s;
}
.thumb-item:hover {
  border-color: #6366f1;
  transform: translateY(-2px);
}
.thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-info-panel {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.info-header-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.detail-cat-badge {
  align-self: flex-start;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.1);
  padding: 4px 10px;
  border-radius: 100px;
}
.detail-name {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.3;
}
.detail-sku-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #64748b;
}
.sku-val {
  font-weight: 700;
  color: #334155;
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
}

.info-price-card {
  background: linear-gradient(135deg, #fff1f2, #fef2f2);
  border: 1px solid #fecdd3;
  border-radius: 12px;
  padding: 16px 20px;
}
.detail-price-row {
  display: flex;
  align-items: baseline;
  gap: 12px;
  flex-wrap: wrap;
}
.detail-price-main {
  font-size: 26px;
  font-weight: 800;
  color: #D70018;
}
.detail-price-orig {
  font-size: 15px;
  text-decoration: line-through;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 6px;
}
.sale-pct {
  display: inline-block;
  background: #D70018;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 100px;
  text-decoration: none;
}

.info-spec-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 15px;
}
.spec-item {
  display: flex;
  align-items: center;
  font-size: 14px;
}
.spec-lbl {
  width: 110px;
  color: #64748b;
  font-weight: 500;
}
.spec-val {
  color: #1e293b;
}

.detail-desc-block h4 {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 8px;
}
.desc-content-scroll {
  max-height: 140px;
  overflow-y: auto;
  font-size: 13px;
  line-height: 1.6;
  color: #475569;
  background: #f8fafc;
  border-radius: 8px;
  padding: 12px;
  border: 1px dashed #e2e8f0;
}
.no-desc {
  font-style: italic;
  color: #94a3b8;
}
</style>
