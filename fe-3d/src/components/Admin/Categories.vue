<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý danh mục</h1>
        <p class="page-sub">Phân loại sản phẩm, quản lý nhóm mô hình và thứ tự hiển thị</p>
      </div>
      <button class="btn-primary" @click="openModal('add')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm danh mục
      </button>
    </div>

    <!-- Stats Row -->
    <div class="mini-stats">
      <div class="mini-stat" v-for="s in miniStats" :key="s.label" :style="{ background: s.bg }">
        <div class="ms-icon-side" :style="{ color: s.color }">
          <svg v-if="s.icon === 'folder'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          </svg>
          <svg v-else-if="s.icon === 'check-circle'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <svg v-else-if="s.icon === 'eye-off'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
          </svg>
          <svg v-else-if="s.icon === 'star'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
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
          <input v-model="search" type="text" placeholder="Tìm theo tên, slug, mô tả..." />
        </div>
        <div class="toolbar-right">
          <select v-model="filterStatus" class="sel">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Đang hiển thị</option>
            <option value="hidden">Đang ẩn</option>
          </select>
          <button class="btn-outline" @click="exportExcel">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Xuất Excel
          </button>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th width="40"><input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" /></th>
            <th>Danh mục</th>
            <th>Mã danh mục (Slug)</th>
            <th>Thứ tự hiển thị</th>
            <th>Số lượng SP</th>
            <th>Trạng thái</th>
            <th width="120">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredCategories.length === 0">
            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
              <p>Không tìm thấy danh mục nào phù hợp</p>
            </td>
          </tr>
          <tr v-for="c in filteredCategories" :key="c.id">
            <td><input type="checkbox" :value="c.id" v-model="selectedIds" /></td>
            <td>
              <div class="category-cell">
                <div class="category-emoji">{{ c.emoji || '📁' }}</div>
                <div class="category-info">
                  <span class="category-name">{{ c.name }}</span>
                  <span class="category-desc" :title="c.desc">{{ c.desc || 'Không có mô tả chi tiết' }}</span>
                </div>
              </div>
            </td>
            <td>
              <code class="category-slug">{{ c.slug }}</code>
            </td>
            <td>
              <span class="order-badge">#{{ c.orderIndex }}</span>
            </td>
            <td>
              <span class="product-count-badge">{{ c.productCount }} sản phẩm</span>
            </td>
            <td>
              <span class="status-pill" :class="c.status === 'active' ? 's-active' : 's-hidden'">
                {{ c.status === 'active' ? 'Hiển thị' : 'Đang ẩn' }}
              </span>
            </td>
            <td>
              <div class="action-btns">
                <button class="act-btn edit" @click="openModal('edit', c)" title="Sửa">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="act-btn del" @click="confirmDelete(c)" title="Xoá">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Footer table info -->
      <div class="table-footer">
        <span class="table-count">Hiển thị {{ filteredCategories.length }} / {{ categories.length }} danh mục</span>
        <div class="pagination">
          <button class="pg-btn" disabled>&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn" disabled>&gt;</button>
        </div>
      </div>
    </div>

    <!-- Modal Thêm/Sửa danh mục -->
    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2>{{ modalMode === 'add' ? 'Thêm danh mục mới' : 'Chỉnh sửa danh mục' }}</h2>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <form @submit.prevent="saveForm">
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group span-2">
                <label>Tên danh mục <span class="req">*</span></label>
                <input type="text" v-model="form.name" @input="onNameInput" required placeholder="VD: Xe mô hình, Mô hình Anime..." />
              </div>
              <div class="form-group">
                <label>Emoji đại diện <span class="req">*</span></label>
                <input type="text" v-model="form.emoji" required placeholder="VD: 🤖, 🏎️, 🐉" style="text-align: center; font-size: 16px;" maxlength="4" />
              </div>
              <div class="form-group">
                <label>Thứ tự hiển thị <span class="req">*</span></label>
                <input type="number" v-model.number="form.orderIndex" required min="1" placeholder="1" />
              </div>
              <div class="form-group span-2">
                <label>Mã danh mục (Slug) <span class="req">*</span></label>
                <input type="text" v-model="form.slug" required placeholder="VD: xe-mo-hinh-tinh" />
              </div>
              <div class="form-group span-2">
                <label>Mô tả chi tiết</label>
                <textarea v-model="form.desc" rows="3" placeholder="Mô tả tóm tắt để người quản lý hoặc khách hàng dễ hiểu..."></textarea>
              </div>
              <div class="form-group span-2">
                <label>Trạng thái</label>
                <div class="radio-group">
                  <label class="radio-opt">
                    <input type="radio" v-model="form.status" value="active" /> Hiển thị ở cửa hàng
                  </label>
                  <label class="radio-opt">
                    <input type="radio" v-model="form.status" value="hidden" /> Tạm ẩn danh mục
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-ghost" @click="showModal = false">Hủy</button>
            <button type="submit" class="btn-primary">
              {{ modalMode === 'add' ? 'Tạo danh mục' : 'Lưu thay đổi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import * as XLSX from 'xlsx';

export default {
  name: "AdminCategories",
  data() {
    return {
      search: "",
      filterStatus: "",
      showModal: false,
      modalMode: "add",
      selectedIds: [],
      form: {
        id: null,
        name: "",
        emoji: "📁",
        slug: "",
        desc: "",
        orderIndex: 1,
        productCount: 0,
        status: "active"
      },
      categories: []
    };
  },
  mounted() {
    this.fetchCategories();
  },
  computed: {
    totalCategories() {
      return this.categories.length;
    },
    activeCategories() {
      return this.categories.filter(c => c.status === "active").length;
    },
    hiddenCategories() {
      return this.categories.filter(c => c.status === "hidden").length;
    },
    topCategory() {
      if (this.categories.length === 0) return "Trống";
      const sorted = [...this.categories].sort((a, b) => b.productCount - a.productCount);
      return sorted[0].name;
    },
    miniStats() {
      return [
        { val: this.totalCategories, label: "Tổng danh mục", icon: "folder", bg: "linear-gradient(135deg, #6366f1, #8b5cf6)", color: "#ffffff" },
        { val: this.activeCategories, label: "Đang hoạt động", icon: "check-circle", bg: "linear-gradient(135deg, #22c55e, #10b981)", color: "#ffffff" },
        { val: this.hiddenCategories, label: "Đang ẩn", icon: "eye-off", bg: "linear-gradient(135deg, #f43f5e, #ff4d5e)", color: "#ffffff" },
        { val: this.topCategory, label: "Nhiều sản phẩm nhất", icon: "star", bg: "linear-gradient(135deg, #f59e0b, #fbbf24)", color: "#ffffff" }
      ];
    },
    filteredCategories() {
      return this.categories.filter(c => {
        const matchSearch = !this.search || 
          c.name.toLowerCase().includes(this.search.toLowerCase()) || 
          c.slug.toLowerCase().includes(this.search.toLowerCase()) || 
          (c.desc && c.desc.toLowerCase().includes(this.search.toLowerCase()));
        
        const matchStatus = !this.filterStatus || c.status === this.filterStatus;
        return matchSearch && matchStatus;
      }).sort((a, b) => a.orderIndex - b.orderIndex);
    },
    isAllSelected() {
      return this.filteredCategories.length > 0 && this.selectedIds.length === this.filteredCategories.length;
    }
  },
  methods: {
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
    slugify(text) {
      return text
        .toString()
        .toLowerCase()
        .replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/g, "a")
        .replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/g, "e")
        .replace(/i|í|ì|ỉ|ĩ|ị/g, "i")
        .replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/g, "o")
        .replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/g, "u")
        .replace(/ý|ỳ|ỷ|ỹ|ỵ/g, "y")
        .replace(/đ/g, "d")
        .replace(/\s+/g, "-") // thay thế khoảng trắng bằng dấu -
        .replace(/[^\w-]+/g, "") // xóa ký tự đặc biệt
        .replace(/--+/g, "-") // thu gọn nhiều dấu - liên tiếp
        .replace(/^-+/, "") // xóa dấu - ở đầu
        .replace(/-+$/, ""); // xóa dấu - ở cuối
    },
    onNameInput() {
      if (this.modalMode === "add") {
        this.form.slug = this.slugify(this.form.name);
      }
    },
    async fetchCategories() {
      try {
        const config = {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('token_admin')
          }
        };
        const res = await axios.get('/api/quan-ly/danh-muc/data', config);
        if (res.data.status) {
          this.categories = res.data.data;
        }
      } catch (error) {
        this.showToast("Không thể tải danh sách danh mục!", "error");
      }
    },
    openModal(mode, category = null) {
      this.modalMode = mode;
      if (category) {
        this.form = { ...category };
      } else {
        const nextOrder = this.categories.length > 0 
          ? Math.max(...this.categories.map(c => c.orderIndex)) + 1 
          : 1;
        this.form = {
          id: null,
          name: "",
          emoji: "📁",
          slug: "",
          desc: "",
          orderIndex: nextOrder,
          productCount: 0,
          status: "active"
        };
      }
      this.showModal = true;
    },
    async saveForm() {
      try {
        const payload = {
          ten_danh_muc: this.form.name,
          duong_dan_mau: this.form.slug,
          emoji: this.form.emoji,
          mo_ta: this.form.desc,
          thu_tu_hien_thi: this.form.orderIndex,
          trang_thai: this.form.status
        };

        const config = {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('token_admin')
          }
        };

        if (this.modalMode === "add") {
          const res = await axios.post('/api/quan-ly/danh-muc/create', payload, config);
          if (res.data.status) {
            this.showToast(`Đã thêm danh mục "${this.form.name}" thành công!`);
            this.fetchCategories();
            this.showModal = false;
          } else {
            this.showToast(res.data.message, "error");
          }
        } else {
          payload.id = this.form.id;
          const res = await axios.post('/api/quan-ly/danh-muc/update', payload, config);
          if (res.data.status) {
            this.showToast(`Cập nhật danh mục "${this.form.name}" thành công!`, "info");
            this.fetchCategories();
            this.showModal = false;
          } else {
            this.showToast(res.data.message, "error");
          }
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          Object.keys(errors).forEach(key => {
            errors[key].forEach(msg => this.showToast(msg, "error"));
          });
        } else {
          this.showToast("Có lỗi xảy ra, vui lòng thử lại!", "error");
        }
      }
    },
    confirmDelete(category) {
      window.Swal.fire({
        title: 'XÓA DANH MỤC NÀY?',
        html: `Bạn có chắc chắn muốn xóa danh mục <b style="color: #D70018;">"${category.name}"</b>?<br><span style="font-size: 13px; color: #64748b;">Hành động này không thể hoàn tác và tất cả sản phẩm thuộc nhóm này sẽ cần được phân loại lại.</span>`,
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
            const config = {
              headers: {
                Authorization: 'Bearer ' + localStorage.getItem('token_admin')
              }
            };
            const res = await axios.post('/api/quan-ly/danh-muc/delete', { id: category.id }, config);
            if (res.data.status) {
              this.showToast("Xóa danh mục thành công!", "danger");
              this.fetchCategories();
            } else {
              this.showToast(res.data.message, "error");
            }
          } catch (error) {
            this.showToast("Không thể xóa danh mục!", "error");
          }
        }
      });
    },
    toggleSelectAll(e) {
      if (e.target.checked) {
        this.selectedIds = this.filteredCategories.map(c => c.id);
      } else {
        this.selectedIds = [];
      }
    },
    exportExcel() {
      let dataToExport = [];
      
      // Nếu có chọn checkbox, xuất những mục được chọn
      if (this.selectedIds.length > 0) {
        dataToExport = this.categories.filter(c => this.selectedIds.includes(c.id));
      } else {
        // Ngược lại, xuất những mục đang được hiển thị theo bộ lọc
        dataToExport = this.filteredCategories;
      }

      if (dataToExport.length === 0) {
        this.showToast("Không có dữ liệu danh mục để xuất!", "warning");
        return;
      }

      this.showToast("Bắt đầu xuất file Excel... Tải xuống sẽ bắt đầu sau giây lát.");

      // Map dữ liệu sang định dạng Excel tiếng Việt
      const excelRows = dataToExport.map(c => ({
        "Biểu tượng (Emoji)": c.emoji || "📁",
        "Tên danh mục": c.name,
        "Mã danh mục (Slug)": c.slug,
        "Thứ tự hiển thị": c.orderIndex,
        "Số lượng sản phẩm": c.productCount || 0,
        "Trạng thái": c.status === "active" ? "Hiển thị" : "Đang ẩn",
        "Mô tả": c.desc || "Không có mô tả chi tiết"
      }));

      // Tạo worksheet và workbook
      const worksheet = XLSX.utils.json_to_sheet(excelRows);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Danh sách danh mục");

      // Căn chỉnh độ rộng cột tự động dựa trên độ dài nội dung dài nhất trong mỗi cột
      const maxColsWidth = [];
      excelRows.forEach(row => {
        Object.keys(row).forEach((key, colIndex) => {
          const val = row[key];
          // Đối với tiếng Việt có dấu, độ dài ký tự thô có thể khác nhưng .length là đủ dùng ở mức cơ bản
          const cellLength = val ? val.toString().length : 0;
          const keyLength = key.length;
          const maxLength = Math.max(cellLength, keyLength);
          
          if (!maxColsWidth[colIndex] || maxLength > maxColsWidth[colIndex]) {
            maxColsWidth[colIndex] = maxLength;
          }
        });
      });

      // Gán độ rộng cột (thêm một khoảng đệm nhỏ)
      worksheet["!cols"] = maxColsWidth.map(w => ({ w: w + 5 }));

      // Xuất file và tải về
      XLSX.writeFile(workbook, "Danh_sach_danh_muc.xlsx");
      this.showToast("Xuất file Excel thành công!");
    }
  }
};
</script>

<style scoped>
@import "../../../public/style_admin/categories.css";
</style>
