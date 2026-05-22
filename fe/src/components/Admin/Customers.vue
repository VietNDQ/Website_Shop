<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý khách hàng</h1>
        <p class="page-sub">Hồ sơ, lịch sử mua hàng và kiểm duyệt đánh giá</p>
      </div>
    </div>

    <!-- Tabs: Danh sách / Đánh giá -->
    <div class="cus-tabs">
      <button class="cus-tab" :class="{ active: tab === 'list' }" @click="tab = 'list'">Danh sách khách hàng</button>
      <button class="cus-tab" :class="{ active: tab === 'reviews' }" @click="tab = 'reviews'">Đánh giá & Bình luận</button>
    </div>

    <!-- Danh sách khách hàng -->
    <div v-if="tab === 'list'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="search" type="text" placeholder="Tìm theo tên, email, SĐT..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="filterGroup">
            <option value="">Tất cả nhóm</option>
            <option>Khách mới</option>
            <option>Khách thân thiết</option>
            <option>Khách VIP</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th style="color:black">Khách hàng</th>
            <th style="color:black">Email</th>
            <th style="color:black">SĐT</th>
            <th style="color:black">Tổng đơn</th>
            <th style="color:black">Tổng chi tiêu</th>
            <th style="color:black">Nhóm</th>
            <th style="color:black">Ngày đăng ký</th>
            <th style="color:black">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredCustomers" :key="c.id" @click="openProfile(c)" style="cursor:pointer">
            <td>
              <div class="customer-cell">
                <div class="cus-avatar" :style="{ background: c.avatarBg }">{{ c.name[0] }}</div>
                <div>
                  <p class="cus-name">{{ c.name }}</p>
                  <p class="cus-phone">ID: #{{ c.id }}</p>
                </div>
              </div>
            </td>
            <td>{{ c.email }}</td>
            <td>{{ c.phone }}</td>
            <td><strong>{{ c.orders }}</strong> đơn</td>
            <td><span class="price-red">{{ c.spent }}</span></td>
            <td><span class="group-badge" :class="'g-' + c.group">{{ c.group }}</span></td>
            <td>{{ c.joinDate }}</td>
            <td>
              <div class="action-btns">
                <button class="act-btn view" title="Xem hồ sơ">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-count">{{ filteredCustomers.length }} khách hàng</span>
        <div class="pagination">
          <button class="pg-btn">&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn">2</button>
          <button class="pg-btn">&gt;</button>
        </div>
      </div>
    </div>

    <!-- Đánh giá & bình luận -->
    <div v-if="tab === 'reviews'" class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" v-model="adminReviewFilters.search" @input="fetchReviewsAdmin(1)" placeholder="Tìm theo sản phẩm, khách hàng..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="adminReviewFilters.sao" @change="fetchReviewsAdmin(1)">
            <option value="">Tất cả sao</option>
            <option value="5">5 sao</option>
            <option value="4">4 sao</option>
            <option value="3">3 sao</option>
            <option value="2">2 sao</option>
            <option value="1">1 sao</option>
          </select>
          <select class="sel" v-model="adminReviewFilters.trang_thai" @change="fetchReviewsAdmin(1)">
            <option value="">Trạng thái</option>
            <option value="hien_thi">Đã duyệt (Hiện)</option>
            <option value="cho_duyet">Chờ duyệt</option>
            <option value="an">Đã ẩn</option>
          </select>
        </div>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Khách hàng</th>
            <th>Sản phẩm</th>
            <th>Đánh giá</th>
            <th>Nội dung</th>
            <th>Ngày</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reviews" :key="r.id">
            <td>
              <strong>{{ r.customer }}</strong>
            </td>
            <td>
              <div>{{ r.product }}</div>
              <div style="font-size: 11px; color: #64748b; font-weight: 500;" v-if="r.variant">
                PL: {{ r.variant }}
              </div>
            </td>
            <td>
              <div class="stars" style="color: #eab308; font-size: 13px;">
                <span v-for="i in 5" :key="i" :style="{ color: i <= r.stars ? '#eab308' : '#cbd5e1' }">★</span>
              </div>
            </td>
            <td class="review-text">
              <div>{{ r.content || '(Không có bình luận)' }}</div>
              <!-- Images -->
              <div v-if="r.images && r.images.length > 0" style="display: flex; gap: 4px; margin-top: 6px;">
                <img v-for="(img, idx) in r.images" :key="idx" :src="img" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid #e2e8f0;" />
              </div>
              <!-- Shop reply -->
              <div v-if="r.reply" style="font-size: 12px; color: #db2777; background: rgba(219, 39, 119, 0.04); border-left: 2px solid #db2777; padding: 4px 8px; border-radius: 0 4px 4px 0; margin-top: 6px; white-space: pre-line;">
                <strong>Phản hồi:</strong> {{ r.reply }}
              </div>
            </td>
            <td>{{ r.date }}</td>
            <td>
              <span class="status-pill" :class="'s-' + r.status">
                {{ r.statusLabel }}
              </span>
            </td>
            <td>
              <div class="action-btns" style="display: flex; gap: 6px;">
                <!-- Phản hồi -->
                <button class="act-btn edit" title="Phản hồi" @click="openReplyModal(r)" style="background: #e0f2fe; color: #0369a1; border: none; padding: 6px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                  <i class="fa-solid fa-reply"></i>
                </button>
                <!-- Toggle Duyệt / Hiện / Ẩn -->
                <button 
                  v-if="r.status !== 'hien_thi'" 
                  class="act-btn edit" 
                  title="Duyệt hiển thị" 
                  @click="updateReviewStatus(r, 'hien_thi')"
                  style="background: #dcfce7; color: #15803d; border: none; padding: 6px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                >
                  <i class="fa-solid fa-eye"></i>
                </button>
                <button 
                  v-if="r.status === 'hien_thi'" 
                  class="act-btn del" 
                  title="Ẩn đánh giá" 
                  @click="updateReviewStatus(r, 'an')"
                  style="background: #fee2e2; color: #b91c1c; border: none; padding: 6px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                >
                  <i class="fa-solid fa-eye-slash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="reviews.length === 0">
            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px 0;">Chưa có đánh giá nào phù hợp.</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination for Admin Reviews -->
      <div class="table-footer" v-if="adminReviewPagination.last_page > 1" style="display: flex; justify-content: flex-end; padding: 12px 24px; border-top: 1px solid #e2e8f0; background: #fff; border-radius: 0 0 8px 8px;">
        <div class="pg-wrap" style="display: flex; align-items: center; gap: 8px;">
          <button 
            class="pg-btn" 
            :disabled="adminReviewPagination.current_page === 1" 
            @click="fetchReviewsAdmin(adminReviewPagination.current_page - 1)"
            style="cursor: pointer; padding: 4px 8px;"
          >&lt;</button>
          <span style="font-size: 13px; color: #475569;">Trang {{ adminReviewPagination.current_page }} / {{ adminReviewPagination.last_page }}</span>
          <button 
            class="pg-btn" 
            :disabled="adminReviewPagination.current_page === adminReviewPagination.last_page" 
            @click="fetchReviewsAdmin(adminReviewPagination.current_page + 1)"
            style="cursor: pointer; padding: 4px 8px;"
          >&gt;</button>
        </div>
      </div>
    </div>

    <!-- Profile modal -->
    <div class="modal-overlay" v-if="showProfile" @click.self="showProfile = false">
      <div class="modal-box modal-lg" v-if="selectedCustomer">
        <div class="modal-header">
          <h2>Hồ sơ: {{ selectedCustomer.name }}</h2>
          <button class="modal-close" @click="showProfile = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="profile-top">
            <div class="profile-avatar-lg" :style="{ background: selectedCustomer.avatarBg }">{{ selectedCustomer.name[0] }}</div>
            <div>
              <h3 class="profile-name">{{ selectedCustomer.name }}</h3>
              <p class="profile-email">{{ selectedCustomer.email }}</p>
              <span class="group-badge" :class="'g-' + selectedCustomer.group">{{ selectedCustomer.group }}</span>
            </div>
          </div>
          <div class="profile-stats">
            <div class="ps-item"><p style="color:red" class="ps-val">{{ selectedCustomer.orders }}</p><p class="ps-lbl">Tổng đơn hàng</p></div>
            <div class="ps-item"><p style="color:red" class="ps-val price-red">{{ selectedCustomer.spent }}</p><p class="ps-lbl">Tổng chi tiêu</p></div>
            <div class="ps-item"><p class="ps-val">{{ selectedCustomer.joinDate }}</p><p class="ps-lbl">Ngày đăng ký</p></div>
          </div>
          <h4 class="detail-sec-title" style="margin-top:16px">Lịch sử mua hàng gần đây</h4>
          <table class="data-table" style="margin-top:8px">
            <thead><tr><th style="color:black">Mã đơn</th><th style="color:black">Sản phẩm</th><th style="color:black">Tổng</th><th style="color:black">Ngày</th><th style="color:black">TT</th></tr></thead>
            <tbody>
              <tr v-for="order in selectedCustomer.orderHistory" :key="order.code">
                <td>{{ order.code }}</td>
                <td>{{ order.products }}</td>
                <td style="color:red">{{ order.total }}</td>
                <td>{{ order.date }}</td>
                <td class="whitespace-nowrap"><span class="status-pill" :class="'s-' + order.status">{{ order.statusLabel }}</span></td>
              </tr>
              <tr v-if="!selectedCustomer.orderHistory || selectedCustomer.orderHistory.length === 0">
                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 12px 0;">Chưa có lịch sử mua hàng.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showProfile = false">Đóng</button>
        </div>
      </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal-overlay" v-if="showReplyModal" @click.self="showReplyModal = false">
      <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
          <h2>Phản hồi đánh giá</h2>
          <button class="modal-close" @click="showReplyModal = false">✕</button>
        </div>
        <div class="modal-body" v-if="selectedReview">
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
            <p style="margin: 0 0 6px 0; font-size: 13px; color: #64748b;">
              <strong>Khách hàng:</strong> {{ selectedReview.customer }} | <strong>Đánh giá:</strong> {{ selectedReview.stars }} ★
            </p>
            <p style="margin: 0; font-size: 13.5px; color: #334155; font-style: italic;">
              "{{ selectedReview.content || '(Không có bình luận)' }}"
            </p>
          </div>
          
          <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
            <label style="font-weight: 600; font-size: 13px; color: #334155;">Nội dung phản hồi của Cửa hàng</label>
            <textarea 
              v-model="replyContent" 
              rows="5" 
              placeholder="Nhập nội dung phản hồi khách hàng..." 
              style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; font-family: inherit; resize: vertical;"
              maxlength="1000"
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showReplyModal = false">Hủy</button>
          <button class="btn-ghost" style="background: #d70018; color: #fff; border-color: #d70018;" @click="submitReply" :disabled="submittingReply">
            {{ submittingReply ? 'Đang gửi...' : 'Gửi phản hồi' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminCustomers',
  data() {
    return {
      tab: 'list', search: '', filterGroup: '',
      showProfile: false, selectedCustomer: null,
      customers: [],
      reviews: [],
      adminReviewFilters: { search: '', sao: '', trang_thai: '' },
      adminReviewPagination: { current_page: 1, last_page: 1 },
      adminReviewLoading: false,
      showReplyModal: false,
      selectedReview: null,
      replyContent: '',
      submittingReply: false,
    };
  },
  watch: {
    tab(newTab) {
      if (newTab === 'reviews') {
        this.fetchReviewsAdmin(1);
      }
    }
  },
  computed: {
    filteredCustomers() {
      return this.customers.filter(c => {
        const ms = !this.search || c.name.toLowerCase().includes(this.search.toLowerCase()) || c.email.includes(this.search);
        const mg = !this.filterGroup || c.group === this.filterGroup;
        return ms && mg;
      });
    },
  },
  mounted() {
    this.fetchCustomers();
  },
  methods: {
    getConfig() {
      return {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('token_admin'),
        }
      };
    },
    async fetchCustomers() {
      try {
        const res = await axios.get('/api/quan-ly/khach-hang/data', this.getConfig());
        if (res.data.status) {
          this.customers = res.data.data || [];
        }
      } catch (err) {
        if (this.$toast) {
          this.$toast.error('Lỗi tải danh sách khách hàng');
        } else {
          console.error(err);
        }
      }
    },
    openProfile(c) { this.selectedCustomer = c; this.showProfile = true; },
    async fetchReviewsAdmin(page = 1) {
      this.adminReviewLoading = true;
      try {
        const params = {
          page: page,
          search: this.adminReviewFilters.search,
          sao: this.adminReviewFilters.sao,
          trang_thai: this.adminReviewFilters.trang_thai
        };
        const res = await axios.get('/api/quan-ly/danh-gia', {
          params,
          ...this.getConfig()
        });
        if (res.data.status) {
          this.reviews = res.data.data.data || [];
          this.adminReviewPagination = {
            current_page: res.data.data.current_page || 1,
            last_page: res.data.data.last_page || 1
          };
        }
      } catch (err) {
        console.error(err);
        if (this.$toast) {
          this.$toast.error('Lỗi khi tải danh sách đánh giá');
        }
      } finally {
        this.adminReviewLoading = false;
      }
    },
    openReplyModal(r) {
      this.selectedReview = r;
      this.replyContent = r.reply || '';
      this.showReplyModal = true;
    },
    async submitReply() {
      if (!this.replyContent.trim()) {
        if (this.$toast) {
          this.$toast.error('Nội dung phản hồi không được để trống.');
        }
        return;
      }
      this.submittingReply = true;
      try {
        const res = await axios.post(`/api/quan-ly/danh-gia/${this.selectedReview.id}/phan-hoi`, {
          phan_hoi_admin: this.replyContent
        }, this.getConfig());
        if (res.data.status) {
          if (this.$toast) {
            this.$toast.success('Phản hồi đánh giá thành công.');
          }
          this.showReplyModal = false;
          this.fetchReviewsAdmin(this.adminReviewPagination.current_page);
        }
      } catch (err) {
        console.error(err);
        const msg = err.response?.data?.message || 'Lỗi khi gửi phản hồi';
        if (this.$toast) {
          this.$toast.error(msg);
        }
      } finally {
        this.submittingReply = false;
      }
    },
    async updateReviewStatus(r, newStatus) {
      try {
        const res = await axios.post(`/api/quan-ly/danh-gia/${r.id}/trang-thai`, {
          trang_thai: newStatus
        }, this.getConfig());
        if (res.data.status) {
          if (this.$toast) {
            this.$toast.success(newStatus === 'hien_thi' ? 'Đã hiển thị đánh giá' : 'Đã ẩn đánh giá');
          }
          this.fetchReviewsAdmin(this.adminReviewPagination.current_page);
        }
      } catch (err) {
        console.error(err);
        if (this.$toast) {
          this.$toast.error('Lỗi khi cập nhật trạng thái đánh giá');
        }
      }
    },
    openProfile(c) { this.selectedCustomer = c; this.showProfile = true; },
  },
};
</script>

<style scoped>
@import "../../../public/style_admin/customers.css";
</style>
