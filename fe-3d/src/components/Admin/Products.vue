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
          <button class="btn-outline" @click="exportToExcel">
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
                  <img v-if="p.image" :src="p.image && p.image.startsWith('http') ? p.image : '' + p.image" class="prod-img-preview" />
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
          <!-- Steps Indicator -->
          <div class="steps-indicator">
            <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
              <div class="step-num">1</div>
              <div class="step-label">Thông tin chung</div>
            </div>
            <div class="step-line" :class="{ active: currentStep > 1 }"></div>
            <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
              <div class="step-num">2</div>
              <div class="step-label">Nhóm thuộc tính</div>
            </div>
            <div class="step-line" :class="{ active: currentStep > 2 }"></div>
            <div class="step" :class="{ active: currentStep >= 3 }">
              <div class="step-num">3</div>
              <div class="step-label">Bảng biến thể</div>
            </div>
          </div>

          <form @submit.prevent="saveProduct">
            <!-- Step 1: General Info & Images -->
            <div class="product-modal-grid" v-show="currentStep === 1">
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
                  <div class="form-group" :class="{ 'disabled-field': hasVariants }">
                    <label>Giá cơ bản (đ) <span class="req" v-if="!hasVariants">*</span></label>
                    <input
                      type="text"
                      inputmode="numeric"
                      :value="formatInputVND(form.price)"
                      @input="form.price = parseVNDInput($event.target.value); $event.target.value = formatInputVND(form.price)"
                      :required="!hasVariants"
                      :disabled="hasVariants"
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
                    <label>SKU sản phẩm <span class="req">*</span></label>
                    <input type="text" v-model="form.sku" required placeholder="GD-001" />
                  </div>
                  <div class="form-group" :class="{ 'disabled-field': hasVariants }">
                    <label>Số lượng mặc định <span class="req" v-if="!hasVariants">*</span></label>
                    <input type="number" v-model.number="form.stock" :required="!hasVariants" :disabled="hasVariants" placeholder="0" />
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

            <!-- Step 2: Custom & Suggested Attributes Setup -->
            <div class="attr-setup-container" v-show="currentStep === 2">
              <div class="suggestion-buttons">
                <span class="suggestion-title">Gợi ý nhanh:</span>
                <button type="button" class="btn-suggest" @click="suggestAttribute('Màu sắc')">+ Màu sắc</button>
                <button type="button" class="btn-suggest" @click="suggestAttribute('Kích thước')">+ Kích thước</button>
                <button type="button" class="btn-suggest" @click="suggestAttribute('Chất liệu')">+ Chất liệu</button>
                <button type="button" class="btn-suggest" @click="suggestAttribute('Phiên bản')">+ Phiên bản</button>
              </div>

              <div class="custom-attr-input-wrap">
                <input 
                  type="text" 
                  v-model="customAttributeName" 
                  placeholder="Hoặc nhập tên thuộc tính tự do (VD: Tỷ lệ, Khớp nối, Dung tích...)" 
                  class="input-custom-attr" 
                  @keyup.enter="addCustomAttribute" 
                />
                <button type="button" class="btn-add-custom-attr" @click="addCustomAttribute">Thêm thuộc tính</button>
              </div>

              <div class="attr-groups-list">
                <div class="attr-group-item" v-for="(attr, idx) in form.thuoc_tinh" :key="idx">
                  <div class="attr-group-header">
                    <span class="attr-group-title">{{ attr.ten_thuoc_tinh }}</span>
                    <span v-if="idx === 0" class="attr-group-hint">📷 Nhóm đầu tiên — mỗi tag có thể gắn ảnh riêng</span>
                    <button type="button" class="btn-delete-attr-group" @click="removeAttributeGroup(idx)" title="Xóa nhóm">✕ Xóa nhóm</button>
                  </div>
                  <div class="attr-tags-wrap">
                    <div class="attr-tag-item" v-for="(tag, tagIdx) in attr.gia_tri" :key="tagIdx">
                      <!-- Ô upload ảnh nhỏ (chỉ nhóm đầu tiên) -->
                      <div v-if="idx === 0" class="tag-img-slot" @click="triggerTagImageUpload(0, tag)" :title="'Upload ảnh cho tag: ' + tag">
                        <img v-if="attr.tagImages && attr.tagImages[tag] && attr.tagImages[tag].imagePreview" :src="attr.tagImages[tag].imagePreview" class="tag-img-thumb" />
                        <span v-else class="tag-img-icon">📷</span>
                      </div>
                      <span class="attr-tag-label">{{ tag }}</span>
                      <button type="button" class="btn-remove-tag" @click="removeTag(idx, tagIdx)">✕</button>
                    </div>
                    <input 
                      type="text" 
                      v-model="attr.newTagInput" 
                      placeholder="Nhập giá trị phân loại rồi bấm Enter (VD: Đỏ, Nhỏ)..." 
                      class="input-new-tag"
                      @keydown.enter.prevent="addTag(idx)"
                    />
                  </div>
                </div>
                <div v-if="form.thuoc_tinh.length === 0" class="no-attr-hint">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>
                  </svg>
                  <p>Chưa có nhóm thuộc tính nào. Sản phẩm này sẽ được coi là sản phẩm không có biến thể.</p>
                </div>
              </div>
              <!-- Hidden file input for tag image upload -->
              <input type="file" ref="tagImageInput" @change="onTagImageChange" accept="image/*" class="file-input-hidden" />
              <!-- Hidden file input for variant image upload -->
              <input type="file" ref="variantImageInput" @change="onVariantImageChange" accept="image/*" class="file-input-hidden" />
            </div>

            <!-- Step 3: Variants Table & Price Management -->
            <div class="variants-container" v-show="currentStep === 3">
              <!-- Bulk Apply Section -->
              <div class="bulk-apply-card" v-if="form.bien_the.length > 0">
                <p class="bulk-apply-title">Thiết lập nhanh cho tất cả các biến thể</p>
                <div class="bulk-apply-inputs">
                  <div class="bulk-input-group">
                    <label>Giá bán (đ):</label>
                    <input 
                      type="text" 
                      inputmode="numeric"
                      :value="formatInputVND(bulkPrice)" 
                      @input="bulkPrice = parseVNDInput($event.target.value); $event.target.value = formatInputVND(bulkPrice)" 
                      placeholder="Nhập giá chung..." 
                    />
                  </div>
                  <div class="bulk-input-group">
                    <label>Giá gốc (đ):</label>
                    <input 
                      type="text" 
                      inputmode="numeric"
                      :value="formatInputVND(bulkPriceOrig)" 
                      @input="bulkPriceOrig = parseVNDInput($event.target.value); $event.target.value = formatInputVND(bulkPriceOrig)" 
                      placeholder="Nhập giá gốc..." 
                    />
                  </div>
                  <div class="bulk-input-group">
                    <label>Số lượng tồn:</label>
                    <input type="number" v-model.number="bulkStock" placeholder="Nhập số lượng..." />
                  </div>
                  <button type="button" class="btn-bulk-apply" @click="applyBulkValues">Áp dụng hàng loạt</button>
                </div>
              </div>

              <!-- Variants Table -->
              <div class="variants-table-wrap">
                <table class="variants-table">
                  <thead>
                    <tr>
                      <th style="width: 22%">Tên biến thể</th>
                      <th style="width: 8%">Ảnh</th>
                      <th style="width: 20%">Mã SKU <span class="req">*</span></th>
                      <th style="width: 18%">Giá bán (đ) <span class="req">*</span></th>
                      <th style="width: 18%">Giá gốc (đ)</th>
                      <th style="width: 14%">Tồn kho <span class="req">*</span></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(bt, btIdx) in form.bien_the" :key="btIdx">
                      <td class="variant-name-cell">{{ bt.variant_name }}</td>
                      <td class="variant-img-cell">
                        <div class="variant-img-container">
                          <div class="variant-img-preview clickable" @click="triggerVariantImageUpload(btIdx)" :title="bt.imagePreview ? 'Click để đổi ảnh' : 'Click để chọn ảnh'">
                            <img v-if="bt.imagePreview" :src="bt.imagePreview" class="variant-thumb" />
                            <span v-else class="variant-no-img">📷</span>
                          </div>
                          <button v-if="bt.imagePreview" type="button" class="btn-remove-variant-img" @click.stop="removeVariantImage(btIdx)" title="Xóa ảnh">✕</button>
                        </div>
                      </td>
                      <td>
                        <input type="text" v-model="bt.sku" required placeholder="Nhập SKU..." />
                      </td>
                      <td>
                        <input 
                          type="text" 
                          inputmode="numeric"
                          :value="formatInputVND(bt.price)" 
                          @input="bt.price = parseVNDInput($event.target.value); $event.target.value = formatInputVND(bt.price)" 
                          required 
                          placeholder="0" 
                          :class="{ 'border-red-500': hasSubmitted && (bt.price === '' || bt.price === null || bt.price === undefined || bt.price < 0) }"
                        />
                      </td>
                      <td>
                        <input 
                          type="text" 
                          inputmode="numeric"
                          :value="formatInputVND(bt.priceOrig)" 
                          @input="bt.priceOrig = parseVNDInput($event.target.value); $event.target.value = formatInputVND(bt.priceOrig)" 
                          placeholder="Để trống nếu không có"
                        />
                      </td>
                      <td>
                        <input 
                          type="number" 
                          v-model.number="bt.stock" 
                          required 
                          placeholder="0" 
                          :class="{ 'border-red-500': hasSubmitted && (bt.stock === '' || bt.stock === null || bt.stock === undefined || bt.stock < 0) }"
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Footer Buttons -->
            <div class="modal-footer">
              <button type="button" class="btn-ghost" @click="closeModal" v-if="currentStep === 1">Hủy</button>
              <button type="button" class="btn-outline" @click="currentStep--" v-if="currentStep > 1">Quay lại</button>
              <button type="button" class="btn-primary" @click="goToNextStep" v-if="currentStep < 3">Tiếp tục</button>
              <button type="submit" class="btn-primary" v-if="currentStep === 3">{{ modalMode === 'add' ? 'Tạo sản phẩm' : 'Lưu thay đổi' }}</button>
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
                <img v-if="selectedProduct.image" :src="selectedProduct.image.startsWith('http') ? selectedProduct.image : '' + selectedProduct.image" class="detail-main-img" />
                <span class="no-img-placeholder" v-else>
                  <span class="emoji-big">{{ selectedProduct.emoji || '📦' }}</span>
                </span>
              </div>
              <div class="gallery-thumbs" v-if="selectedProduct.gallery && selectedProduct.gallery.length > 0">
                <div class="thumb-item" v-for="(img, idx) in selectedProduct.gallery" :key="idx">
                  <img :src="img.startsWith('http') ? img : '' + img" class="thumb-img" />
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

              <!-- Attributes Section -->
              <div class="info-attrs-block" v-if="selectedProduct.thuoc_tinh && selectedProduct.thuoc_tinh.length > 0">
                <h4 class="detail-section-title">Phân loại thuộc tính:</h4>
                <div class="detail-attrs-list">
                  <div class="detail-attr-row" v-for="(tt, idx) in selectedProduct.thuoc_tinh" :key="idx">
                    <span class="detail-attr-name">{{ tt.ten_thuoc_tinh }}:</span>
                    <div class="detail-attr-values">
                      <span class="detail-attr-val-badge" v-for="(val, vIdx) in parseAttributeValues(tt.gia_tri)" :key="vIdx">
                        {{ val }}
                      </span>
                    </div>
                  </div>
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

          <!-- Variants Section -->
          <div class="detail-variants-section" v-if="selectedProduct.bien_the && selectedProduct.bien_the.length > 0">
            <h4 class="detail-section-title mt-4">Danh sách các biến thể của sản phẩm</h4>
            <div class="variants-table-wrap">
              <table class="variants-table detail-variants-table">
                <thead>
                  <tr>
                    <th>Tên biến thể</th>
                    <th>Mã SKU</th>
                    <th>Giá bán (đ)</th>
                    <th>Tồn kho</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(bt, btIdx) in selectedProduct.bien_the" :key="btIdx">
                    <td class="variant-name-cell">{{ getVariantName(bt) }}</td>
                    <td>
                      <span class="sku-val">{{ bt.ma_kho || bt.sku }}</span>
                    </td>
                    <td>
                      <span class="detail-variant-price">{{ formatVND(bt.gia_ban || bt.price) }}</span>
                    </td>
                    <td>
                      <span class="stock-badge" :class="(bt.so_luong_ton_kho || bt.stock) < 10 ? 'low' : 'ok'">
                        {{ bt.so_luong_ton_kho || bt.stock }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
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
import * as XLSX from 'xlsx';

export default {
  name: 'AdminProducts',
  data() {
    return {
      currentStep: 1,
      customAttributeName: '',
      bulkPrice: '',
      bulkPriceOrig: '',
      bulkStock: '',
      search: '',
      filterCategory: '',
      filterStatus: '',
      showModal: false,
      showViewModal: false,
      selectedProduct: null,
      modalMode: 'add',
      hasSubmitted: false,
      activeTagUpload: null, // { attrIdx, tag } - dùng cho upload ảnh tag
      activeVariantUploadIdx: null, // dùng cho upload ảnh biến thể
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
        deletePrimaryImage: false, // flag to delete primary image
        thuoc_tinh: [],
        bien_the: []
      },
      statusMap: { active: 'Đang bán', out: 'Hết hàng', hidden: 'Ẩn' },
      products: [],
      categories: [],
    };
  },
  computed: {
    hasVariants() {
      return this.form.thuoc_tinh && this.form.thuoc_tinh.length > 0;
    },
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
        const res = await axios.get('/api/quan-ly/san-pham/data', this.getConfig());
        this.products = res.data.data || [];
      } catch (err) {
        this.showToast('Lỗi tải danh sách sản phẩm', 'error');
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get('/api/quan-ly/danh-muc/data', this.getConfig());
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
    exportToExcel() {
      if (!this.filteredProducts || this.filteredProducts.length === 0) {
        this.showToast('Không có sản phẩm nào để xuất!', 'warning');
        return;
      }
      
      try {
        const dataToExport = this.filteredProducts.map((p, index) => ({
          'STT': index + 1,
          'Tên sản phẩm': p.name || '',
          'SKU': p.sku || 'N/A',
          'Thương hiệu': p.brand || 'N/A',
          'Danh mục': p.category || 'Chưa phân loại',
          'Giá bán (đ)': p.price || 0,
          'Giá gốc (đ)': p.priceOrig || 0,
          'Số lượng tồn': p.stock || 0,
          'Trạng thái': this.statusMap[p.status] || p.status
        }));

        const worksheet = XLSX.utils.json_to_sheet(dataToExport);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Sản phẩm');

        // Auto-fit column widths
        const maxLens = {};
        dataToExport.forEach(row => {
          Object.keys(row).forEach(key => {
            const val = String(row[key] || '');
            maxLens[key] = Math.max(maxLens[key] || key.length, val.length);
          });
        });
        worksheet['!cols'] = Object.keys(maxLens).map(key => ({
          wch: Math.min(Math.max(maxLens[key] + 3, 10), 50)
        }));

        XLSX.writeFile(workbook, 'Danh_sach_san_pham.xlsx');
        this.showToast('Xuất Excel thành công!', 'success');
      } catch (error) {
        this.showToast('Lỗi khi xuất Excel!', 'error');
        console.error(error);
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
        const path = imgUrl.replace('', '');
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
    getVariantName(bt) {
      if (bt.variant_name) return bt.variant_name;
      const ttMap = typeof bt.thuoc_tinh === 'string' ? JSON.parse(bt.thuoc_tinh) : bt.thuoc_tinh;
      const nameValues = ttMap ? Object.values(ttMap) : [];
      return nameValues.length > 0 ? nameValues.join(' - ') : 'Mặc định';
    },
    parseAttributeValues(giaTri) {
      if (Array.isArray(giaTri)) return giaTri;
      if (typeof giaTri === 'string') {
        try {
          return JSON.parse(giaTri);
        } catch (e) {
          return [];
        }
      }
      return [];
    },
    openModal(mode, product = null) {
      this.modalMode = mode;
      this.currentStep = 1;
      this.customAttributeName = '';
      this.bulkPrice = '';
      this.bulkPrice = '';
      this.bulkPriceOrig = '';
      this.bulkStock = '';
      this.hasSubmitted = false;
      if (product) {
        const keyTranslation = {
          'color': 'mau_sac',
          'mau': 'mau_sac',
          'mau_sac': 'mau_sac',
          'size': 'kich_thuoc',
          'kich_thuoc': 'kich_thuoc',
          'kich_co': 'kich_thuoc',
          'material': 'chat_lieu',
          'chat_lieu': 'chat_lieu',
          'version': 'phien_ban',
          'phien_ban': 'phien_ban'
        };
        const displayNameMap = {
          'mau_sac': 'Màu sắc',
          'kich_thuoc': 'Kích thước',
          'chat_lieu': 'Chất liệu',
          'phien_ban': 'Phiên bản'
        };

        const formBienThe = product.bien_the ? product.bien_the.map(bt => {
          let ttMap = typeof bt.thuoc_tinh === 'string' ? JSON.parse(bt.thuoc_tinh) : bt.thuoc_tinh;
          if (ttMap) {
            const translatedMap = {};
            Object.keys(ttMap).forEach(k => {
              const normK = keyTranslation[k] || this.normalizeAttributeKey(k);
              translatedMap[normK] = ttMap[k];
            });
            ttMap = translatedMap;
          }
          const nameValues = ttMap ? Object.values(ttMap) : [];
          const variantName = nameValues.length > 0 ? nameValues.join(' - ') : 'Mặc định';
          const existingImage = bt.hinh_anh || null;
          return {
            id: bt.id,
            variant_name: variantName,
            thuoc_tinh: ttMap || {},
            price: bt.gia_ban,
            priceOrig: bt.gia_goc || null,
            stock: bt.so_luong_ton_kho,
            sku: bt.ma_kho,
            imageFile: null,
            imagePreview: existingImage ? (existingImage.startsWith('http') ? existingImage : '' + existingImage) : '',
            existingImage: existingImage,
          };
        }) : [];

        let formThuocTinh = product.thuoc_tinh ? product.thuoc_tinh.map(tt => ({
          id: tt.id,
          ten_thuoc_tinh: tt.ten_thuoc_tinh,
          gia_tri: this.parseAttributeValues(tt.gia_tri),
          newTagInput: ''
        })) : [];

        // Nếu product.thuoc_tinh rỗng nhưng có biến thể chứa thuộc tính, thực hiện dựng lại
        if (formThuocTinh.length === 0 && formBienThe.length > 0) {
          const tempAttrs = {};
          formBienThe.forEach(bt => {
            if (bt.thuoc_tinh) {
              Object.keys(bt.thuoc_tinh).forEach(k => {
                if (!tempAttrs[k]) {
                  const displayName = displayNameMap[k] || k.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                  tempAttrs[k] = {
                    ten_thuoc_tinh: displayName,
                    gia_tri: new Set()
                  };
                }
                if (bt.thuoc_tinh[k]) {
                  tempAttrs[k].gia_tri.add(bt.thuoc_tinh[k]);
                }
              });
            }
          });

          const reconstructed = [];
          Object.keys(tempAttrs).forEach(k => {
            reconstructed.push({
              id: null,
              ten_thuoc_tinh: tempAttrs[k].ten_thuoc_tinh,
              gia_tri: Array.from(tempAttrs[k].gia_tri),
              newTagInput: ''
            });
          });

          if (reconstructed.length > 0) {
            formThuocTinh = reconstructed;
          }
        }

        this.form = { 
          id: product.id,
          name: product.name,
          category: product.category,
          id_danh_muc: product.id_danh_muc,
          brand: product.brand || '',
          price: product.price,
          priceOrig: product.priceOrig || '',
          sku: (() => {
            // Trích xuất SKU gốc sạch: bỏ hậu tố thuộc tính của biến thể đầu tiên
            // VD: 'SP-00014-VANG-TO-NHUA' → 'SP-00014'
            let baseSku = product.sku || '';
            if (formBienThe && formBienThe.length > 0) {
              const firstVariant = formBienThe[0];
              const firstTt = firstVariant.thuoc_tinh;
              if (firstTt && Object.keys(firstTt).length > 0) {
                const suffix = Object.values(firstTt)
                  .map(val => (val || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').replace(/[^a-zA-Z0-9\s]/g, '').trim().replace(/\s+/g, '-'))
                  .join('-')
                  .toUpperCase();
                const regex = new RegExp('-' + suffix + '$', 'i');
                if (regex.test(baseSku)) {
                  baseSku = baseSku.replace(regex, '');
                }
              }
            }
            return baseSku;
          })(),
          stock: product.stock,
          desc: product.desc || '',
          status: product.status,
          emoji: product.emoji || '📦',
          image: product.image || '',
          imageFile: null,
          gallery: product.gallery ? product.gallery.map(img => img.startsWith('http') ? img : '' + img) : [],
          galleryFiles: [],
          deletedGalleryPaths: [],
          deletePrimaryImage: false,
          thuoc_tinh: formThuocTinh,
          bien_the: formBienThe
        };
        // Reconstruct tagImages từ các biến thể đã có ảnh (cho nhóm thuộc tính đầu tiên)
        if (this.form.thuoc_tinh.length > 0 && this.form.bien_the.length > 0) {
          const firstAttr = this.form.thuoc_tinh[0];
          const firstAttrNormKey = this.normalizeAttributeKey(firstAttr.ten_thuoc_tinh);
          const tagImages = {};
          this.form.bien_the.forEach(bt => {
            if (bt.thuoc_tinh) {
              const firstVal = bt.thuoc_tinh[firstAttrNormKey];
              if (firstVal && bt.imagePreview && !tagImages[firstVal]) {
                tagImages[firstVal] = {
                  imageFile: null,
                  imagePreview: bt.imagePreview,
                  existingImage: bt.existingImage,
                };
              }
            }
          });
          firstAttr.tagImages = tagImages;
        }
        // Ghép domain cho ảnh từ storage
        if (this.form.image && !this.form.image.startsWith('http') && !this.form.image.startsWith('data:')) {
            this.form.image = '' + this.form.image;
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
          deletePrimaryImage: false,
          thuoc_tinh: [],
          bien_the: []
        };
      }
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveProduct() {
      this.hasSubmitted = true;
      if (this.hasVariants) {
        let hasError = false;
        this.form.bien_the.forEach(bt => {
          if (bt.price === '' || bt.price === null || bt.price === undefined || bt.price < 0 ||
              bt.stock === '' || bt.stock === null || bt.stock === undefined || bt.stock < 0) {
            hasError = true;
          }
        });

        if (hasError) {
          this.showToast('Vui lòng nhập đầy đủ giá bán và tồn kho hợp lệ (>= 0) cho tất cả các biến thể!', 'error');
          return;
        }

        // Tự động đồng bộ price của sản phẩm gốc bằng giá thấp nhất của biến thể
        // và stock bằng tổng stock của các biến thể để tránh lỗi validation ở FormRequest
        this.form.price = Math.min(...this.form.bien_the.map(b => b.price || 0));
        const totalStock = this.form.bien_the.reduce((acc, curr) => acc + (parseInt(curr.stock) || 0), 0);
        this.form.stock = totalStock;
      }

      try {
        const formData = new FormData();
        formData.append('ten_san_pham', this.form.name);
        formData.append('gia_co_ban', this.form.price);
        if (this.form.priceOrig && this.form.priceOrig > 0) {
          formData.append('gia_goc', this.form.priceOrig);
        }
        formData.append('sku', this.form.sku);
        
        // Tính tổng tồn kho của các biến thể
        const totalStock = this.form.bien_the.reduce((acc, curr) => acc + (parseInt(curr.stock) || 0), 0);
        formData.append('so_luong_ton_kho', totalStock);
        formData.append('tinh_trang', this.form.status);

        // Gửi thông tin thuộc tính & biến thể dạng JSON
        const thuocTinhPayload = this.form.thuoc_tinh.map(tt => ({
          ten_thuoc_tinh: tt.ten_thuoc_tinh,
          gia_tri: tt.gia_tri
        }));
        const bienThePayload = this.form.bien_the.map(bt => ({
          id: bt.id || null,
          sku: bt.sku,
          price: bt.price,
          price_orig: bt.priceOrig || null,
          stock: bt.stock,
          thuoc_tinh: bt.thuoc_tinh,
          existing_image: bt.existingImage || null,
        }));
        formData.append('thuoc_tinh', JSON.stringify(thuocTinhPayload));
        formData.append('bien_the', JSON.stringify(bienThePayload));

        // Append ảnh biến thể (bien_the_images[index])
        this.form.bien_the.forEach((bt, idx) => {
          if (bt.imageFile) {
            formData.append(`bien_the_images[${idx}]`, bt.imageFile);
          }
        });

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
          const res = await axios.post('/api/quan-ly/san-pham/create', formData, config);
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
          const res = await axios.post('/api/quan-ly/san-pham/update', formData, config);
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
            const res = await axios.post(`/api/quan-ly/san-pham/delete`, { id: product.id }, this.getConfig());
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
    },
    suggestAttribute(name) {
      const exists = this.form.thuoc_tinh.some(tt => tt.ten_thuoc_tinh.toLowerCase() === name.toLowerCase());
      if (exists) {
        this.showToast(`Thuộc tính "${name}" đã tồn tại!`, 'warning');
        return;
      }
      const isFirst = this.form.thuoc_tinh.length === 0;
      this.form.thuoc_tinh.push({
        ten_thuoc_tinh: name,
        gia_tri: [],
        newTagInput: '',
        tagImages: isFirst ? {} : undefined,
      });
    },
    addCustomAttribute() {
      const name = this.customAttributeName.trim();
      if (!name) {
        this.showToast('Vui lòng nhập tên thuộc tính!', 'warning');
        return;
      }
      const exists = this.form.thuoc_tinh.some(tt => tt.ten_thuoc_tinh.toLowerCase() === name.toLowerCase());
      if (exists) {
        this.showToast(`Thuộc tính "${name}" đã tồn tại!`, 'warning');
        return;
      }
      const isFirst = this.form.thuoc_tinh.length === 0;
      this.form.thuoc_tinh.push({
        ten_thuoc_tinh: name,
        gia_tri: [],
        newTagInput: '',
        tagImages: isFirst ? {} : undefined,
      });
      this.customAttributeName = '';
    },
    removeAttributeGroup(idx) {
      this.form.thuoc_tinh.splice(idx, 1);
    },
    addTag(idx) {
      const attr = this.form.thuoc_tinh[idx];
      const val = attr.newTagInput.trim();
      if (!val) return;
      if (attr.gia_tri.includes(val)) {
        this.showToast(`Giá trị "${val}" đã tồn tại trong nhóm này!`, 'warning');
        return;
      }
      attr.gia_tri.push(val);
      // Nếu là nhóm đầu tiên, khởi tạo tagImages entry cho tag mới
      if (idx === 0) {
        if (!attr.tagImages) attr.tagImages = {};
        attr.tagImages[val] = { imageFile: null, imagePreview: '', existingImage: null };
      }
      attr.newTagInput = '';
    },
    removeTag(idx, tagIdx) {
      const attr = this.form.thuoc_tinh[idx];
      const tag = attr.gia_tri[tagIdx];
      // Nếu là nhóm đầu tiên, xóa tagImages tương ứng
      if (idx === 0 && attr.tagImages && attr.tagImages[tag]) {
        delete attr.tagImages[tag];
      }
      attr.gia_tri.splice(tagIdx, 1);
    },
    // Upload ảnh cho tag phân loại đầu tiên
    triggerTagImageUpload(attrIdx, tag) {
      this.activeTagUpload = { attrIdx, tag };
      this.$refs.tagImageInput.click();
    },
    onTagImageChange(e) {
      const file = e.target.files[0];
      if (!file || !this.activeTagUpload) return;
      if (file.size > 9 * 1024 * 1024) {
        this.showToast('Ảnh không được vượt quá 9MB!', 'error');
        e.target.value = '';
        return;
      }
      const { attrIdx, tag } = this.activeTagUpload;
      const attr = this.form.thuoc_tinh[attrIdx];
      if (!attr || !attr.tagImages) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        attr.tagImages[tag] = {
          imageFile: file,
          imagePreview: ev.target.result,
          existingImage: null,
        };
        // Cập nhật imagePreview trên các biến thể đã sinh ra có cùng tag này
        const normalizedAttrKey = this.normalizeAttributeKey(attr.ten_thuoc_tinh);
        this.form.bien_the.forEach(bt => {
          if (bt.thuoc_tinh && bt.thuoc_tinh[normalizedAttrKey] === tag) {
            bt.imageFile = file;
            bt.imagePreview = ev.target.result;
            bt.existingImage = null;
          }
        });
      };
      reader.readAsDataURL(file);
      e.target.value = '';
    },
    // Triggers variant image upload
    triggerVariantImageUpload(idx) {
      this.activeVariantUploadIdx = idx;
      this.$refs.variantImageInput.click();
    },
    onVariantImageChange(e) {
      const file = e.target.files[0];
      if (!file || this.activeVariantUploadIdx === null) return;
      if (file.size > 9 * 1024 * 1024) {
        this.showToast('Ảnh không được vượt quá 9MB!', 'error');
        e.target.value = '';
        return;
      }
      const bt = this.form.bien_the[this.activeVariantUploadIdx];
      if (!bt) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        bt.imageFile = file;
        bt.imagePreview = ev.target.result;
        bt.existingImage = null; // clear existing image when new file is uploaded
      };
      reader.readAsDataURL(file);
      e.target.value = '';
      this.activeVariantUploadIdx = null;
    },
    removeVariantImage(idx) {
      const bt = this.form.bien_the[idx];
      if (bt) {
        bt.imageFile = null;
        bt.imagePreview = '';
        bt.existingImage = null;
      }
    },
    normalizeAttributeKey(str) {
      if (!str) return '';
      return str
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9\s_]/g, '')
        .trim()
        .replace(/\s+/g, '_');
    },
    removeAccents(str) {
      if (!str) return '';
      return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-zA-Z0-9\s]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    },
    generateVariants() {
      const activeAttrs = this.form.thuoc_tinh.filter(attr => attr.gia_tri && attr.gia_tri.length > 0);
      const tagImages = (activeAttrs[0] && activeAttrs[0].tagImages) || {};
      
      if (activeAttrs.length === 0) {
        // Mặc định
        const existingDefault = this.form.bien_the.find(bt => {
          const tt = bt.thuoc_tinh;
          return !tt || Object.keys(tt).length === 0;
        });
        
        this.form.bien_the = [{
          id: existingDefault ? existingDefault.id : null,
          sku: existingDefault ? existingDefault.sku : this.form.sku || '',
          price: existingDefault ? existingDefault.price : this.form.price || 0,
          stock: existingDefault ? existingDefault.stock : this.form.stock || 0,
          thuoc_tinh: {},
          variant_name: 'Mặc định'
        }];
        return;
      }

      const cartesian = (arrays) => {
        return arrays.reduce((acc, curr) => {
          const res = [];
          acc.forEach(a => {
            curr.forEach(b => {
              res.push([...a, b]);
            });
          });
          return res;
        }, [[]]);
      };

      const tagArrays = activeAttrs.map(attr => attr.gia_tri);
      const combinations = cartesian(tagArrays);

      const newVariants = combinations.map((combo, index) => {
        const variantName = combo.join(' - ');
        
        const thuocTinhMap = {};
        combo.forEach((val, idx) => {
          const originalKey = activeAttrs[idx].ten_thuoc_tinh;
          const normalizedKey = this.normalizeAttributeKey(originalKey);
          thuocTinhMap[normalizedKey] = val;
        });

        const existing = this.form.bien_the.find(bt => {
          const btThuocTinh = typeof bt.thuoc_tinh === 'string' ? JSON.parse(bt.thuoc_tinh) : bt.thuoc_tinh;
          if (!btThuocTinh) return false;
          
          const keys1 = Object.keys(btThuocTinh);
          const keys2 = Object.keys(thuocTinhMap);
          if (keys1.length !== keys2.length) return false;
          return keys1.every(k => btThuocTinh[k] === thuocTinhMap[k]);
        });

        let suggestedSku = '';
        if (existing && existing.sku) {
          suggestedSku = existing.sku;
        } else {
          const baseSku = this.form.sku || '';
          const suffix = combo.map(val => this.removeAccents(val)).join('-').toUpperCase();
          suggestedSku = baseSku ? `${baseSku}-${suffix}` : suffix;
        }

        // Lấy ảnh từ tagImages theo tag đầu tiên (combo[0] = tag của nhóm thuộc tính đầu)
        const firstTagValue = combo[0];
        const tagImageData = (tagImages && tagImages[firstTagValue]) || { imageFile: null, imagePreview: '', existingImage: null };

        return {
          id: existing ? existing.id : null,
          variant_name: variantName,
          thuoc_tinh: thuocTinhMap,
          price: existing ? existing.price : (this.form.price || 0),
          priceOrig: existing ? existing.priceOrig : (this.form.priceOrig || null),
          stock: existing ? existing.stock : (this.form.stock || 0),
          sku: suggestedSku,
          imageFile: existing ? existing.imageFile : tagImageData.imageFile,
          imagePreview: existing ? existing.imagePreview : tagImageData.imagePreview,
          existingImage: existing ? existing.existingImage : tagImageData.existingImage,
        };
      });

      this.form.bien_the = newVariants;
    },
    goToNextStep() {
      if (this.currentStep === 1) {
        if (!this.form.name || !this.form.id_danh_muc || (!this.hasVariants && !this.form.price) || !this.form.sku) {
          this.showToast('Vui lòng điền đầy đủ các thông tin bắt buộc (*)', 'warning');
          return;
        }
        this.currentStep = 2;
      } else if (this.currentStep === 2) {
        // Tự động add tag nếu người dùng gõ chữ nhưng quên ấn Enter
        this.form.thuoc_tinh.forEach((attr, idx) => {
          if (attr.newTagInput && attr.newTagInput.trim()) {
            this.addTag(idx);
          }
        });

        const isMissingTags = this.form.thuoc_tinh.some(attr => !attr.gia_tri || attr.gia_tri.length === 0);
        if (isMissingTags) {
          const missingAttr = this.form.thuoc_tinh.find(attr => !attr.gia_tri || attr.gia_tri.length === 0);
          this.showToast(`Vui lòng nhập ít nhất một giá trị cho nhóm phân loại [${missingAttr.ten_thuoc_tinh}] hoặc xóa nhóm nếu không sử dụng`, 'error');
          return;
        }
        this.generateVariants();
        this.currentStep = 3;
      }
    },
    applyBulkValues() {
      if (this.bulkPrice === '' && this.bulkPriceOrig === '' && this.bulkStock === '') {
        this.showToast('Vui lòng nhập giá bán, giá gốc hoặc số lượng để áp dụng!', 'warning');
        return;
      }
      this.form.bien_the.forEach(bt => {
        if (this.bulkPrice !== '') {
          bt.price = parseInt(this.bulkPrice);
        }
        if (this.bulkPriceOrig !== '') {
          bt.priceOrig = parseInt(this.bulkPriceOrig);
        }
        if (this.bulkStock !== '') {
          bt.stock = parseInt(this.bulkStock);
        }
      });
      this.showToast('Đã áp dụng thành công cho tất cả các biến thể!', 'success');
      this.bulkPrice = '';
      this.bulkPriceOrig = '';
      this.bulkStock = '';
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/products.css";

/* Modal Width adjustment */
.modal-box.modal-xl {
  max-width: 1140px !important;
  width: 95% !important;
}
.modal-box.modal-lg {
  max-width: 880px !important;
  width: 95% !important;
}

/* Double-column Modal Layout */
.product-modal-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 16px;
  align-items: start;
}
.modal-form-left {
  display: flex;
  flex-direction: column;
}
.modal-media-right {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Media Card Styling */
.media-card {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
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
  gap: 8px;
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
  max-width: 1140px !important;
  max-height: 90vh !important;
  display: flex;
  flex-direction: column;
}
.view-detail-modal .modal-body {
  overflow-y: auto;
  flex: 1;
  padding: 24px;
}
.detail-section-title {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.mt-4 {
  margin-top: 8px !important;
}
.info-attrs-block {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.detail-attrs-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.detail-attr-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.detail-attr-name {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  min-width: 100px;
}
.detail-attr-values {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.detail-attr-val-badge {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  font-size: 12px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 6px;
}
.detail-variants-section {
  border-top: 1px solid #e2e8f0;
  padding-top: 12px;
  margin-top: 8px;
}
.detail-variants-table th {
  background: #f1f5f9 !important;
  color: #1e293b !important;
}
.detail-variants-table td {
  padding: 10px 12px !important;
}
.detail-variant-price {
  font-size: 14px;
  font-weight: 700;
  color: #D70018;
}
.product-detail-grid {
  display: grid;
  grid-template-columns: 1.1fr 1.3fr;
  gap: 16px;
  align-items: start;
}
.detail-media-panel {
  display: flex;
  flex-direction: column;
  gap: 8px;
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
  gap: 8px;
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
  gap: 8px;
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
  gap: 8px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 8px;
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

/* Steps Indicator */
.steps-indicator {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  background: #f8fafc;
  padding: 8px 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}
.step {
  display: flex;
  align-items: center;
  gap: 8px;
}
.step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #cbd5e1;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
  transition: all 0.3s;
}
.step-label {
  font-size: 14px;
  font-weight: 600;
  color: #64748b;
  transition: all 0.3s;
}
.step.active .step-num {
  background: #6366f1;
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}
.step.active .step-label {
  color: #0f172a;
}
.step.completed .step-num {
  background: #22c55e;
  color: #ffffff;
}
.step.completed .step-label {
  color: #22c55e;
}
.step-line {
  flex: 1;
  height: 2px;
  background: #cbd5e1;
  margin: 0 16px;
  transition: all 0.3s;
}
.step-line.active {
  background: #6366f1;
}

/* Step 2 Styles: Attributes Setup */
.attr-setup-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.suggestion-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding: 12px;
  background: rgba(99, 102, 241, 0.03);
  border: 1px solid rgba(99, 102, 241, 0.08);
  border-radius: 8px;
}
.suggestion-title {
  font-size: 12px;
  font-weight: 700;
  color: #6366f1;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.btn-suggest {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-suggest:hover {
  background: rgba(99, 102, 241, 0.05);
  border-color: #6366f1;
  color: #6366f1;
}
.custom-attr-input-wrap {
  display: flex;
  gap: 8px;
}
.input-custom-attr {
  flex: 1;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  transition: all 0.2s;
}
.input-custom-attr:focus {
  border-color: #6366f1;
  outline: none;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.btn-add-custom-attr {
  background: #0f172a;
  color: #ffffff;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-add-custom-attr:hover {
  background: #1e293b;
}

.attr-groups-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-top: 8px;
}
@media (max-width: 768px) {
  .attr-groups-list {
    grid-template-columns: 1fr;
  }
}
.attr-group-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.attr-group-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.attr-group-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}
.btn-delete-attr-group {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}
.btn-delete-attr-group:hover {
  color: #ef4444;
  background: #fef2f2;
}

.attr-tags-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 8px;
  align-items: center;
}
.attr-tag {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #334155;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-remove-tag {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 11px;
  padding: 2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}
.btn-remove-tag:hover {
  color: #ef4444;
  background: #fef2f2;
}
.input-new-tag {
  flex: 1;
  min-width: 150px;
  border: none;
  font-size: 13px;
  padding: 4px;
}
.input-new-tag:focus {
  outline: none;
}
.no-attr-hint {
  grid-column: span 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  background: #ffffff;
  text-align: center;
  color: #64748b;
  font-size: 14px;
}

/* Step 3 Styles: Variants Table & Bulk Apply */
.variants-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.bulk-apply-card {
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 12px 16px;
}
.bulk-apply-title {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  margin-bottom: 8px;
  letter-spacing: 0.02em;
}
.bulk-apply-inputs {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}
.bulk-input-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.bulk-input-group label {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}
.bulk-input-group input {
  width: 130px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 13px;
}
.btn-bulk-apply {
  background: #6366f1;
  color: #ffffff;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-bulk-apply:hover {
  background: #4f46e5;
}

.variants-table-wrap {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  max-height: 320px;
  overflow-y: auto;
}
.variants-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.variants-table th {
  background: #f8fafc;
  padding: 10px 12px;
  font-weight: 700;
  color: #334155;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.variants-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #e2e8f0;
  background: #ffffff;
}
.variants-table tr:last-child td {
  border-bottom: none;
}
.variant-name-cell {
  font-weight: 700;
  color: #0f172a;
}
.variants-table input {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 13px;
  transition: border-color 0.2s;
}
.variants-table input:focus {
  border-color: #6366f1;
  outline: none;
}

/* Compact layout overrides for modals */
.form-grid {
  gap: 8px !important;
}
.form-group {
  gap: 4px !important;
}
.modal-body {
  padding: 12px 16px !important;
}
.modal-header {
  padding: 12px 16px 8px !important;
}
.modal-footer {
  padding: 8px 16px !important;
}
.disabled-field input {
  opacity: 0.6 !important;
  background-color: #f1f5f9 !important;
  cursor: not-allowed !important;
}
.border-red-500 {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 1px #ef4444 !important;
}

/* ── Tag image upload (Bước 2) ── */
.attr-group-hint {
  font-size: 11px;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.08);
  padding: 2px 8px;
  border-radius: 100px;
  margin-right: auto;
}
.attr-tag-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #f0f4ff;
  border: 1.5px solid #c7d2fe;
  border-radius: 20px;
  padding: 4px 8px 4px 4px;
  font-size: 13px;
  font-weight: 600;
  color: #3730a3;
  cursor: default;
}
.tag-img-slot {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: 1.5px dashed #6366f1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: pointer;
  background: #eef2ff;
  transition: border-color 0.2s, transform 0.15s;
  flex-shrink: 0;
}
.tag-img-slot:hover {
  border-color: #4f46e5;
  transform: scale(1.08);
}
.tag-img-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
.tag-img-icon {
  font-size: 12px;
}
.attr-tag-label {
  line-height: 1;
}

/* ── Variant image column (Bước 3) ── */
.variant-img-cell {
  text-align: center;
  padding: 4px !important;
}
.variant-img-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  margin: 0 auto;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  overflow: hidden;
}
.variant-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 6px;
}
.variant-no-img {
  color: #6366f1;
  font-size: 14px;
}
.variant-img-container {
  position: relative;
  width: 40px;
  height: 40px;
  margin: 0 auto;
}
.variant-img-preview.clickable {
  cursor: pointer;
  border-style: dashed;
  border-color: #6366f1;
  transition: all 0.2s ease-in-out;
}
.variant-img-preview.clickable:hover {
  border-color: #4f46e5;
  background: #eef2ff;
  transform: scale(1.05);
}
.btn-remove-variant-img {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.8);
  color: #ffffff;
  border: none;
  font-size: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  z-index: 5;
}
.btn-remove-variant-img:hover {
  background: #D70018;
}
</style>
