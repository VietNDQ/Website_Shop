<template>
  <div class="profile-page">
    <div class="profile-container">
      <!-- Left sidebar: Smember Menu -->
      <aside class="profile-sidebar">
        <!-- User short card -->
        <div class="user-card-sm">
          <div class="avatar-wrap">
            <img :src="userAvatar" alt="Avatar" class="avatar-img" />
            <label
              for="avatar-upload"
              class="avatar-upload-label"
              title="Thay đổi ảnh đại diện"
            >
              📷
            </label>
            <input
              id="avatar-upload"
              type="file"
              @change="handleAvatarChange"
              accept="image/*"
              class="hidden-input"
            />
          </div>
          <div class="user-meta">
            <h3 class="user-fullname">{{ user.name || "Khách hàng" }}</h3>
            <span class="member-badge" :class="memberTierClass">
              👑 {{ memberTierName }}
            </span>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <nav class="sidebar-nav">
          <button
            v-for="tab in sidebarTabs"
            :key="tab.id"
            class="nav-item"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            <span class="nav-icon"><i :class="tab.icon"></i></span>
            <span class="nav-text">{{ tab.label }}</span>
          </button>

          <button class="nav-item logout-btn-sidebar" @click="logoutUser">
            <span class="nav-icon"
              ><i class="fa-solid fa-right-from-bracket"></i
            ></span>
            <span class="nav-text">Đăng xuất</span>
          </button>
        </nav>
      </aside>

      <!-- Right main: Content Panel -->
      <main class="profile-content">
        <!-- LOADING STATE -->
        <div v-if="loading" class="content-loading">
          <i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải thông tin
          tài khoản...
        </div>

        <!-- TAB 1: SMEMBER DASHBOARD -->
        <div v-else-if="activeTab === 'dashboard'" class="tab-pane">
          <!-- Welcome Loyalty Card -->
          <div class="smember-rank-card">
            <div class="card-header-row">
              <div>
                <span class="smember-label">TRUY CẬP SMEMBER</span>
                <h2 class="smember-title">Xin chào, {{ user.name }}</h2>
              </div>
              <div class="member-logo">Smember</div>
            </div>

            <div class="loyalty-stats-grid">
              <div class="loyalty-stat">
                <div class="stat-num">{{ formatPoints(userPoints) }}</div>
                <div class="stat-label">Điểm tích lũy</div>
              </div>
              <div class="loyalty-stat">
                <div class="stat-num">{{ formatPrice(totalSpent) }}</div>
                <div class="stat-label">Tổng tiền đã mua</div>
              </div>
              <div class="loyalty-stat">
                <div class="stat-num">{{ memberTierName }}</div>
                <div class="stat-label">Hạng thành viên</div>
              </div>
            </div>

            <!-- Progress bar to next tier -->
            <div class="tier-progress-wrap" v-if="nextTierName">
              <div class="progress-labels">
                <span
                  >Hạng hiện tại: <strong>{{ memberTierName }}</strong></span
                >
                <span
                  >Cần thêm
                  <strong>{{ formatPrice(amountToNextTier) }}</strong> để lên
                  <strong>{{ nextTierName }}</strong></span
                >
              </div>
              <div class="progress-bar-bg">
                <div
                  class="progress-bar-fill"
                  :style="{ width: tierProgressPercent + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Quick Overview Cards -->
          <div class="dashboard-widgets">
            <!-- Email & Phone verification status -->
            <div class="widget-card">
              <h4>🛡️ Trạng thái xác thực</h4>
              <ul class="verification-list">
                <li>
                  <span class="v-label">Email:</span>
                  <span class="v-val">{{ user.email }}</span>
                  <span class="v-badge verified">✓ Đã xác thực</span>
                </li>
                <li>
                  <span class="v-label">Số điện thoại:</span>
                  <span class="v-val">{{ user.phone || "Chưa liên kết" }}</span>
                  <span
                    class="v-badge"
                    :class="user.phone ? 'verified' : 'unverified'"
                  >
                    {{ user.phone ? "✓ Đã liên kết" : "⚠️ Chưa liên kết" }}
                  </span>
                </li>
              </ul>
            </div>

            <!-- Profile Info summary -->
            <div class="widget-card">
              <h4>📅 Thông tin bổ sung</h4>
              <p>
                <strong>Ngày sinh:</strong>
                {{ formatDate(user.dob) || "Chưa cập nhật" }}
              </p>
              <p>
                <strong>Giới thiệu:</strong> {{ user.bio || "Chưa cập nhật" }}
              </p>
              <button class="widget-action-btn" @click="activeTab = 'profile'">
                Sửa thông tin hồ sơ
              </button>
            </div>
          </div>

          <!-- Recent Orders Widget -->
          <div class="recent-orders-widget">
            <div class="widget-header">
              <h3>📦 Đơn hàng gần đây</h3>
              <button class="view-all-link" @click="activeTab = 'orders'">
                Xem tất cả
              </button>
            </div>
            <div v-if="orders.length === 0" class="empty-widget-state">
              Bạn chưa có đơn đặt hàng nào.
            </div>
            <div v-else class="orders-list-sm">
              <div
                v-for="order in orders.slice(0, 2)"
                :key="order.id"
                class="order-item-sm"
              >
                <div class="o-meta">
                  <span class="o-code"
                    ><strong>{{ order.ma_don_hang }}</strong></span
                  >
                  <span class="o-date">{{ formatDate(order.tao_luc) }}</span>
                </div>
                <div class="o-price-status">
                  <span class="o-price">{{
                    formatPrice(order.tong_thanh_toan)
                  }}</span>
                  <span
                    class="status-badge"
                    :class="getOrderStatusClass(order.trang_thai)"
                  >
                    {{ getOrderStatusText(order.trang_thai) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: PROFILE PROFILE EDIT -->
        <div v-else-if="activeTab === 'profile'" class="tab-pane profile-details-pane">
          <!-- Card 1: Thông tin cá nhân -->
          <div class="profile-section-card">
            <div class="card-header-row-sm">
              <h3>Thông tin cá nhân</h3>
              <button class="btn-text-action" @click="openEditProfileModal">
                <i class="fa-solid fa-pen-to-square"></i> Cập nhật
              </button>
            </div>
            <div class="profile-info-grid">
              <div class="info-row">
                <div class="info-item">
                  <span class="info-label">Họ và tên:</span>
                  <span class="info-value bold">{{ user.name || '-' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Số điện thoại:</span>
                  <span class="info-value bold">{{ user.phone || '-' }}</span>
                </div>
              </div>
              <div class="info-row">
                <div class="info-item">
                  <span class="info-label">Giới tính:</span>
                  <span class="info-value">{{ user.bio || '-' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Email:</span>
                  <span class="info-value">{{ user.email || '-' }}</span>
                </div>
              </div>
              <div class="info-row">
                <div class="info-item">
                  <span class="info-label">Ngày sinh:</span>
                  <span class="info-value">{{ formatDate(user.dob) || '-' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Địa chỉ mặc định:</span>
                  <span class="info-value">{{ defaultAddressText || '-' }}</span>
                </div>
              </div>
            </div>
          </div>

          <hr class="profile-divider" />

          <!-- Card 2: Sổ địa chỉ -->
          <div class="profile-section-card">
            <div class="card-header-row-sm">
              <h3>Sổ địa chỉ</h3>
              <button class="btn-text-action" @click="openAddAddressModal">
                <i class="fa-solid fa-plus"></i> Thêm địa chỉ
              </button>
            </div>
            
            <div v-if="addresses.length === 0" class="empty-address-container">
              <i class="fa-solid fa-truck-fast empty-address-icon"></i>
              <p class="empty-address-text">Bạn chưa có địa chỉ nào được tạo</p>
            </div>
            
            <div v-else class="addresses-grid-container-new">
              <div
                v-for="addr in addresses"
                :key="addr.id"
                class="address-card-new"
                :class="{ 'default-card': addr.la_mac_dinh }"
              >
                <div class="addr-card-header">
                  <span class="addr-card-label">{{ parseAddressDisplay(addr).label }}</span>
                  <span class="addr-card-badge-type">
                    <i :class="parseAddressDisplay(addr).type === 'Nhà' ? 'fa-solid fa-house' : 'fa-solid fa-building'"></i>
                    {{ parseAddressDisplay(addr).type }}
                  </span>
                </div>
                <div class="addr-card-body">
                  <div class="addr-card-recipient-row">
                    <span class="recipient-name">{{ user.name }}</span>
                    <span class="separator-pipe">|</span>
                    <span class="recipient-phone">{{ addr.so_dien_thoai }}</span>
                    <span class="badge-default-addr-new" v-if="addr.la_mac_dinh">Mặc định</span>
                  </div>
                  <div class="addr-card-details">
                    {{ parseAddressDisplay(addr).fullAddress }}
                  </div>
                </div>
                <div class="addr-card-footer">
                  <button type="button" class="btn-card-action delete" @click="deleteAddress(addr.id)">
                    Xóa
                  </button>
                  <span class="separator-pipe-actions">|</span>
                  <button type="button" class="btn-card-action edit" @click="editAddress(addr)">
                    Cập nhật
                  </button>
                </div>
              </div>
            </div>
          </div>

          <hr class="profile-divider" />

          <!-- Card 3 & Card 4 Row -->
          <div class="profile-two-column">
            <!-- Left Card: Mật khẩu -->
            <div class="profile-section-card">
              <div class="card-header-row-sm">
                <h3>Mật khẩu</h3>
                <button class="btn-text-action" @click="openChangePasswordModal">
                  <i class="fa-solid fa-key"></i> Thay đổi mật khẩu
                </button>
              </div>
              <div class="password-info-body">
                <div class="info-item-single">
                  <span class="info-label">Cập nhật lần cuối lúc:</span>
                  <span class="info-value bold">{{ user.updated_at || 'Đã thiết lập' }}</span>
                </div>
              </div>
            </div>

            <!-- Right Card: Tài khoản liên kết -->
            <div class="profile-section-card">
              <div class="card-header-row-sm">
                <h3>Tài khoản liên kết</h3>
              </div>
              <div class="social-links-body">
                <div class="social-row-item">
                  <div class="social-left">
                    <img src="https://lh3.googleusercontent.com/COxit4t2yaZsFoTR26YtFcSEj2gO5tTyYusA2QLJa56OBxKDHj999xt5EC996Ea67XCc" alt="Google" class="social-logo" />
                    <span class="social-name">Google</span>
                    <span class="badge-linked">Đã liên kết</span>
                  </div>
                  <button class="btn-social-action unbind">Hủy liên kết</button>
                </div>
                <div class="social-row-item">
                  <div class="social-left">
                    <img src="https://uxwing.com/wp-content/themes/uxwing/download/brands-and-social-media/zalo-icon.png" alt="Zalo" class="social-logo" />
                    <span class="social-name">Zalo</span>
                  </div>
                  <button class="btn-social-action bind">Liên kết</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: ORDER HISTORY -->
        <div v-else-if="activeTab === 'orders'" class="tab-pane">
          <div class="content-header">
            <h2>Lịch sử mua hàng</h2>
            <p>Xem danh sách các đơn hàng bạn đã đặt và trạng thái giao hàng</p>
          </div>

          <div v-if="orders.length === 0" class="empty-state">
            <span class="empty-icon">📦</span>
            <h3>Chưa có đơn hàng nào</h3>
            <p>
              Hãy khám phá các sản phẩm tuyệt vời của chúng tôi và đặt hàng nhé!
            </p>
            <router-link to="/" class="btn-primary-inline"
              >Mua sắm ngay</router-link
            >
          </div>

          <div v-else class="orders-list">
            <div v-for="order in orders" :key="order.id" class="order-card">
              <div class="order-header-row">
                <div>
                  <span class="order-code-title"
                    >Đơn hàng: <strong>{{ order.ma_don_hang }}</strong></span
                  >
                  <span class="order-date-text"
                    >Ngày đặt: {{ formatDate(order.tao_luc) }} <span style="margin-left: 15px; color: #4b5563;">Thời gian: {{ formatTime(order.tao_luc) }}</span></span
                  >
                </div>
                <div>
                  <span
                    class="status-badge"
                    :class="getOrderStatusClass(order.trang_thai)"
                  >
                    {{ getOrderStatusText(order.trang_thai) }}
                  </span>
                </div>
              </div>

              <!-- Order items -->
              <div class="order-items">
                <div
                  v-for="detail in order.chi_tiets"
                  :key="detail.id"
                  class="order-item-row"
                >
                  <div class="item-info">
                    <span class="item-name">{{
                      detail.ten_bien_the_luc_mua
                    }}</span>
                    <span class="item-qty">x {{ detail.so_luong }}</span>
                  </div>
                  <span class="item-price">{{
                    formatPrice(detail.thanh_tien)
                  }}</span>
                </div>
              </div>

              <!-- Order footer price -->
              <div class="order-summary-footer">
                <div class="summary-details">
                  <div class="summary-line">
                    <span>Tổng tiền hàng:</span>
                    <span>{{ formatPrice(order.tong_tien_hang) }}</span>
                  </div>
                  <div class="summary-line" v-if="order.tien_duoc_giam > 0">
                    <span>Mã giảm giá:</span>
                    <span class="discount-txt"
                      >-{{ formatPrice(order.tien_duoc_giam) }}</span
                    >
                  </div>
                  <div class="summary-line">
                    <span>Phí vận chuyển:</span>
                    <span>{{ formatPrice(order.phi_giao_hang) }}</span>
                  </div>
                  <div class="summary-line total">
                    <span>Tổng thanh toán:</span>
                    <span class="total-price">{{
                      formatPrice(order.tong_thanh_toan)
                    }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="ordersPagination.last_page > 1" class="pagination-container">
              <button 
                type="button"
                class="btn-page" 
                :disabled="ordersPagination.current_page === 1"
                @click="loadOrders(ordersPagination.current_page - 1)"
              >
                ‹ Trước
              </button>
              <span class="page-info">Trang {{ ordersPagination.current_page }} / {{ ordersPagination.last_page }}</span>
              <button 
                type="button"
                class="btn-page" 
                :disabled="ordersPagination.current_page === ordersPagination.last_page"
                @click="loadOrders(ordersPagination.current_page + 1)"
              >
                Sau ›
              </button>
            </div>
          </div>
        </div>



        <!-- TAB 5: MY VOUCHERS -->
        <div v-else-if="activeTab === 'vouchers'" class="tab-pane">
          <div class="content-header">
            <h2>Ưu đãi của tôi</h2>
            <p>
              Danh sách các mã giảm giá đặc quyền và ưu đãi vận chuyển dành cho
              bạn
            </p>
          </div>

          <div class="vouchers-grid">
            <div
              v-for="voucher in vouchers"
              :key="voucher.code"
              class="voucher-card"
            >
              <div class="voucher-left">
                <span class="v-icon">🎟️</span>
              </div>
              <div class="voucher-right">
                <h3 class="v-title">{{ voucher.title }}</h3>
                <p class="v-desc">{{ voucher.desc }}</p>
                <div class="v-footer">
                  <span class="v-code"
                    >Code: <strong>{{ voucher.code }}</strong></span
                  >
                  <button class="btn-copy" @click="copyCode(voucher.code)">
                    Copy mã
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 6: WISHLIST & RECENTLY VIEWED -->
        <div v-else-if="activeTab === 'wishlist'" class="tab-pane">
          <div class="content-header">
            <h2>Sản phẩm yêu thích & Đã xem</h2>
            <p>Quản lý các sản phẩm bạn đã gắn tim hoặc vừa xem gần đây</p>
          </div>

          <div class="sub-tabs">
            <button
              class="sub-tab"
              :class="{ active: subTab === 'fav' }"
              @click="subTab = 'fav'"
            >
              ❤️ Yêu thích ({{ wishlist.length }})
            </button>
            <button
              class="sub-tab"
              :class="{ active: subTab === 'recent' }"
              @click="subTab = 'recent'"
            >
              👁️ Đã xem gần đây ({{ recentlyViewed.length }})
            </button>
          </div>

          <!-- Wishlist items -->
          <div v-if="subTab === 'fav'">
            <div v-if="wishlist.length === 0" class="empty-state">
              <span class="empty-icon">🤍</span>
              <h3>Danh sách yêu thích trống</h3>
              <p>Gắn tim các sản phẩm bạn thích để lưu lại tại đây nhé.</p>
            </div>
            <div v-else class="wishlist-grid">
              <div
                v-for="item in wishlist"
                :key="item.id"
                class="wishlist-item-card"
                @click="$router.push(`/product/${item.id}`)"
              >
                <div class="w-img-wrap">
                  <img :src="getProductImageUrl(item.image)" class="w-img" />
                </div>
                <h4 class="w-name">{{ item.name }}</h4>
                <div class="w-price-row">
                  <span class="w-price">{{ formatPrice(item.price) }}</span>
                  <button
                    class="btn-remove-fav"
                    @click.stop="removeFav(item.id)"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Recently Viewed items -->
          <div v-if="subTab === 'recent'">
            <div v-if="recentlyViewed.length === 0" class="empty-state">
              <span class="empty-icon">👁️</span>
              <h3>Chưa có sản phẩm đã xem</h3>
              <p>
                Khi bạn xem chi tiết các sản phẩm, chúng sẽ xuất hiện tại đây.
              </p>
            </div>
            <div v-else class="wishlist-grid">
              <div
                v-for="item in recentlyViewed"
                :key="item.id"
                class="wishlist-item-card"
                @click="$router.push(`/product/${item.id}`)"
              >
                <div class="w-img-wrap">
                  <img :src="getProductImageUrl(item.image)" class="w-img" />
                </div>
                <h4 class="w-name">{{ item.name }}</h4>
                <div class="w-price-row">
                  <span class="w-price">{{ formatPrice(item.price) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- EDIT PROFILE MODAL -->
    <div v-if="showEditProfileModal" class="modal-overlay-custom" @click.self="showEditProfileModal = false">
      <div class="modal-card-custom">
        <div class="modal-header-custom">
          <h3>Cập nhật thông tin cá nhân</h3>
          <button class="btn-close-modal" @click="showEditProfileModal = false">✕</button>
        </div>
        <form @submit.prevent="updateProfileInfo">
          <div class="modal-body-custom">
            <div class="form-group">
              <label>Họ và tên</label>
              <input
                type="text"
                v-model="profileForm.ho_ten"
                required
                placeholder="Nhập họ và tên"
              />
            </div>
            <div class="form-group">
              <label>Số điện thoại</label>
              <input
                type="text"
                v-model="profileForm.so_dien_thoai"
                required
                placeholder="Nhập số điện thoại"
              />
            </div>
            <div class="form-group">
              <label>Ngày sinh</label>
              <input type="date" v-model="profileForm.ngay_sinh" />
            </div>
            <div class="form-group">
              <label>Giới tính</label>
              <select v-model="profileForm.gioi_thieu" class="form-select-custom">
                <option value="">Chọn giới tính</option>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
                <option value="Khác">Khác</option>
              </select>
            </div>
          </div>
          <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-custom" @click="showEditProfileModal = false">Hủy bỏ</button>
            <button type="submit" class="btn-primary-custom" :disabled="submittingProfile">
              {{ submittingProfile ? "Đang lưu..." : "Lưu thay đổi" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div v-if="showChangePasswordModal" class="modal-overlay-custom" @click.self="showChangePasswordModal = false">
      <div class="modal-card-custom">
        <div class="modal-header-custom">
          <h3>Thay đổi mật khẩu</h3>
          <button class="btn-close-modal" @click="showChangePasswordModal = false">✕</button>
        </div>
        <form @submit.prevent="updatePassword">
          <div class="modal-body-custom">
            <div class="form-group">
              <label>Mật khẩu hiện tại</label>
              <div class="password-input-wrapper">
                <input
                  :type="showCurrentPassword ? 'text' : 'password'"
                  v-model="passwordForm.current_password"
                  required
                  placeholder="Nhập mật khẩu hiện tại"
                />
                <i 
                  class="fa-solid password-icon" 
                  :class="showCurrentPassword ? 'fa-eye password-icon-red' : 'fa-eye-slash password-icon-gray'"
                  @click="showCurrentPassword = !showCurrentPassword"
                ></i>
              </div>
            </div>
            <div class="form-group">
              <label>Mật khẩu mới</label>
              <div class="password-input-wrapper">
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="passwordForm.new_password"
                  required
                  placeholder="Nhập mật khẩu mới"
                />
                <i 
                  class="fa-solid password-icon" 
                  :class="showNewPassword ? 'fa-eye password-icon-red' : 'fa-eye-slash password-icon-gray'"
                  @click="showNewPassword = !showNewPassword"
                ></i>
              </div>
            </div>
            <div class="form-group">
              <label>Xác nhận mật khẩu mới</label>
              <div class="password-input-wrapper">
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="passwordForm.confirm_password"
                  required
                  placeholder="Nhập lại mật khẩu mới"
                />
                <i 
                  class="fa-solid password-icon" 
                  :class="showConfirmPassword ? 'fa-eye password-icon-red' : 'fa-eye-slash password-icon-gray'"
                  @click="showConfirmPassword = !showConfirmPassword"
                ></i>
              </div>
            </div>
          </div>
          <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-custom" @click="showChangePasswordModal = false">Hủy bỏ</button>
            <button type="submit" class="btn-primary-custom" :disabled="submittingPassword">
              {{ submittingPassword ? "Đang cập nhật..." : "Cập nhật" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ADD/EDIT ADDRESS MODAL -->
    <div v-if="showAddAddressForm" class="modal-overlay-custom" @click.self="showAddAddressForm = false">
      <div class="modal-card-custom" style="max-width: 500px;">
        <div class="modal-header-custom">
          <h3>{{ editingAddressId ? "Cập nhật địa chỉ nhận hàng" : "Thêm địa chỉ nhận hàng" }}</h3>
          <button class="btn-close-modal" @click="showAddAddressForm = false">✕</button>
        </div>
        <form @submit.prevent="handleSubmitAddress">
          <div class="modal-body-custom">
            <div class="form-group">
              <label>Số điện thoại người nhận</label>
              <input
                type="text"
                v-model="addressForm.so_dien_thoai"
                required
                placeholder="Nhập số điện thoại"
              />
            </div>
            
            <div class="form-group position-relative-custom">
              <label>Tỉnh/Thành phố</label>
              <div 
                class="searchable-select-trigger" 
                @click="toggleProvinceDropdown"
                :class="{ 'disabled': submittingAddress }"
              >
                <span>{{ addressForm.thanh_pho || 'Chọn Tỉnh/Thành phố' }}</span>
                <i class="fa-solid fa-chevron-down trigger-icon"></i>
              </div>
              <div v-if="showProvinceDropdown" class="searchable-select-dropdown">
                <div class="search-input-wrapper">
                  <input 
                    type="text" 
                    v-model="provinceSearchQuery" 
                    placeholder="Tìm kiếm Tỉnh/Thành phố..."
                    class="dropdown-search-input"
                    ref="provinceSearchInput"
                    @click.stop
                  />
                  <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </div>
                <div class="dropdown-options-list">
                  <div 
                    v-for="city in filteredProvinces" 
                    :key="city.PROVINCE_ID" 
                    class="dropdown-option-item"
                    :class="{ 'active': addressForm.thanh_pho_id === city.PROVINCE_ID }"
                    @click="selectProvince(city)"
                  >
                    {{ city.PROVINCE_NAME }}
                  </div>
                  <div v-if="filteredProvinces.length === 0" class="no-options-found">
                    Không tìm thấy kết quả
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-group position-relative-custom">
              <label>Quận/Huyện</label>
              <div 
                class="searchable-select-trigger" 
                @click="toggleDistrictDropdown"
                :class="{ 'disabled': !addressForm.thanh_pho_id || loadingDistricts || submittingAddress }"
              >
                <span v-if="loadingDistricts">
                  <i class="fa-solid fa-spinner fa-spin spacer-right"></i> Đang tải danh sách...
                </span>
                <span v-else>{{ addressForm.quan_huyen || 'Chọn Quận/Huyện' }}</span>
                <i class="fa-solid fa-chevron-down trigger-icon"></i>
              </div>
              <div v-if="showDistrictDropdown" class="searchable-select-dropdown">
                <div class="search-input-wrapper">
                  <input 
                    type="text" 
                    v-model="districtSearchQuery" 
                    placeholder="Tìm kiếm Quận/Huyện..."
                    class="dropdown-search-input"
                    ref="districtSearchInput"
                    @click.stop
                  />
                  <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </div>
                <div class="dropdown-options-list">
                  <div 
                    v-for="district in filteredDistricts" 
                    :key="district.DISTRICT_ID" 
                    class="dropdown-option-item"
                    :class="{ 'active': addressForm.quan_huyen_id === district.DISTRICT_ID }"
                    @click="selectDistrict(district)"
                  >
                    {{ district.DISTRICT_NAME }}
                  </div>
                  <div v-if="filteredDistricts.length === 0" class="no-options-found">
                    Không tìm thấy kết quả
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-group position-relative-custom">
              <label>Phường/Xã</label>
              <div 
                class="searchable-select-trigger" 
                @click="toggleWardDropdown"
                :class="{ 'disabled': !addressForm.quan_huyen_id || loadingWards || submittingAddress }"
              >
                <span v-if="loadingWards">
                  <i class="fa-solid fa-spinner fa-spin spacer-right"></i> Đang tải danh sách...
                </span>
                <span v-else>{{ addressForm.phuong_xa || 'Chọn Phường/Xã' }}</span>
                <i class="fa-solid fa-chevron-down trigger-icon"></i>
              </div>
              <div v-if="showWardDropdown" class="searchable-select-dropdown">
                <div class="search-input-wrapper">
                  <input 
                    type="text" 
                    v-model="wardSearchQuery" 
                    placeholder="Tìm kiếm Phường/Xã..."
                    class="dropdown-search-input"
                    ref="wardSearchInput"
                    @click.stop
                  />
                  <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </div>
                <div class="dropdown-options-list">
                  <div 
                    v-for="ward in filteredWards" 
                    :key="ward.WARDS_ID" 
                    class="dropdown-option-item"
                    :class="{ 'active': addressForm.phuong_xa_id === ward.WARDS_ID }"
                    @click="selectWard(ward)"
                  >
                    {{ ward.WARDS_NAME }}
                  </div>
                  <div v-if="filteredWards.length === 0" class="no-options-found">
                    Không tìm thấy kết quả
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label>Địa chỉ nhà</label>
              <input
                type="text"
                v-model="addressForm.dia_chi_nha"
                required
                placeholder="Nhập địa chỉ nhà"
              />
            </div>
            
            <hr class="modal-dashed-divider" />
            
            <!-- Group 2: Đặt tên gợi nhớ -->
            <div class="modal-section-title">Đặt tên gợi nhớ</div>
            <div class="form-group">
              <input
                type="text"
                v-model="addressForm.ten_goi_nho"
                placeholder="Ví dụ: Nhà riêng, Công ty, Trường học..."
              />
            </div>
            
            <!-- Group 3: Loại địa chỉ -->
            <div class="form-group">
              <label>Loại địa chỉ</label>
              <div class="address-type-buttons">
                <button
                  type="button"
                  class="btn-type-option"
                  :class="{ active: addressForm.loai_dia_chi === 'Nhà' }"
                  @click="addressForm.loai_dia_chi = 'Nhà'"
                >
                  Nhà
                </button>
                <button
                  type="button"
                  class="btn-type-option"
                  :class="{ active: addressForm.loai_dia_chi === 'Văn phòng' }"
                  @click="addressForm.loai_dia_chi = 'Văn phòng'"
                >
                  Văn phòng
                </button>
              </div>
            </div>
            
            <hr class="modal-dashed-divider" />
            
            <!-- Group 4: Mặc định toggle -->
            <div class="default-toggle-row" style="margin-bottom: -10px;">
              <span>Đặt làm địa chỉ mặc định</span>
              <label class="switch-custom">
                <input type="checkbox" v-model="addressForm.la_mac_dinh" />
                <span class="slider-custom"></span>
              </label>
            </div>
          </div>
          
          <div class="modal-footer-custom" style="padding: 8px 20px 12px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <button
              type="submit"
              class="full-width-btn"
              :disabled="submittingAddress"
            >
              {{ submittingAddress ? "Đang lưu..." : (editingAddressId ? "Cập nhật địa chỉ" : "Thêm địa chỉ") }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- LOGOUT CONFIRM MODAL -->
    <div
      v-if="showLogoutModal"
      class="logout-modal-overlay"
      @click.self="showLogoutModal = false"
    >
      <div class="logout-modal-card">
        <div class="logout-modal-icon"><i class="fa-solid fa-right-from-bracket" style="color: #d70018;"></i></div>
        <h3>Xác nhận đăng xuất</h3>
        <p>Bạn có chắc chắn muốn đăng xuất khỏi tài khoản này không?</p>
        <div class="logout-modal-actions">
          <button class="btn-cancel-logout" @click="showLogoutModal = false">
            Hủy bỏ
          </button>
          <button class="btn-confirm-logout" @click="confirmLogout">
            Đăng xuất
          </button>
        </div>
      </div>
    </div>

    <!-- DELETE ADDRESS CONFIRM MODAL -->
    <div
      v-if="showDeleteConfirmModal"
      class="logout-modal-overlay"
      @click.self="showDeleteConfirmModal = false"
    >
      <div class="logout-modal-card">
        <div class="logout-modal-icon"><i class="fa-solid fa-trash-can" style="color: #ef4444;"></i></div>
        <h3>Xác nhận xóa địa chỉ</h3>
        <p>Bạn có chắc chắn muốn xóa địa chỉ này khỏi sổ địa chỉ của mình không?</p>
        <div class="logout-modal-actions">
          <button class="btn-cancel-logout" @click="showDeleteConfirmModal = false">
            Hủy bỏ
          </button>
          <button class="btn-confirm-logout" style="background-color: #ef4444;" @click="confirmDeleteAddress">
            Xóa địa chỉ
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const CITIES_DATA = {
  "Hà Nội": {
    "Quận Cầu Giấy": ["Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Quan Hoa", "Phường Nghĩa Tân", "Phường Mai Dịch"],
    "Quận Đống Đa": ["Phường Láng Thượng", "Phường Láng Hạ", "Phường Quang Trung", "Phường Ô Chợ Dừa", "Phường Cát Linh"],
    "Quận Ba Đình": ["Phường Trúc Bạch", "Phường Cống Vị", "Phường Giảng Võ", "Phường Kim Mã", "Phường Thành Công"],
    "Quận Hoàn Kiếm": ["Phường Hàng Bạc", "Phường Hàng Bông", "Phường Tràng Tiền", "Phường Hàng Đào", "Phường Hàng Gai"]
  },
  "TP. Hồ Chí Minh": {
    "Quận 1": ["Phường Bến Nghé", "Phường Bến Thành", "Phường Đa Kao", "Phường Tân Định", "Phường Phạm Ngũ Lão"],
    "Quận 3": ["Phường Võ Thị Sáu", "Phường 6", "Phường 7", "Phường 8"],
    "Quận 7": ["Phường Tân Phong", "Phường Tân Quy", "Phường Phú Mỹ", "Phường Tân Thuận Đông"],
    "Quận Bình Thạnh": ["Phường 15", "Phường 22", "Phường 25", "Phường 26"]
  },
  "Đà Nẵng": {
    "Quận Hải Châu": ["Phường Thạch Thang", "Phường Hải Châu I", "Phường Hải Châu II", "Phường Phước Ninh"],
    "Quận Thanh Khê": ["Phường Vĩnh Trung", "Phường Thạc Gián", "Phường Chính Gián", "Phường Tam Thuận"]
  }
};

import axios from "axios";

export default {
  name: "Profile",
  data() {
    return {
      activeTab: "dashboard",
      subTab: "fav",
      loading: true,
      submittingProfile: false,
      submittingPassword: false,
      submittingAddress: false,
      showAddAddressForm: false,
      showLogoutModal: false,
      showDeleteConfirmModal: false,
      addressIdToDelete: null,
      editingAddressId: null,
      showProvinceDropdown: false,
      provinceSearchQuery: "",
      showDistrictDropdown: false,
      districtSearchQuery: "",
      showWardDropdown: false,
      wardSearchQuery: "",
      loadingDistricts: false,
      loadingWards: false,
      showEditProfileModal: false,
      showChangePasswordModal: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,

      // User data
      user: {
        id: null,
        name: "",
        email: "",
        phone: "",
        dob: "",
        bio: "",
        address: "",
        avatar: null,
        updated_at: "",
      },

      // Secondary fields
      orders: [],
      ordersPagination: {
        current_page: 1,
        last_page: 1,
      },
      addresses: [],
      provincesList: [],
      districtsList: [],
      wardsList: [],
      wishlist: [],
      recentlyViewed: [],

      // Mock Loyalty points and Vouchers
      userPoints: 0,
      totalSpent: 0, // Tổng tiền tích lũy mua hàng
      vouchers: [
        {
          code: "CHAMUNG",
          title: "Mã Chào Mừng 10%",
          desc: "Giảm 10% tối đa 50K cho đơn hàng tối thiểu 200K.",
        },
        {
          code: "GIAM50K",
          title: "Khuyến mãi 50K",
          desc: "Giảm trực tiếp 50K cho đơn hàng tối thiểu 500K.",
        },
        {
          code: "FREESHIP",
          title: "Miễn phí vận chuyển",
          desc: "Giảm tối đa 30K phí vận chuyển cho đơn hàng từ 300K.",
        },
      ],

      // Forms
      profileForm: {
        ho_ten: "",
        email: "",
        so_dien_thoai: "",
        ngay_sinh: "",
        gioi_thieu: "",
      },
      passwordForm: {
        current_password: "",
        new_password: "",
        confirm_password: "",
      },
      addressForm: {
        so_dien_thoai: "",
        thanh_pho_id: "",
        thanh_pho: "",
        quan_huyen_id: "",
        quan_huyen: "",
        phuong_xa_id: "",
        phuong_xa: "",
        dia_chi_nha: "",
        ten_goi_nho: "",
        loai_dia_chi: "Nhà",
        la_mac_dinh: false,
      },

      sidebarTabs: [
        {
          id: "dashboard",
          label: "Trang chủ Smember",
          icon: "fa-solid fa-house",
        },
        { id: "profile", label: "Hồ sơ & Mật khẩu", icon: "fa-solid fa-user" },
        { id: "orders", label: "Lịch sử mua hàng", icon: "fa-solid fa-box" },
        { id: "vouchers", label: "Ưu đãi của tôi", icon: "fa-solid fa-ticket" },
        {
          id: "wishlist",
          label: "Yêu thích & Đã xem",
          icon: "fa-solid fa-heart",
        },
      ],
    };
  },
  computed: {
    userAvatar() {
      if (this.user.avatar) {
        return this.user.avatar;
      }
      return "https://cellphones.com.vn/sforum/wp-content/uploads/2023/10/avatar-trang-4.jpg";
    },
    memberTierName() {
      if (this.totalSpent >= 2000000) return "S-VIP (Vàng)";
      if (this.totalSpent >= 500000) return "S-Class (Bạc)";
      return "Smember (Đồng)";
    },
    memberTierClass() {
      if (this.totalSpent >= 2000000) return "svip";
      if (this.totalSpent >= 500000) return "sclass";
      return "snormal";
    },
    nextTierName() {
      if (this.totalSpent >= 2000000) return null;
      if (this.totalSpent >= 500000) return "S-VIP (Vàng)";
      return "S-Class (Bạc)";
    },
    amountToNextTier() {
      if (this.totalSpent >= 2000000) return 0;
      if (this.totalSpent >= 500000) return 2000000 - this.totalSpent;
      return 500000 - this.totalSpent;
    },
    tierProgressPercent() {
      if (this.totalSpent >= 2000000) return 100;
      if (this.totalSpent >= 500000) {
        return ((this.totalSpent - 500000) / 1500000) * 100;
      }
      return (this.totalSpent / 500000) * 100;
    },
    defaultAddressText() {
      const def = this.addresses.find(a => a.la_mac_dinh);
      if (def) {
        return `${def.dia_chi_chi_tiet}, ${def.quan_huyen}, ${def.thanh_pho}`;
      }
      return '-';
    },
    citiesList() {
      return Object.keys(CITIES_DATA);
    },
    availableDistricts() {
      if (!this.addressForm.thanh_pho) return [];
      return Object.keys(CITIES_DATA[this.addressForm.thanh_pho] || {});
    },
    availableWards() {
      if (!this.addressForm.thanh_pho || !this.addressForm.quan_huyen) return [];
      return CITIES_DATA[this.addressForm.thanh_pho][this.addressForm.quan_huyen] || [];
    },
    filteredProvinces() {
      if (!this.provinceSearchQuery) return this.provincesList;
      const q = this.provinceSearchQuery.toLowerCase();
      return this.provincesList.filter(p => p.PROVINCE_NAME.toLowerCase().includes(q));
    },
    filteredDistricts() {
      if (!this.districtSearchQuery) return this.districtsList;
      const q = this.districtSearchQuery.toLowerCase();
      return this.districtsList.filter(d => d.DISTRICT_NAME.toLowerCase().includes(q));
    },
    filteredWards() {
      if (!this.wardSearchQuery) return this.wardsList;
      const q = this.wardSearchQuery.toLowerCase();
      return this.wardsList.filter(w => w.WARDS_NAME.toLowerCase().includes(q));
    }
  },
  mounted() {
    this.checkLoginAndLoad();
    this.loadWishlist();
    this.loadRecentlyViewed();
    this.fetchProvinces();
    document.addEventListener("click", this.closeSearchDropdowns);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.closeSearchDropdowns);
  },
  methods: {
    getToken() {
      return localStorage.getItem("token_client");
    },
    async fetchProvinces() {
      try {
        const res = await axios.get("http://127.0.0.1:8000/api/viettelpost/provinces");
        if (res.data && res.data.data) {
          this.provincesList = res.data.data;
        } else if (res.data) {
          this.provincesList = res.data;
        }
      } catch (error) {
        console.error("Lỗi khi tải danh sách Tỉnh/Thành phố:", error);
      }
    },
    async fetchDistricts(provinceId) {
      if (!provinceId) return;
      this.loadingDistricts = true;
      try {
        const res = await axios.get(`http://127.0.0.1:8000/api/viettelpost/districts/${provinceId}`);
        if (res.data && res.data.data) {
          this.districtsList = res.data.data;
        } else if (res.data) {
          this.districtsList = res.data;
        }
      } catch (error) {
        console.error("Lỗi khi tải danh sách Quận/Huyện:", error);
      } finally {
        this.loadingDistricts = false;
      }
    },
    async fetchWards(districtId) {
      if (!districtId) return;
      this.loadingWards = true;
      try {
        const res = await axios.get(`http://127.0.0.1:8000/api/viettelpost/wards/${districtId}`);
        if (res.data && res.data.data) {
          this.wardsList = res.data.data;
        } else if (res.data) {
          this.wardsList = res.data;
        }
      } catch (error) {
        console.error("Lỗi khi tải danh sách Phường/Xã:", error);
      } finally {
        this.loadingWards = false;
      }
    },
    handleProvinceChange() {
      this.addressForm.quan_huyen_id = "";
      this.addressForm.quan_huyen = "";
      this.addressForm.phuong_xa_id = "";
      this.addressForm.phuong_xa = "";
      this.districtsList = [];
      this.wardsList = [];

      const prov = this.provincesList.find(p => p.PROVINCE_ID === this.addressForm.thanh_pho_id);
      if (prov) {
        this.addressForm.thanh_pho = prov.PROVINCE_NAME;
        this.fetchDistricts(prov.PROVINCE_ID);
      }
    },
    handleDistrictChange() {
      this.addressForm.phuong_xa_id = "";
      this.addressForm.phuong_xa = "";
      this.wardsList = [];

      const dist = this.districtsList.find(d => d.DISTRICT_ID === this.addressForm.quan_huyen_id);
      if (dist) {
        this.addressForm.quan_huyen = dist.DISTRICT_NAME;
        this.fetchWards(dist.DISTRICT_ID);
      }
    },
    handleWardChange() {
      const ward = this.wardsList.find(w => w.WARDS_ID === this.addressForm.phuong_xa_id);
      if (ward) {
        this.addressForm.phuong_xa = ward.WARDS_NAME;
      }
    },
    toggleProvinceDropdown() {
      if (this.submittingAddress) return;
      this.showProvinceDropdown = !this.showProvinceDropdown;
      this.showDistrictDropdown = false;
      this.showWardDropdown = false;
      this.provinceSearchQuery = "";
      if (this.showProvinceDropdown) {
        this.$nextTick(() => {
          if (this.$refs.provinceSearchInput) this.$refs.provinceSearchInput.focus();
        });
      }
    },
    selectProvince(city) {
      this.addressForm.thanh_pho_id = city.PROVINCE_ID;
      this.addressForm.thanh_pho = city.PROVINCE_NAME;
      this.showProvinceDropdown = false;
      this.handleProvinceChange();
    },
    toggleDistrictDropdown() {
      if (!this.addressForm.thanh_pho_id || this.loadingDistricts || this.submittingAddress) return;
      this.showDistrictDropdown = !this.showDistrictDropdown;
      this.showProvinceDropdown = false;
      this.showWardDropdown = false;
      this.districtSearchQuery = "";
      if (this.showDistrictDropdown) {
        this.$nextTick(() => {
          if (this.$refs.districtSearchInput) this.$refs.districtSearchInput.focus();
        });
      }
    },
    selectDistrict(district) {
      this.addressForm.quan_huyen_id = district.DISTRICT_ID;
      this.addressForm.quan_huyen = district.DISTRICT_NAME;
      this.showDistrictDropdown = false;
      this.handleDistrictChange();
    },
    toggleWardDropdown() {
      if (!this.addressForm.quan_huyen_id || this.loadingWards || this.submittingAddress) return;
      this.showWardDropdown = !this.showWardDropdown;
      this.showProvinceDropdown = false;
      this.showDistrictDropdown = false;
      this.wardSearchQuery = "";
      if (this.showWardDropdown) {
        this.$nextTick(() => {
          if (this.$refs.wardSearchInput) this.$refs.wardSearchInput.focus();
        });
      }
    },
    selectWard(ward) {
      this.addressForm.phuong_xa_id = ward.WARDS_ID;
      this.addressForm.phuong_xa = ward.WARDS_NAME;
      this.showWardDropdown = false;
      this.handleWardChange();
    },
    closeSearchDropdowns(e) {
      if (!e.target.closest('.position-relative-custom')) {
        this.showProvinceDropdown = false;
        this.showDistrictDropdown = false;
        this.showWardDropdown = false;
      }
    },
    parseAddressDisplay(addr) {
      const detail = addr.dia_chi_chi_tiet || "";
      const regex = /^\[(.*?) - (Nhà|Văn phòng)\] (.*?)$/;
      const match = detail.match(regex);
      
      let label = "";
      let type = "Nhà";
      let streetAndWard = detail;

      if (match) {
        label = match[1];
        type = match[2];
        streetAndWard = match[3];
      } else {
        const regex2 = /^\[(Nhà|Văn phòng)\] (.*?)$/;
        const match2 = detail.match(regex2);
        if (match2) {
          label = "";
          type = match2[1];
          streetAndWard = match2[2];
        }
      }

      // Format the full address details nicely
      const fullAddressDetails = [streetAndWard, addr.quan_huyen, addr.thanh_pho]
        .filter(Boolean)
        .join(", ");

      return {
        label: label || type || "Địa chỉ",
        type: type,
        fullAddress: fullAddressDetails
      };
    },
    async loadAddressesOnly() {
      const token = this.getToken();
      if (!token) return;
      try {
        const addressRes = await axios.get(
          "http://127.0.0.1:8000/api/khach-hang/dia-chi",
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.addresses = addressRes.data || [];
        
        // Update user's default address display
        const defaultAddr = this.addresses.find(a => a.la_mac_dinh) || this.addresses[0];
        if (defaultAddr) {
          this.user.address = defaultAddr.dia_chi_chi_tiet || "";
        } else {
          this.user.address = "";
        }
      } catch (error) {
        console.error("Lỗi khi tải lại danh sách địa chỉ:", error);
      }
    },
    async checkLoginAndLoad() {
      const token = this.getToken();
      if (!token) {
        this.$toast.warning("Bạn cần đăng nhập để truy cập trang này.");
        this.$router.push("/login");
        return;
      }

      this.loading = true;
      try {
        // 1. Tải thông tin tài khoản
        const profileRes = await axios.get(
          "http://127.0.0.1:8000/api/thong-tin-ca-nhan/profile",
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );

        if (profileRes.data.status === 1) {
          const d = profileRes.data.data;
          this.user = {
            id: d.id,
            name: d.name,
            email: d.email,
            phone: d.phone,
            dob: d.dob || "",
            bio: d.bio || "",
            address: d.address || "",
            avatar: d.avatar,
            updated_at: d.updated_at || "",
          };

          this.profileForm = {
            ho_ten: d.name,
            email: d.email,
            so_dien_thoai: d.phone,
            ngay_sinh: d.dob || "",
            gioi_thieu: d.bio || "",
          };

          this.totalSpent = d.tong_chi_tieu || 0;
          this.userPoints = d.diem_thanh_vien || 0;
        }

        // 2. Tải sổ địa chỉ nhận hàng
        const addressRes = await axios.get(
          "http://127.0.0.1:8000/api/khach-hang/dia-chi",
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.addresses = addressRes.data || [];

        // 3. Tải lịch sử đơn hàng (Trang đầu tiên)
        await this.loadOrders(1);
      } catch (error) {
        console.error("Lỗi khi tải thông tin cá nhân:", error);
        if (error.response && error.response.status === 401) {
          this.$toast.warning(
            "Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.",
          );
          localStorage.removeItem("token_client");
          localStorage.removeItem("ho_ten_client");
          this.$router.push("/login");
        }
      } finally {
        this.loading = false;
      }
    },
    async updateProfileInfo() {
      const token = this.getToken();
      this.submittingProfile = true;
      try {
        const payload = new FormData();
        payload.append("ho_ten", this.profileForm.ho_ten);
        payload.append("email", this.profileForm.email);
        payload.append("so_dien_thoai", this.profileForm.so_dien_thoai || "");
        payload.append("ngay_sinh", this.profileForm.ngay_sinh || "");
        payload.append("dia_chi", this.user.address || "");
        payload.append("gioi_thieu", this.profileForm.gioi_thieu || "");

        const res = await axios.post(
          "http://127.0.0.1:8000/api/thong-tin-ca-nhan/update",
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          },
        );

        if (res.data.status === 1) {
          const updated = res.data.data;
          this.user.name = updated.name;
          this.user.phone = updated.phone;
          this.user.dob = updated.dob;
          this.user.bio = updated.bio;
          this.user.updated_at = updated.updated_at || "";
          if (updated.avatar) {
            this.user.avatar = updated.avatar;
          }
          // Lưu tên mới vào localStorage
          localStorage.setItem("ho_ten_client", updated.name);
          window.dispatchEvent(new Event("clientLoginUpdated"));
          this.showEditProfileModal = false;
          this.$toast.success("Cập nhật thông tin cá nhân thành công!");
        } else {
          this.$toast.error(res.data.message || "Cập nhật thất bại.");
        }
      } catch (error) {
        console.error("Cập nhật thông tin lỗi:", error);
        this.$toast.error(
          error.response?.data?.message || "Đã xảy ra lỗi hệ thống.",
        );
      } finally {
        this.submittingProfile = false;
      }
    },
    async updatePassword() {
      if (
        this.passwordForm.new_password !== this.passwordForm.confirm_password
      ) {
        this.$toast.warning("Xác nhận mật khẩu mới không khớp.");
        return;
      }

      const token = this.getToken();
      this.submittingPassword = true;
      try {
        const res = await axios.post(
          "http://127.0.0.1:8000/api/thong-tin-ca-nhan/update-password",
          this.passwordForm,
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );

        if (res.data.status === 1) {
          this.showChangePasswordModal = false;
          this.$toast.success("Đổi mật khẩu thành công!");
          this.passwordForm = {
            current_password: "",
            new_password: "",
            confirm_password: "",
          };
          // Cập nhật lại thời gian thay đổi mật khẩu
          this.checkLoginAndLoad();
        } else {
          this.$toast.error(res.data.message || "Đổi mật khẩu thất bại.");
        }
      } catch (error) {
        console.error("Đổi mật khẩu lỗi:", error);
        this.$toast.error(
          error.response?.data?.message ||
            "Mật khẩu cũ không chính xác hoặc lỗi định dạng.",
        );
      } finally {
        this.submittingPassword = false;
      }
    },
    async handleAvatarChange(e) {
      const file = e.target.files[0];
      if (!file) return;

      const token = this.getToken();
      const payload = new FormData();
      payload.append("ho_ten", this.profileForm.ho_ten);
      payload.append("email", this.profileForm.email);
      payload.append("avatar", file);

      try {
        const res = await axios.post(
          "http://127.0.0.1:8000/api/thong-tin-ca-nhan/update",
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          },
        );
        if (res.data.status === 1 && res.data.data.avatar) {
          this.user.avatar = res.data.data.avatar;
          this.$toast.success("Cập nhật ảnh đại diện thành công!");
        }
      } catch (error) {
        console.error("Lỗi khi tải ảnh đại diện lên:", error);
        this.$toast.error("Không thể tải lên ảnh đại diện.");
      }
    },
    openEditProfileModal() {
      this.profileForm = {
        ho_ten: this.user.name,
        email: this.user.email,
        so_dien_thoai: this.user.phone,
        ngay_sinh: this.user.dob,
        gioi_thieu: this.user.bio,
      };
      this.showEditProfileModal = true;
    },
    openChangePasswordModal() {
      this.passwordForm = {
        current_password: "",
        new_password: "",
        confirm_password: "",
      };
      this.showChangePasswordModal = true;
    },
    openAddAddressModal() {
      this.editingAddressId = null;
      this.addressForm = {
        so_dien_thoai: "",
        thanh_pho_id: "",
        thanh_pho: "",
        quan_huyen_id: "",
        quan_huyen: "",
        phuong_xa_id: "",
        phuong_xa: "",
        dia_chi_nha: "",
        ten_goi_nho: "",
        loai_dia_chi: "Nhà",
        la_mac_dinh: false,
      };
      this.districtsList = [];
      this.wardsList = [];
      this.showAddAddressForm = true;
    },
    async editAddress(addr) {
      this.editingAddressId = addr.id;
      this.addressForm = {
        so_dien_thoai: addr.so_dien_thoai,
        thanh_pho_id: addr.thanh_pho_id || "",
        thanh_pho: addr.thanh_pho || "",
        quan_huyen_id: addr.quan_huyen_id || "",
        quan_huyen: addr.quan_huyen || "",
        phuong_xa_id: addr.phuong_xa_id || "",
        phuong_xa: addr.phuong_xa || "",
        dia_chi_nha: "",
        ten_goi_nho: "",
        loai_dia_chi: "Nhà",
        la_mac_dinh: !!addr.la_mac_dinh,
      };

      // Clear dependent lists first
      this.districtsList = [];
      this.wardsList = [];

      // Parse detailed address
      const detail = addr.dia_chi_chi_tiet || "";
      const regex = /^\[(.*?) - (Nhà|Văn phòng)\] (.*?)$/;
      const match = detail.match(regex);
      let rawStreet = detail;
      if (match) {
        this.addressForm.ten_goi_nho = match[1];
        this.addressForm.loai_dia_chi = match[2];
        rawStreet = match[3];
      } else {
        const regex2 = /^\[(Nhà|Văn phòng)\] (.*?)$/;
        const match2 = detail.match(regex2);
        if (match2) {
          this.addressForm.ten_goi_nho = "";
          this.addressForm.loai_dia_chi = match2[1];
          rawStreet = match2[2];
        } else {
          this.addressForm.ten_goi_nho = "";
          this.addressForm.loai_dia_chi = "Nhà";
        }
      }

      // Strip ward name if it is at the end of the rawStreet
      if (addr.phuong_xa && rawStreet.endsWith(`, ${addr.phuong_xa}`)) {
        rawStreet = rawStreet.slice(0, rawStreet.length - (addr.phuong_xa.length + 2));
      }
      this.addressForm.dia_chi_nha = rawStreet;

      // Load dropdowns based on IDs
      if (addr.thanh_pho_id) {
        await this.fetchDistricts(addr.thanh_pho_id);
      }
      if (addr.quan_huyen_id) {
        await this.fetchWards(addr.quan_huyen_id);
      }

      // Fallback for older addresses created without IDs
      if (!this.addressForm.thanh_pho_id && this.addressForm.thanh_pho) {
        const matchedProv = this.provincesList.find(
          p => p.PROVINCE_NAME.toLowerCase().trim() === this.addressForm.thanh_pho.toLowerCase().trim()
        );
        if (matchedProv) {
          this.addressForm.thanh_pho_id = matchedProv.PROVINCE_ID;
          await this.fetchDistricts(matchedProv.PROVINCE_ID);

          if (this.addressForm.quan_huyen) {
            const matchedDist = this.districtsList.find(
              d => d.DISTRICT_NAME.toLowerCase().trim() === this.addressForm.quan_huyen.toLowerCase().trim()
            );
            if (matchedDist) {
              this.addressForm.quan_huyen_id = matchedDist.DISTRICT_ID;
              await this.fetchWards(matchedDist.DISTRICT_ID);

              // Find ward from detailed text if available
              const regexWard = /,\s*([^,]+)$/;
              const matchWard = detail.match(regexWard);
              let possibleWardName = addr.phuong_xa;
              if (!possibleWardName && matchWard) {
                possibleWardName = matchWard[1];
              }

              if (possibleWardName) {
                const matchedWard = this.wardsList.find(
                  w => w.WARDS_NAME.toLowerCase().trim() === possibleWardName.toLowerCase().trim()
                );
                if (matchedWard) {
                  this.addressForm.phuong_xa_id = matchedWard.WARDS_ID;
                  this.addressForm.phuong_xa = matchedWard.WARDS_NAME;
                }
              }
            }
          }
        }
      }

      this.showAddAddressForm = true;
    },
    async handleSubmitAddress() {
      if (!this.addressForm.thanh_pho_id) {
        alert("Vui lòng chọn Tỉnh/Thành phố");
        return;
      }
      if (!this.addressForm.quan_huyen_id) {
        alert("Vui lòng chọn Quận/Huyện");
        return;
      }
      if (!this.addressForm.phuong_xa_id) {
        alert("Vui lòng chọn Phường/Xã");
        return;
      }

      const type = this.addressForm.loai_dia_chi || "Nhà";
      const prefix = this.addressForm.ten_goi_nho 
        ? `[${this.addressForm.ten_goi_nho} - ${type}]`
        : `[${type}]`;
      
      const wardPart = this.addressForm.phuong_xa ? `, ${this.addressForm.phuong_xa}` : "";
      const combinedDetail = `${prefix} ${this.addressForm.dia_chi_nha}${wardPart}`;
      
      const payload = {
        so_dien_thoai: this.addressForm.so_dien_thoai,
        thanh_pho_id: this.addressForm.thanh_pho_id,
        thanh_pho: this.addressForm.thanh_pho,
        quan_huyen_id: this.addressForm.quan_huyen_id,
        quan_huyen: this.addressForm.quan_huyen,
        phuong_xa_id: this.addressForm.phuong_xa_id,
        phuong_xa: this.addressForm.phuong_xa,
        dia_chi_chi_tiet: combinedDetail,
        la_mac_dinh: this.addressForm.la_mac_dinh,
      };

      if (this.editingAddressId) {
        await this.updateExistingAddress(payload);
      } else {
        await this.saveNewAddress(payload);
      }
    },
    async saveNewAddress(payload) {
      const token = this.getToken();
      this.submittingAddress = true;
      try {
        await axios.post(
          "http://127.0.0.1:8000/api/khach-hang/dia-chi",
          payload,
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.showAddAddressForm = false;
        this.$toast.success("Thêm địa chỉ nhận hàng thành công!");
        this.loadAddressesOnly(); // Reload list silently
      } catch (error) {
        console.error("Lỗi lưu địa chỉ:", error);
        this.$toast.error("Đã xảy ra lỗi, vui lòng kiểm tra lại thông tin.");
      } finally {
        this.submittingAddress = false;
      }
    },
    async updateExistingAddress(payload) {
      const token = this.getToken();
      this.submittingAddress = true;
      try {
        await axios.put(
          `http://127.0.0.1:8000/api/khach-hang/dia-chi/${this.editingAddressId}`,
          payload,
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.showAddAddressForm = false;
        this.editingAddressId = null;
        this.$toast.success("Cập nhật địa chỉ nhận hàng thành công!");
        this.loadAddressesOnly(); // Reload list silently
      } catch (error) {
        console.error("Lỗi cập nhật địa chỉ:", error);
        this.$toast.error("Đã xảy ra lỗi, vui lòng kiểm tra lại thông tin.");
      } finally {
        this.submittingAddress = false;
      }
    },
    deleteAddress(id) {
      this.addressIdToDelete = id;
      this.showDeleteConfirmModal = true;
    },
    async confirmDeleteAddress() {
      if (!this.addressIdToDelete) return;
      const token = this.getToken();
      try {
        await axios.delete(
          `http://127.0.0.1:8000/api/khach-hang/dia-chi/${this.addressIdToDelete}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.$toast.success("Xóa địa chỉ thành công!");
        this.loadAddressesOnly(); // Reload list silently
      } catch (error) {
        console.error("Lỗi xóa địa chỉ:", error);
        this.$toast.error("Không thể xóa địa chỉ này.");
      } finally {
        this.showDeleteConfirmModal = false;
        this.addressIdToDelete = null;
      }
    },
    async loadOrders(page = 1) {
      const token = this.getToken();
      if (!token) return;
      try {
        const ordersRes = await axios.get(
          `http://127.0.0.1:8000/api/khach-hang/don-hang?page=${page}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          },
        );
        this.orders = ordersRes.data.data || [];
        this.ordersPagination = {
          current_page: ordersRes.data.current_page || 1,
          last_page: ordersRes.data.last_page || 1,
        };
      } catch (error) {
        console.error("Lỗi khi tải đơn hàng:", error);
      }
    },
    loadWishlist() {
      this.wishlist = JSON.parse(localStorage.getItem("wishlist") || "[]");
    },
    removeFav(id) {
      this.wishlist = this.wishlist.filter((item) => item.id !== id);
      localStorage.setItem("wishlist", JSON.stringify(this.wishlist));
    },
    loadRecentlyViewed() {
      this.recentlyViewed = JSON.parse(
        localStorage.getItem("recentlyViewed") || "[]",
      );
    },
    getProductImageUrl(path) {
      if (!path) return "https://via.placeholder.com/300?text=No+Image";
      if (path.startsWith("http")) return path;
      return "http://127.0.0.1:8000" + (path.startsWith("/") ? "" : "/") + path;
    },
    copyCode(code) {
      navigator.clipboard.writeText(code);
      this.$toast.success(`Đã copy mã voucher: ${code}`);
    },
    formatPrice(value) {
      if (!value) return "0 đ";
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(value);
    },
    formatPoints(value) {
      return new Intl.NumberFormat("vi-VN").format(value) + " xu";
    },
    formatDate(dateStr) {
      if (!dateStr) return "";
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },
    formatTime(dateStr) {
      if (!dateStr) return "";
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return "";
      const hours = String(d.getHours()).padStart(2, "0");
      const minutes = String(d.getMinutes()).padStart(2, "0");
      return `${hours}:${minutes}`;
    },
    getOrderStatusText(status) {
      const map = {
        cho_xu_ly: "Chờ xử lý",
        dang_chuan_bi: "Đang chuẩn bị hàng",
        dang_giao: "Đang giao hàng",
        da_giao: "Đã giao hàng thành công",
        hoan_thanh: "Hoàn tất đơn hàng",
        da_huy: "Đã hủy",
      };
      return map[status] || status;
    },
    getOrderStatusClass(status) {
      const map = {
        cho_xu_ly: "status-pending",
        dang_chuan_bi: "status-preparing",
        dang_giao: "status-shipping",
        da_giao: "status-completed",
        hoan_thanh: "status-completed",
        da_huy: "status-cancelled",
      };
      return map[status] || "";
    },
    logoutUser() {
      this.showLogoutModal = true;
    },
    confirmLogout() {
      this.showLogoutModal = false;
      localStorage.removeItem("token_client");
      localStorage.removeItem("ho_ten_client");
      window.dispatchEvent(new Event("clientLoginUpdated"));
      this.$router.push("/");
    },
  },
};
</script>

<style scoped>
/* ── Container Layout ── */
.profile-page {
  background-color: #f4f6f9;
  padding: 12px 0 40px 0;
  color: #1e293b;
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 30px;
  align-items: start;
}

/* ── Sidebar Style ── */
.profile-sidebar {
  background: transparent;
  padding: 10px 0;
}

.user-card-sm {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 20px;
}

.avatar-wrap {
  position: relative;
  width: 90px;
  height: 90px;
  margin-bottom: 12px;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--red, #d70018);
  padding: 2px;
}

.avatar-upload-label {
  position: absolute;
  bottom: 0;
  right: 0;
  background: #fff;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
  transition: transform 0.2s;
}

.avatar-upload-label:hover {
  transform: scale(1.1);
}

.hidden-input {
  display: none;
}

.user-fullname {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 6px 0;
}

.member-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}
.member-badge.snormal {
  background: #f1f5f9;
  color: #64748b;
}
.member-badge.sclass {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}
.member-badge.svip {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  background: none;
  border: none;
  padding: 12px 16px;
  border-radius: 10px;
  color: #475569;
  font-weight: 600;
  font-size: 14.5px;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-item:hover {
  background: #f8fafc;
  color: var(--red, #d70018);
}

.nav-item.active {
  background: #fff1f2;
  color: var(--red, #d70018);
}

.logout-btn-sidebar {
  margin-top: 12px;
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
  border-radius: 0 0 10px 10px;
}

.logout-btn-sidebar:hover {
  background: #fee2e2;
  color: #dc2626 !important;
}

.nav-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  font-size: 16px;
  color: inherit;
}

/* ── Content Main Panel ── */
.profile-content {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  min-height: 500px;
}

.content-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  font-size: 17px;
  color: #64748b;
  font-weight: 500;
}
.content-loading i {
  margin-right: 8px;
  font-size: 22px;
  color: var(--red, #d70018);
}

.content-header {
  margin-bottom: 10px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 5px;
}
.content-header h2 {
  font-size: 22px;
  font-weight: 800;
  margin: 0 0 6px 0;
}
.content-header p {
  margin: 0;
  font-size: 13.5px;
  color: #64748b;
}

/* ── Smember Card Style ── */
.smember-rank-card {
  background: linear-gradient(135deg, #d70018, #99000c);
  border-radius: 16px;
  color: #fff;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 8px 24px rgba(215, 0, 24, 0.2);
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 20px;
}

.smember-label {
  font-size: 11px;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  padding: 3px 10px;
  border-radius: 6px;
  letter-spacing: 0.05em;
}

.smember-title {
  margin: 8px 0 0 0;
  font-size: 22px;
  font-weight: 800;
}

.member-logo {
  font-size: 20px;
  font-style: italic;
  font-weight: 900;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.loyalty-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
}

.loyalty-stat {
  text-align: center;
}

.stat-num {
  font-size: 18px;
  font-weight: 800;
}

.stat-label {
  font-size: 11px;
  opacity: 0.85;
  margin-top: 4px;
}

/* Tier progress bar */
.tier-progress-wrap {
  margin-top: 10px;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  margin-bottom: 6px;
  opacity: 0.9;
}

.progress-bar-bg {
  height: 8px;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 10px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 6px rgba(255, 255, 255, 0.5);
  transition: width 0.4s ease;
}

/* Dashboard Widgets */
.dashboard-widgets {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 28px;
}

.widget-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
}

.widget-card h4 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 15px;
  font-weight: 700;
}

.widget-card p {
  font-size: 13.5px;
  margin: 6px 0;
}

.verification-list {
  padding: 0;
  margin: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.verification-list li {
  font-size: 13px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}

.v-label {
  font-weight: 600;
  color: #64748b;
}

.v-val {
  font-weight: 500;
}

.v-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}
.v-badge.verified {
  background: #dcfce7;
  color: #166534;
}
.v-badge.unverified {
  background: #fee2e2;
  color: #991b1b;
}

.widget-action-btn {
  background: #fff;
  border: 1.5px solid var(--red, #d70018);
  color: var(--red, #d70018);
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 12px;
  transition: all 0.2s;
}
.widget-action-btn:hover {
  background: #fff1f2;
}

/* Recent Orders Widget */
.recent-orders-widget {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
}

.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}
.widget-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 750;
}
.view-all-link {
  background: none;
  border: none;
  color: var(--red, #d70018);
  font-weight: 700;
  cursor: pointer;
  font-size: 13px;
}

.orders-list-sm {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.order-item-sm {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: #f8fafc;
  border-radius: 10px;
  font-size: 13.5px;
}

.o-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.o-date {
  font-size: 11px;
  color: #94a3b8;
}

.o-price-status {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.o-price {
  font-weight: 700;
  color: var(--red, #d70018);
}

.status-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-block;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}
.status-preparing {
  background: #eff6ff;
  color: #1e40af;
}
.status-shipping {
  background: #e0f2fe;
  color: #0369a1;
}
.status-completed {
  background: #dcfce7;
  color: #15803d;
}
.status-cancelled {
  background: #fee2e2;
  color: #b91c1c;
}

/* ── Forms CSS ── */
.profile-form {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 10px;
}

.profile-form h3 {
  margin-top: 0;
  margin-bottom: 18px;
  font-size: 16px;
  font-weight: 750;
  border-left: 4px solid var(--red, #d70018);
  padding-left: 10px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group.full-width {
  grid-column: span 2;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}

.form-group input,
.form-group textarea {
  padding: 10px 14px;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  background: #fff;
  transition: border-color 0.2s;
  outline: none;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: var(--red, #d70018);
}

.disabled-input {
  background: #f1f5f9 !important;
  color: #64748b;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--red, #d70018);
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
  transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(215, 0, 24, 0.3);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ── Order History List ── */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-card {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  background: #f8fafc;
}

.order-header-row {
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
  padding: 10px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-code-title {
  display: block;
  font-size: 14px;
}

.order-date-text {
  display: block;
  font-size: 11px;
  color: #64748b;
  margin-top: 1px;
}

.order-items {
  padding: 10px 16px;
  border-bottom: 1px solid #e2e8f0;
}

.order-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  font-size: 13.5px;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.item-name {
  font-weight: 600;
  color: #1e293b;
}

.item-qty {
  color: #64748b;
  font-size: 12px;
}

.item-price {
  font-weight: 700;
  color: #0f172a;
}

.order-summary-footer {
  padding: 10px 16px;
  background: #f8fafc;
  display: flex;
  justify-content: flex-end;
}

.summary-details {
  width: 260px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12.5px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  color: #475569;
}

.discount-txt {
  color: #16a34a;
  font-weight: 600;
}

.summary-line.total {
  border-top: 1px solid #e2e8f0;
  padding-top: 6px;
  margin-top: 2px;
  font-size: 14.5px;
  font-weight: 800;
}

.total-price {
  color: var(--red, #d70018);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

.btn-primary-inline {
  display: inline-block;
  background: var(--red, #d70018);
  color: #fff;
  text-decoration: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 700;
  margin-top: 16px;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}

/* ── Address Book Style ── */
.content-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 16px;
  margin-bottom: 24px;
}
.content-header-row h2 {
  font-size: 22px;
  font-weight: 800;
  margin: 0 0 4px 0;
}
.content-header-row p {
  margin: 0;
  font-size: 13.5px;
  color: #64748b;
}

.addresses-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.address-card {
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
  position: relative;
  transition: all 0.2s;
}

.address-card.default {
  border-color: var(--red, #d70018);
  background: #fff1f2;
}

.addr-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.addr-phone {
  font-weight: 700;
  font-size: 14px;
}

.default-badge {
  background: var(--red, #d70018);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
}

.addr-detail {
  font-weight: 600;
  font-size: 14.5px;
  margin: 0 0 6px 0;
}

.addr-region {
  color: #64748b;
  font-size: 13px;
  margin: 0;
}

/* Add Address Modal */
.address-form-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-card {
  background: #fff;
  border-radius: 16px;
  padding: 30px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.modal-card h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 18px;
  font-weight: 850;
  color: #0f172a;
}

.form-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 16px 0;
}

.form-checkbox label {
  font-size: 13.5px;
  font-weight: 600;
  color: #475569;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-cancel {
  background: #f1f5f9;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
}

.btn-save {
  background: var(--red, #d70018);
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
}

/* ── Vouchers CSS ── */
.vouchers-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.voucher-card {
  display: flex;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.voucher-left {
  background: var(--red, #d70018);
  color: #fff;
  width: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.v-icon {
  font-size: 24px;
}

.voucher-right {
  flex: 1;
  padding: 16px;
  background: #fff;
}

.v-title {
  font-size: 15px;
  font-weight: 750;
  margin: 0 0 6px 0;
}

.v-desc {
  font-size: 12px;
  color: #64748b;
  margin: 0 0 12px 0;
  line-height: 1.4;
}

.v-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
}

.btn-copy {
  background: #fff1f2;
  border: 1px dashed var(--red, #d70018);
  color: var(--red, #d70018);
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 700;
  cursor: pointer;
}

/* ── Wishlist & Recently Viewed CSS ── */
.sub-tabs {
  display: flex;
  gap: 16px;
  border-bottom: 2px solid #f1f5f9;
  margin-bottom: 24px;
}

.sub-tab {
  background: none;
  border: none;
  padding: 10px 4px;
  font-size: 14.5px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s;
}

.sub-tab.active {
  color: var(--red, #d70018);
  border-bottom-color: var(--red, #d70018);
}

.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.wishlist-item-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.wishlist-item-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  border-color: var(--red, #d70018);
}

.w-img-wrap {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 10px;
}

.w-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.w-name {
  font-size: 13px;
  font-weight: 600;
  margin: 0 0 6px 0;
  height: 36px;
  overflow: hidden;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.w-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.w-price {
  font-weight: 750;
  color: var(--red, #d70018);
  font-size: 14.5px;
}

.btn-remove-fav {
  background: #f1f5f9;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
}
.btn-remove-fav:hover {
  background: #fee2e2;
  color: #ef4444;
}

/* Responsive */
@media (max-width: 900px) {
  .profile-container {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 600px) {
  .dashboard-widgets,
  .addresses-grid,
  .vouchers-grid,
  .wishlist-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }
  .form-group.full-width {
    grid-column: span 1;
  }
  .profile-content {
    padding: 20px;
  }
}

/* ── Logout Modal Style ── */
.logout-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.25s ease;
}

.logout-modal-card {
  background: #fff;
  border-radius: 16px;
  padding: 30px;
  width: 100%;
  max-width: 400px;
  text-align: center;
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  animation: scaleUp 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.logout-modal-icon {
  font-size: 40px;
  margin-bottom: 16px;
  display: inline-block;
  animation: bounce 2s infinite;
}

.logout-modal-card h3 {
  font-size: 18px;
  font-weight: 750;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.logout-modal-card p {
  font-size: 14px;
  color: #64748b;
  margin: 0 0 24px 0;
  line-height: 1.5;
}

.logout-modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn-cancel-logout {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel-logout:hover {
  background: #e2e8f0;
}

.btn-confirm-logout {
  background: var(--red, #d70018);
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.25);
  transition: all 0.2s;
}

.btn-confirm-logout:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(215, 0, 24, 0.35);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes scaleUp {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Pagination & Address Actions Styles */
.pagination-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-top: 24px;
}
.btn-page {
  padding: 6px 12px;
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 6px;
  font-size: 14px;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-page:hover:not(:disabled) {
  border-color: var(--red, #d70018);
  color: var(--red, #d70018);
}
.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.page-info {
  font-size: 14px;
  color: #64748b;
}

.addr-actions {
  display: flex;
  gap: 12px;
  margin-top: 12px;
  border-top: 1px dashed #e2e8f0;
  padding-top: 10px;
}
.btn-addr-action {
  font-size: 13px;
  font-weight: 600;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: opacity 0.2s;
}
.btn-addr-action:hover {
  opacity: 0.8;
}
.btn-addr-action.edit {
  color: #3b82f6;
}
.btn-addr-action.delete {
  color: #ef4444;
}

/* ── Profile Details Cards Style ── */
.profile-section-card {
  background: #fff;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.card-header-row-sm {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 6px;
}
.card-header-row-sm h3 {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.btn-text-action {
  background: none;
  border: none;
  color: #d70018;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: opacity 0.2s;
}
.btn-text-action:hover {
  opacity: 0.8;
}

.profile-info-grid {
  display: flex;
  flex-direction: column;
}
.info-row {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #f8fafc;
  padding: 6px 0;
}
.info-row:last-child {
  border-bottom: none;
}
.info-item {
  width: 48%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.info-label {
  color: #64748b;
  font-size: 14px;
}
.info-value {
  color: #1e293b;
  font-size: 14px;
  text-align: right;
}
.info-value.bold {
  font-weight: 600;
}

.addresses-grid-container-new {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 8px;
}
@media (max-width: 768px) {
  .addresses-grid-container-new {
    grid-template-columns: 1fr;
  }
}
.address-card-new {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
  transition: all 0.2s ease;
}
.address-card-new:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}
.address-card-new.default-card {
  border-color: #fee2e2;
  background: #fffbfa;
}
.addr-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.addr-card-label {
  font-weight: 700;
  font-size: 15px;
  color: #0f172a;
}
.addr-card-badge-type {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.addr-card-badge-type i {
  font-size: 10px;
  color: #64748b;
}
.addr-card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-grow: 1;
}
.addr-card-recipient-row {
  display: flex;
  align-items: center;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 2px;
}
.recipient-name {
  color: #0f172a;
}
.recipient-phone {
  color: #334155;
  font-weight: 500;
}
.separator-pipe {
  color: #cbd5e1;
  margin: 0 8px;
  font-weight: 400;
}
.badge-default-addr-new {
  background: #fee2e2;
  color: #d70018;
  font-size: 10px;
  font-weight: 600;
  padding: 1px 5px;
  border-radius: 3px;
  margin-left: 8px;
}
.addr-card-details {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  word-break: break-word;
}
.addr-card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  border-top: 1px dashed #e2e8f0;
  padding-top: 6px;
  margin-top: 4px;
}
.btn-card-action {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  transition: color 0.15s;
}
.btn-card-action.delete {
  color: #64748b;
}
.btn-card-action.delete:hover {
  color: #ef4444;
}
.btn-card-action.edit {
  color: #3b82f6;
}
.btn-card-action.edit:hover {
  color: #2563eb;
}
.separator-pipe-actions {
  color: #e2e8f0;
  margin: 0 8px;
  font-size: 11px;
}

.profile-two-column {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.password-info-body {
  padding: 8px 0;
}
.info-item-single {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.social-links-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.social-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f1f5f9;
}
.social-row-item:last-child {
  border-bottom: none;
}
.social-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.social-logo {
  width: 24px;
  height: 24px;
  object-fit: contain;
}
.social-name {
  font-size: 14px;
  color: #0f172a;
  font-weight: 500;
}
.badge-linked {
  background: #dcfce7;
  color: #15803d;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 8px;
}
.btn-social-action {
  font-size: 13px;
  font-weight: 600;
  border: none;
  background: none;
  cursor: pointer;
}
.btn-social-action.unbind {
  color: #64748b;
}
.btn-social-action.bind {
  color: #d70018;
}

.empty-address-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 10px;
  text-align: center;
}
.empty-address-icon {
  font-size: 64px;
  color: #d70018;
  margin-bottom: 16px;
}
.empty-address-text {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Custom Modal Dialog Styles */
.modal-overlay-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.2s ease;
}
.modal-card-custom {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  overflow: hidden;
  animation: scaleUp 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-card-custom.modal-full-height {
  height: 100vh;
  max-height: 100vh;
  border-radius: 0;
  display: flex;
  flex-direction: column;
  animation: slideInBottom 0.3s ease-out;
}
@keyframes slideInBottom {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}
.modal-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
}
.modal-header-custom h3 {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.btn-close-modal {
  background: none;
  border: none;
  font-size: 16px;
  color: #94a3b8;
  cursor: pointer;
}
.btn-close-modal:hover {
  color: #475569;
}
.modal-body-custom {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.modal-body-custom .form-group {
  margin-bottom: 0;
  gap: 0;
}
.modal-body-custom .form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0px;
}
.modal-body-custom .form-group input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s;
}
.modal-body-custom .form-group input:focus {
  border-color: #d70018;
}
.modal-footer-custom {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 20px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}
.btn-primary-custom {
  background: #d70018;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
}
.btn-secondary-custom {
  background: #cbd5e1;
  color: #475569;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
}
.form-select-custom {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background-color: #fff;
  outline: none;
}
.profile-divider {
  border: none;
  border-top: 1px solid #b90d3b;
  margin: 8px 0;
}

/* Redesigned Address Modal Styles */
.modal-section-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0px;
  text-align: left;
}

.modal-dashed-divider {
  border: none;
  border-top: 1px dashed #e2e8f0;
  margin: 0px 0;
}

.address-type-buttons {
  display: flex;
  gap: 12px;
  margin-top: 0px;
}

.btn-type-option {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  cursor: pointer;
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #475569;
  transition: all 0.2s;
}

.btn-type-option.active {
  border-color: #d70018;
  color: #d70018;
  background: #fff5f5;
  font-weight: 600;
}

.default-toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
}

.full-width-btn {
  width: 100%;
  padding: 12px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 8px;
  text-align: center;
  background: #d70018;
  color: #fff;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
  transition: all 0.2s;
}
.full-width-btn:hover {
  background: #b90d3b;
}

/* Switch toggle */
.switch-custom {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.switch-custom input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider-custom {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: .3s;
  border-radius: 24px;
}
.slider-custom:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
input:checked + .slider-custom {
  background-color: #d70018;
}
input:checked + .slider-custom:before {
  transform: translateX(20px);
}
.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.password-input-wrapper input {
  width: 100%;
  padding-right: 40px !important;
}
.password-icon {
  position: absolute;
  right: 12px;
  cursor: pointer;
  font-size: 16px;
  transition: color 0.2s;
}
.password-icon-gray {
  color: #94a3b8;
}
.password-icon-red {
  color: #d70018;
}

/* ── Searchable Select Custom Styles ── */
.position-relative-custom {
  position: relative;
}

.searchable-select-trigger {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 10px 14px;
  background-color: #fff;
  cursor: pointer;
  font-size: 14px;
  color: #0f172a;
  min-height: 42px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.searchable-select-trigger:hover:not(.disabled) {
  border-color: #94a3b8;
}

.searchable-select-trigger.disabled {
  background-color: #f1f5f9;
  color: #94a3b8;
  cursor: not-allowed;
  border-color: #e2e8f0;
}

.searchable-select-trigger .trigger-icon {
  font-size: 11px;
  color: #94a3b8;
  transition: transform 0.2s;
}

.searchable-select-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 4px;
  background-color: #fff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
  z-index: 1200;
  max-height: 250px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: dropdownFadeIn 0.15s ease-out;
}

@keyframes dropdownFadeIn {
  from { opacity: 0; transform: translateY(2px); }
  to { opacity: 1; transform: translateY(0); }
}

.search-input-wrapper {
  position: relative;
  padding: 8px;
  border-bottom: 1px solid #f1f5f9;
  background-color: #f8fafc;
}

.dropdown-search-input {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 10px 6px 28px;
  font-size: 13px;
  outline: none;
  font-family: inherit;
  box-sizing: border-box;
}

.dropdown-search-input:focus {
  border-color: #d70018;
  box-shadow: 0 0 0 2px rgba(215, 0, 24, 0.1);
}

.search-input-wrapper .search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: #94a3b8;
}

.dropdown-options-list {
  flex: 1;
  overflow-y: auto;
  max-height: 190px;
}

.dropdown-option-item {
  padding: 8px 14px;
  font-size: 13.5px;
  color: #334155;
  cursor: pointer;
  text-align: left;
  transition: background-color 0.15s;
}

.dropdown-option-item:hover {
  background-color: #f1f5f9;
}

.dropdown-option-item.active {
  background-color: #fff1f2;
  color: #d70018;
  font-weight: 600;
}

.no-options-found {
  padding: 12px 14px;
  font-size: 13px;
  color: #94a3b8;
  text-align: center;
}

.spacer-right {
  margin-right: 6px;
}

/* ── Mobile Responsive Specific Optimizations ── */
@media (max-width: 900px) {
  .profile-container {
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 0 12px;
  }
  .user-card-sm {
    flex-direction: row;
    align-items: center;
    text-align: left;
    gap: 16px;
    padding-bottom: 12px;
    margin-bottom: 12px;
  }
  .avatar-wrap {
    width: 60px;
    height: 60px;
    margin-bottom: 0;
  }
  .sidebar-nav {
    flex-direction: row;
    overflow-x: auto;
    gap: 8px;
    padding: 4px 0;
    scrollbar-width: none;
  }
  .sidebar-nav::-webkit-scrollbar {
    display: none;
  }
  .nav-item {
    padding: 8px 14px;
    font-size: 13px;
    width: auto;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .logout-btn-sidebar {
    margin-top: 0;
    border-top: none;
    padding-top: 8px;
    border-radius: 10px;
  }
  .profile-content {
    padding: 12px !important;
  }
}

@media (max-width: 768px) {
  /* Address modal full-screen layout with sticky bottom button */
  .modal-card-custom {
    position: fixed;
    inset: 0;
    width: 100% !important;
    max-width: 100% !important;
    height: 100% !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    display: flex;
    flex-direction: column;
    margin: 0 !important;
    animation: none;
  }
  
  .modal-card-custom form {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
  }

  .modal-body-custom {
    flex: 1;
    overflow-y: auto;
    padding: 16px 16px 80px 16px;
  }

  .modal-footer-custom {
    position: sticky;
    bottom: 0;
    width: 100%;
    background: #f8fafc;
    padding: 12px 16px !important;
    border-top: 1px solid #e2e8f0;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
    z-index: 10;
    box-sizing: border-box;
  }

  /* Address Card touch target optimization */
  .addr-card-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
  }
  .separator-pipe-actions {
    display: none;
  }
  .btn-card-action {
    padding: 8px 16px;
    font-size: 13px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    min-width: 84px;
    box-sizing: border-box;
  }
  .btn-card-action.delete {
    background: #f1f5f9;
    color: #64748b;
  }
  .btn-card-action.edit {
    background: #eff6ff;
    color: #3b82f6;
  }
}
</style>
