<template>
  <div class="checkout-page">
    <div class="checkout-container">
      
      <!-- Normal Checkout View -->
      <template v-if="!orderSuccess">
        <h1 class="checkout-title">Thanh Toán Đơn Hàng</h1>
        
        <!-- Loading initial data state -->
        <div v-if="loading" class="checkout-loading">
          <i class="fa-solid fa-circle-notch fa-spin"></i>
          <span>Đang chuẩn bị thông tin thanh toán...</span>
        </div>

        <div v-else class="checkout-grid">
          <!-- Left Column: Shipping & Payment -->
          <div class="checkout-section-column">
            
            <!-- Address Selection Section -->
            <div class="checkout-card">
              <!-- Logged In Address List & Form -->
              <template v-if="isLoggedIn">
                <div class="card-header-with-action">
                  <h2 class="section-card-title">
                    <i class="fa-solid fa-map-location-dot"></i> Địa chỉ giao hàng
                  </h2>
                  <button 
                    v-if="addresses.length > 0" 
                    class="btn-toggle-address-form" 
                    @click="showAddAddressForm = !showAddAddressForm"
                  >
                    {{ showAddAddressForm ? 'Hủy' : '+ Thêm địa chỉ mới' }}
                  </button>
                </div>

                <!-- List of Saved Addresses -->
                <div v-if="addresses.length > 0 && !showAddAddressForm" class="address-cards-list">
                  <div 
                    v-for="addr in addresses" 
                    :key="addr.id" 
                    class="address-card-item"
                    :class="{ 'active': selectedAddressId === addr.id }"
                    @click="selectedAddressId = addr.id"
                  >
                    <div class="addr-radio-wrapper">
                      <span class="custom-radio" :class="{ 'checked': selectedAddressId === addr.id }"></span>
                    </div>
                    <div class="addr-details">
                      <div class="addr-row-header">
                        <span class="addr-phone">
                          <i class="fa-solid fa-phone"></i> {{ addr.so_dien_thoai }}
                        </span>
                        <span v-if="addr.la_mac_dinh" class="addr-badge-default">Mặc định</span>
                      </div>
                      <p class="addr-text">
                        {{ addr.dia_chi_chi_tiet }}, {{ addr.phuong_xa }}, {{ addr.quan_huyen }}, {{ addr.thanh_pho }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Address Form (Displays if empty or user clicks + Add address) -->
                <div v-if="addresses.length === 0 || showAddAddressForm" class="add-address-form">
                  <h3 class="form-title">
                    {{ addresses.length === 0 ? 'Vui lòng cung cấp địa chỉ nhận hàng' : 'Nhập địa chỉ giao hàng mới' }}
                  </h3>
                  
                  <div class="form-grid">
                    <div class="input-group">
                      <label>Số điện thoại liên hệ <span class="required-asterisk">*</span></label>
                      <input 
                        type="text" 
                        placeholder="Ví dụ: 0987654321" 
                        v-model="newAddress.so_dien_thoai"
                      />
                    </div>

                    <div class="input-group">
                      <label>Tỉnh / Thành phố <span class="required-asterisk">*</span></label>
                      <select v-model="newAddress.thanh_pho_id" @change="onProvinceChange('member')" class="address-select">
                        <option value="">-- Chọn Tỉnh / Thành phố --</option>
                        <option v-for="prov in provincesList" :key="prov.PROVINCE_ID" :value="prov.PROVINCE_ID">
                          {{ prov.PROVINCE_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group">
                      <label>Quận / Huyện <span class="required-asterisk">*</span></label>
                      <select v-model="newAddress.quan_huyen_id" @change="onDistrictChange('member')" :disabled="!newAddress.thanh_pho_id || loadingDistricts" class="address-select">
                        <option value="">-- Chọn Quận / Huyện --</option>
                        <option v-for="dist in districtsList" :key="dist.DISTRICT_ID" :value="dist.DISTRICT_ID">
                          {{ dist.DISTRICT_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group">
                      <label>Phường / Xã <span class="required-asterisk">*</span></label>
                      <select v-model="newAddress.phuong_xa_id" @change="onWardChange('member')" :disabled="!newAddress.quan_huyen_id || loadingWards" class="address-select">
                        <option value="">-- Chọn Phường / Xã --</option>
                        <option v-for="w in wardsList" :key="w.WARDS_ID" :value="w.WARDS_ID">
                          {{ w.WARDS_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group full-width">
                      <label>Địa chỉ chi tiết (Số nhà, tên đường...) <span class="required-asterisk">*</span></label>
                      <input 
                        type="text" 
                        placeholder="Ví dụ: Số 15, Ngõ 20 Trần Thái Tông" 
                        v-model="newAddress.dia_chi_chi_tiet"
                      />
                    </div>

                    <div class="input-group checkbox-group full-width">
                      <label class="checkbox-label">
                        <input type="checkbox" v-model="newAddress.la_mac_dinh" />
                        <span>Đặt làm địa chỉ mặc định</span>
                      </label>
                    </div>
                  </div>

                  <div class="form-actions">
                    <button 
                      v-if="addresses.length > 0" 
                      type="button" 
                      class="btn-form-cancel" 
                      @click="showAddAddressForm = false"
                    >
                      Hủy bỏ
                    </button>
                    <button 
                      type="button" 
                      class="btn-form-save" 
                      :disabled="isSavingAddress"
                      @click="saveNewAddress"
                    >
                      <i class="fa-solid fa-spinner fa-spin" v-if="isSavingAddress"></i>
                      Lưu địa chỉ
                    </button>
                  </div>
                </div>
              </template>

              <!-- Guest Checkout Info & Address Form -->
              <template v-else>
                <div class="card-header-with-action">
                  <h2 class="section-card-title">
                    <i class="fa-solid fa-user-pen"></i> Thông tin mua hàng (Không cần đăng nhập)
                  </h2>
                </div>

                <div class="add-address-form">
                  <div class="form-grid">
                    <div class="input-group">
                      <label>Họ và tên người nhận <span class="required-asterisk">*</span></label>
                      <input 
                        type="text" 
                        placeholder="Nhập họ tên người nhận..." 
                        v-model="guestInfo.ho_ten"
                      />
                    </div>

                    <div class="input-group">
                      <label>Số điện thoại liên hệ <span class="required-asterisk">*</span></label>
                      <input 
                        type="text" 
                        placeholder="Ví dụ: 0987654321" 
                        v-model="guestInfo.so_dien_thoai"
                      />
                    </div>

                    <div class="input-group">
                      <label>Tỉnh / Thành phố <span class="required-asterisk">*</span></label>
                      <select v-model="guestInfo.thanh_pho_id" @change="onProvinceChange('guest')" class="address-select">
                        <option value="">-- Chọn Tỉnh / Thành phố --</option>
                        <option v-for="prov in provincesList" :key="prov.PROVINCE_ID" :value="prov.PROVINCE_ID">
                          {{ prov.PROVINCE_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group">
                      <label>Quận / Huyện <span class="required-asterisk">*</span></label>
                      <select v-model="guestInfo.quan_huyen_id" @change="onDistrictChange('guest')" :disabled="!guestInfo.thanh_pho_id || loadingDistricts" class="address-select">
                        <option value="">-- Chọn Quận / Huyện --</option>
                        <option v-for="dist in districtsList" :key="dist.DISTRICT_ID" :value="dist.DISTRICT_ID">
                          {{ dist.DISTRICT_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group">
                      <label>Phường / Xã <span class="required-asterisk">*</span></label>
                      <select v-model="guestInfo.phuong_xa_id" @change="onWardChange('guest')" :disabled="!guestInfo.quan_huyen_id || loadingWards" class="address-select">
                        <option value="">-- Chọn Phường / Xã --</option>
                        <option v-for="w in wardsList" :key="w.WARDS_ID" :value="w.WARDS_ID">
                          {{ w.WARDS_NAME }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group full-width">
                      <label>Địa chỉ chi tiết (Số nhà, tên đường...) <span class="required-asterisk">*</span></label>
                      <input 
                        type="text" 
                        placeholder="Ví dụ: Số 15, Ngõ 20 Trần Thái Tông" 
                        v-model="guestInfo.dia_chi_chi_tiet"
                      />
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Payment Methods Section -->
            <div class="checkout-card">
              <h2 class="section-card-title">
                <i class="fa-solid fa-credit-card"></i> Phương thức thanh toán
              </h2>
              
              <div class="payment-methods-grid">
                <!-- COD Payment Method -->
                <div 
                  class="payment-method-card"
                  :class="{ 'active': selectedPaymentMethod === 'cod' }"
                  @click="selectedPaymentMethod = 'cod'"
                >
                  <div class="pay-icon-wrapper">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                  </div>
                  <div class="pay-info">
                    <h4>COD (Thanh toán khi nhận hàng)</h4>
                    <p>Nhận hàng và thanh toán trực tiếp tại nhà.</p>
                  </div>
                  <div class="pay-checkbox">
                    <span class="custom-radio" :class="{ 'checked': selectedPaymentMethod === 'cod' }"></span>
                  </div>
                </div>

                <!-- Bank Transfer Payment Method -->
                <div 
                  class="payment-method-card"
                  :class="{ 'active': selectedPaymentMethod === 'online_banking' }"
                  @click="selectedPaymentMethod = 'online_banking'"
                >
                  <div class="pay-icon-wrapper">
                    <i class="fa-solid fa-building-columns"></i>
                  </div>
                  <div class="pay-info">
                    <h4>Chuyển khoản ngân hàng (QR Code)</h4>
                    <p>Thông tin chuyển khoản sẽ xuất hiện sau khi đặt hàng.</p>
                  </div>
                  <div class="pay-checkbox">
                    <span class="custom-radio" :class="{ 'checked': selectedPaymentMethod === 'online_banking' }"></span>
                  </div>
                </div>
              </div>

              <!-- Bank Transfer instruction display -->
              <transition name="fade">
                <div v-if="selectedPaymentMethod === 'online_banking'" class="bank-details-instruction">
                  <div class="bank-title">Thanh toán qua mã QR VietQR (Napas 247)</div>
                  <p style="font-size: 13px; color: #4a5568; margin: 0; line-height: 1.5;">
                    Mã QR và thông tin tài khoản chuyển khoản sẽ hiển thị sau khi bạn bấm <strong>Đặt Hàng Ngay</strong>. Bạn chỉ cần mở app ngân hàng quét mã QR để thanh toán tự động mà không cần nhập thủ công số tài khoản & nội dung chuyển khoản.
                  </p>
                </div>
              </transition>
            </div>

            <!-- Customer Notes Section -->
            <div class="checkout-card">
              <h2 class="section-card-title">
                <i class="fa-solid fa-pen-clip"></i> Ghi chú giao hàng
              </h2>
              <textarea 
                class="customer-notes-textarea" 
                placeholder="Nhập ghi chú cho đơn vị vận chuyển (ví dụ: giao giờ hành chính, gọi trước khi đến...)" 
                v-model="customerNotes"
                rows="3"
              ></textarea>
            </div>

          </div>

          <!-- Right Column: Order Items & Pricing Summary -->
          <div class="checkout-summary-column">
            
            <!-- Checklist items card -->
            <div class="checkout-card summary-card">
              <h2 class="summary-card-title">Chi tiết đơn hàng</h2>
              
              <div class="order-items-list-scroll">
                <div 
                  v-for="item in cartItems" 
                  :key="item.id_bien_the" 
                  class="order-item-mini"
                >
                  <img :src="getProductImageUrl(item.hinh_anh)" :alt="item.ten_san_pham" class="item-mini-img" />
                  <div class="item-mini-details">
                    <h4 class="item-mini-name">{{ item.ten_san_pham }}</h4>
                    <span class="item-mini-variant">{{ item.ten_bien_the || 'Mặc định' }}</span>
                    <div class="item-mini-price-row">
                      <span class="item-mini-qty">x {{ item.so_luong }}</span>
                      <span class="item-mini-price">{{ formatCurrency(item.gia_ban * item.so_luong) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Price calculations summary -->
              <div class="summary-price-breakdown">
                <div class="price-row">
                  <span>Tạm tính</span>
                  <span class="val-bold">{{ formatCurrency(cartTotal) }}</span>
                </div>
                
                <div class="price-row" v-if="voucherDiscountAmount > 0">
                  <span>Giảm giá mã</span>
                  <span class="val-bold discount">-{{ formatCurrency(voucherDiscountAmount) }}</span>
                </div>

                <div class="price-row">
                  <span>Phí vận chuyển</span>
                  <span class="val-bold">{{ (isLoggedIn && userTier === 'vang') ? 'Miễn phí' : '30.000đ' }}</span>
                </div>

                <!-- Coin discount row -->
                <div class="price-row" v-if="coinDiscountAmount > 0">
                  <span>Giảm giá bằng xu</span>
                  <span class="val-bold discount">-{{ formatCurrency(coinDiscountAmount) }}</span>
                </div>

                <hr class="summary-divider" />

                <!-- Add Coins option & Voucher option -->
                <div class="summary-discount-options">
                  <!-- Coins option -->
                  <div v-if="isLoggedIn && userCoins >= 10 && maxCoinsAllowedToUse > 0" class="coin-usage-row">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                      <input type="checkbox" v-model="useCoins" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer" />
                      <span class="text-sm font-semibold text-gray-700">
                        Dùng <strong class="text-yellow-600">{{ maxCoinsAllowedToUse }}</strong> xu để giảm <strong class="text-yellow-600">{{ formatCurrency(maxCoinsAllowedToUse * 1000) }}</strong> (Ví của bạn có {{ userCoins }} xu)
                      </span>
                    </label>
                  </div>

                  <!-- Voucher option -->
                  <div class="voucher-usage-block">
                    <div class="voucher-input-group" v-if="!appliedVoucher">
                      <input 
                        type="text" 
                        placeholder="Nhập mã giảm giá..." 
                        v-model="voucherCode"
                        class="voucher-checkout-input"
                        @keyup.enter="applyVoucher"
                      />
                      <button 
                        type="button" 
                        class="btn-apply-voucher-checkout"
                        :disabled="isValidatingVoucher"
                        @click="applyVoucher"
                      >
                        <i class="fa-solid fa-spinner fa-spin" v-if="isValidatingVoucher"></i>
                        Áp dụng
                      </button>
                    </div>
                    
                    <!-- Wallet Vouchers Dropdown Selection -->
                    <div class="select-wallet-voucher" v-if="isLoggedIn && !appliedVoucher && myVouchers.length > 0">
                      <button type="button" class="btn-select-voucher-wallet" @click="showVoucherDropdown = !showVoucherDropdown">
                        🎟️ Chọn voucher từ ví (Có {{ myVouchers.length }} mã khả dụng)
                      </button>
                      <div class="voucher-wallet-dropdown-panel" v-if="showVoucherDropdown">
                        <div 
                          v-for="v in myVouchers" 
                          :key="v.id" 
                          class="voucher-dropdown-item"
                          :class="{ 'disabled': cartTotal < v.minOrder }"
                          @click="selectVoucherFromWallet(v)"
                        >
                          <div class="vd-info">
                            <span class="vd-code">{{ v.code }}</span>
                            <span class="vd-desc">{{ v.title }}</span>
                            <span class="vd-min-order">Đơn tối thiểu: {{ formatCurrency(v.minOrder) }}</span>
                          </div>
                          <button 
                            type="button" 
                            class="btn-select-v"
                            :disabled="cartTotal < v.minOrder"
                          >
                            Áp dụng
                          </button>
                        </div>
                      </div>
                    </div>

                    <div class="voucher-applied-display" v-else-if="appliedVoucher">
                      <div class="voucher-applied-tag">
                        <i class="fa-solid fa-ticket text-red-500"></i>
                        <span>{{ appliedVoucher.ma_code }}</span>
                        <button type="button" @click="removeVoucher" class="btn-remove-voucher-tag">&times;</button>
                      </div>
                    </div>
                    <p class="voucher-msg-checkout success" v-if="voucherSuccess">{{ voucherSuccess }}</p>
                    <p class="voucher-msg-checkout error" v-if="voucherError">{{ voucherError }}</p>
                  </div>
                </div>

                <hr class="summary-divider" />

                <div class="price-row total">
                  <span>Tổng tiền thanh toán</span>
                  <span class="val-total-payable">{{ formatCurrency(finalTotal) }}</span>
                </div>
              </div>

              <!-- Submit button -->
              <button 
                class="btn-place-order" 
                :disabled="isSubmittingOrder || (isLoggedIn && !selectedAddressId) || (!isLoggedIn && !isGuestFormValid)"
                @click="submitOrder"
              >
                <i class="fa-solid fa-spinner fa-spin" v-if="isSubmittingOrder"></i>
                <span>{{ isSubmittingOrder ? ' Đang xử lý đơn hàng...' : 'Đặt Hàng Ngay' }}</span>
              </button>
              
              <p v-if="!selectedAddressId" class="address-warning-msg">
                * Vui lòng chọn hoặc thêm địa chỉ nhận hàng để tiếp tục đặt hàng.
              </p>
            </div>

          </div>
        </div>
      </template>

      <!-- Order Success Splash View -->
      <template v-else>
        <div class="order-success-card glass-modal">
          <div class="success-icon-wrapper">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
              <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
              <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
          </div>
          
          <h2 class="success-headline">Đặt Hàng Thành Công!</h2>
          <p class="success-subtext">Cảm ơn bạn đã tin tưởng mua sắm tại cửa hàng của chúng tôi.</p>
          
          <div class="success-details-box">
            <div class="detail-row">
              <span>Mã đơn hàng:</span>
              <strong class="order-code">{{ createdOrder.ma_don_hang }}</strong>
            </div>
            <div class="detail-row">
              <span>Trạng thái:</span>
              <span class="status-badge">Chờ xử lý</span>
            </div>
            <div class="detail-row">
              <span>Tổng tiền thanh toán:</span>
              <strong class="payable-red">{{ formatCurrency(createdOrder.tong_thanh_toan) }}</strong>
            </div>
            <div class="detail-row">
              <span>Phương thức thanh toán:</span>
              <span>{{ createdOrder.phuong_thuc === 'cod' ? 'COD (Thanh toán khi nhận hàng)' : 'Chuyển khoản ngân hàng' }}</span>
            </div>
          </div>

          <div class="success-actions">
            <button class="btn-success-history" @click="$router.push('/thong-tin-ca-nhan')">
              <i class="fa-solid fa-list-check"></i> Xem lịch sử đơn hàng
            </button>
            <button class="btn-success-home" @click="$router.push('/san-pham')">
              Tiếp tục mua sắm
            </button>
          </div>
        </div>
      </template>

    </div>

    <!-- QR Payment Modal -->
    <transition name="fade">
      <div v-if="showQRModal" class="qr-modal-overlay">
        <div class="qr-modal-card">
          <div class="qr-modal-header">
            <h3 class="qr-modal-title">
              <i class="fa-solid fa-qrcode"></i> Thanh Toán Chuyển Khoản
            </h3>
            <button class="qr-modal-close-btn" @click="closeQRModal">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          
          <div class="qr-modal-body">
            <p class="qr-modal-subtitle">Vui lòng quét mã QR dưới đây để thực hiện thanh toán chuyển khoản nhanh chóng qua Napas 247.</p>
            
            <div v-if="createdOrder && createdOrder.qr_url" class="qr-modal-image-wrapper">
              <img :src="createdOrder.qr_url" alt="VietQR Code" class="qr-modal-img" />
              <div class="qr-modal-badge">
                <i class="fa-solid fa-qrcode"></i> Quét QR qua ứng dụng Ngân hàng
              </div>
            </div>
            
            <div v-if="createdOrder && createdOrder.bank_info" class="qr-modal-bank-info">
              <div class="qr-info-row">
                <span class="lbl">Ngân hàng:</span>
                <span class="val uppercase">{{ createdOrder.bank_info.bank_id }}</span>
              </div>
              <div class="qr-info-row">
                <span class="lbl">Số tài khoản:</span>
                <span class="val highlight font-mono">{{ createdOrder.bank_info.bank_account_no }}</span>
              </div>
              <div class="qr-info-row">
                <span class="lbl">Chủ tài khoản:</span>
                <span class="val uppercase">{{ createdOrder.bank_info.bank_account_name }}</span>
              </div>
              <div class="qr-info-row">
                <span class="lbl">Số tiền:</span>
                <span class="val highlight">{{ formatCurrency(createdOrder.tong_thanh_toan) }}</span>
              </div>
              <div class="qr-info-row">
                <span class="lbl">Nội dung CK:</span>
                <span class="val highlight font-mono">{{ createdOrder.ma_don_hang }}</span>
              </div>
            </div>
          </div>
          
          <div class="qr-modal-footer">
            <button class="btn-cancel" @click="closeQRModal">Đóng</button>
            <button class="btn-paid" @click="confirmPaid">
              <i class="fa-solid fa-circle-check"></i> Đã thanh toán
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import { useCartStore } from "../../store/cartStore";

export default {
  name: "Checkout",
  data() {
    return {
      cartStore: null,
      loading: true,
      isLoggedIn: false,
      addresses: [],
      selectedAddressId: null,
      selectedPaymentMethod: "cod",
      customerNotes: "",
      
      // Administrative Lists
      provincesList: [],
      districtsList: [],
      wardsList: [],
      loadingDistricts: false,
      loadingWards: false,

      // Guest Form Information
      guestInfo: {
        ho_ten: "",
        so_dien_thoai: "",
        thanh_pho: "",
        thanh_pho_id: "",
        quan_huyen: "",
        quan_huyen_id: "",
        phuong_xa: "",
        phuong_xa_id: "",
        dia_chi_chi_tiet: ""
      },

      // Add Address inline form states for Member
      showAddAddressForm: false,
      isSavingAddress: false,
      newAddress: {
        so_dien_thoai: "",
        dia_chi_chi_tiet: "",
        thanh_pho: "",
        thanh_pho_id: "",
        quan_huyen: "",
        quan_huyen_id: "",
        phuong_xa: "",
        phuong_xa_id: "",
        la_mac_dinh: false
      },

      // Submit state
      isSubmittingOrder: false,
      orderSuccess: false,
      showQRModal: false,
      createdOrder: null,

      // Voucher & Coins properties
      voucherCode: "",
      appliedVoucher: null,
      voucherError: "",
      voucherSuccess: "",
      isValidatingVoucher: false,
      useCoins: false,
      userCoins: 0,
      userTier: 'dong',
      buyNowItem: null,
      myVouchers: [],
      showVoucherDropdown: false,
    };
  },
  computed: {
    cartItems() {
      if (this.buyNowItem) return [this.buyNowItem];
      return this.cartStore ? this.cartStore.items.filter(item => item.isSelected !== false) : [];
    },
    cartTotal() {
      const items = this.cartItems;
      return items.reduce((total, item) => {
        let giaBan = parseFloat(item.gia_ban || 0);
        const isPhuKien = ['dung-cu-lap-rap-cat-got', 'son-va-hoa-chat-mo-hinh', 'dung-cu-ca-nhan'].includes(item.duong_dan_mau_danh_muc);
        if (isPhuKien && this.isLoggedIn) {
          if (this.userTier === 'bac') {
            giaBan = giaBan * 0.99; // Giảm 1%
          } else if (this.userTier === 'vang') {
            giaBan = giaBan * 0.95; // Giảm 5%
          }
        }
        return total + (giaBan * item.so_luong);
      }, 0);
    },
    voucherDiscountAmount() {
      if (!this.appliedVoucher) return 0;
      const v = this.appliedVoucher;
      if (this.cartTotal < v.don_hang_toi_thieu) return 0;
      if (v.loai_giam_gia === 'phan_tram') {
        let discount = (this.cartTotal * v.gia_tri_giam) / 100;
        if (v.muc_giam_toi_da) {
          discount = Math.min(discount, v.muc_giam_toi_da);
        }
        return discount;
      } else {
        return v.gia_tri_giam;
      }
    },
    maxCoinsAllowedToUse() {
      if (!this.isLoggedIn || this.userCoins < 10) return 0;
      const remainingPayableBeforeCoins = Math.max(0, this.cartTotal - this.voucherDiscountAmount);
      const maxDiscountMoney = remainingPayableBeforeCoins * 0.5; // Tối đa 50%
      const maxCoinsAllowed = Math.floor(maxDiscountMoney / 1000);
      return Math.min(this.userCoins, Math.min(500, maxCoinsAllowed));
    },
    coinDiscountAmount() {
      if (!this.useCoins) return 0;
      return this.maxCoinsAllowedToUse * 1000;
    },
    finalTotal() {
      const phiShip = (this.isLoggedIn && this.userTier === 'vang') ? 0 : 30000;
      return Math.max(0, this.cartTotal - this.voucherDiscountAmount - this.coinDiscountAmount + phiShip);
    },
    isGuestFormValid() {
      const gi = this.guestInfo;
      return !!(
        gi.ho_ten.trim() &&
        gi.so_dien_thoai.trim() &&
        gi.thanh_pho.trim() &&
        gi.quan_huyen.trim() &&
        gi.phuong_xa.trim() &&
        gi.dia_chi_chi_tiet.trim()
      );
    }
  },
  async created() {
    // 1. Check Login
    const token = localStorage.getItem("token_client");
    this.isLoggedIn = !!token;

    // 2. Initialize Cart Store
    this.cartStore = useCartStore();
    await this.cartStore.loadCart();
    const buyNowRaw = sessionStorage.getItem("buy_now_checkout_item");
    if (buyNowRaw) {
      try {
        this.buyNowItem = JSON.parse(buyNowRaw);
      } catch (e) {
        this.buyNowItem = null;
        sessionStorage.removeItem("buy_now_checkout_item");
      }
    }

    // Redirect if no selected products
    if (this.cartItems.length === 0) {
      this.showToast("Bạn chưa chọn sản phẩm nào để thanh toán!", "warning");
      this.$router.push("/gio-hang");
      return;
    }

    // 3. Load administrative provinces
    await this.fetchProvinces();

    // 4. Load user addresses & profile if logged in
    if (this.isLoggedIn) {
      await Promise.all([
        this.loadAddresses(),
        this.loadUserProfile(),
        this.loadMyVouchers()
      ]);
    }
    this.loading = false;
  },
  methods: {
    getProductImageUrl(imagePath) {
      if (!imagePath) return "https://via.placeholder.com/60";
      if (imagePath.startsWith("http")) return imagePath;
      return "" + (imagePath.startsWith("/") ? "" : "/") + imagePath;
    },

    formatCurrency(val) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(val);
    },

    showToast(message, type = "success") {
      if (this.$toast) {
        this.$toast[type](message);
      } else {
        console.log(`[Toast ${type}]: ${message}`);
      }
    },

    async fetchProvinces() {
      try {
        const res = await axios.get("/api/viettelpost/provinces");
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
        const res = await axios.get(`/api/viettelpost/districts/${provinceId}`);
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
        const res = await axios.get(`/api/viettelpost/wards/${districtId}`);
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

    onProvinceChange(formType) {
      const target = formType === 'guest' ? this.guestInfo : this.newAddress;
      target.quan_huyen_id = "";
      target.quan_huyen = "";
      target.phuong_xa_id = "";
      target.phuong_xa = "";
      this.districtsList = [];
      this.wardsList = [];
      
      const selectedProv = this.provincesList.find(p => p.PROVINCE_ID === target.thanh_pho_id);
      if (selectedProv) {
        target.thanh_pho = selectedProv.PROVINCE_NAME;
        this.fetchDistricts(target.thanh_pho_id);
      } else {
        target.thanh_pho = "";
      }
    },

    onDistrictChange(formType) {
      const target = formType === 'guest' ? this.guestInfo : this.newAddress;
      target.phuong_xa_id = "";
      target.phuong_xa = "";
      this.wardsList = [];
      
      const selectedDist = this.districtsList.find(d => d.DISTRICT_ID === target.quan_huyen_id);
      if (selectedDist) {
        target.quan_huyen = selectedDist.DISTRICT_NAME;
        this.fetchWards(target.quan_huyen_id);
      } else {
        target.quan_huyen = "";
      }
    },

    onWardChange(formType) {
      const target = formType === 'guest' ? this.guestInfo : this.newAddress;
      const selectedWard = this.wardsList.find(w => w.WARDS_ID === target.phuong_xa_id);
      if (selectedWard) {
        target.phuong_xa = selectedWard.WARDS_NAME;
      } else {
        target.phuong_xa = "";
      }
    },

    async loadAddresses() {
      const token = localStorage.getItem("token_client");
      try {
        const res = await axios.get("/api/khach-hang/dia-chi", {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (res.data) {
          this.addresses = res.data;
          // Pre-select default address
          const defAddr = this.addresses.find(a => a.la_mac_dinh);
          if (defAddr) {
            this.selectedAddressId = defAddr.id;
          } else if (this.addresses.length > 0) {
            this.selectedAddressId = this.addresses[0].id;
          }
        }
      } catch (error) {
        console.error("Lỗi tải danh sách địa chỉ:", error);
        this.showToast("Không thể tải thông tin địa chỉ.", "error");
      }
    },

    async saveNewAddress() {
      // Validate inputs
      const na = this.newAddress;
      if (!na.so_dien_thoai.trim() || !na.dia_chi_chi_tiet.trim() || !na.thanh_pho.trim() || !na.quan_huyen.trim() || !na.phuong_xa.trim()) {
        this.showToast("Vui lòng nhập đầy đủ các trường thông tin địa chỉ bắt buộc (*)", "warning");
        return;
      }

      this.isSavingAddress = true;
      const token = localStorage.getItem("token_client");

      try {
        const res = await axios.post("/api/khach-hang/dia-chi", {
          so_dien_thoai: na.so_dien_thoai,
          dia_chi_chi_tiet: na.dia_chi_chi_tiet,
          thanh_pho: na.thanh_pho,
          thanh_pho_id: na.thanh_pho_id,
          quan_huyen: na.quan_huyen,
          quan_huyen_id: na.quan_huyen_id,
          phuong_xa: na.phuong_xa,
          phuong_xa_id: na.phuong_xa_id,
          la_mac_dinh: na.la_mac_dinh
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });

        if (res.data) {
          this.showToast("Thêm địa chỉ giao hàng mới thành công.", "success");
          
          // Clear address inputs
          this.newAddress = {
            so_dien_thoai: "",
            dia_chi_chi_tiet: "",
            thanh_pho: "",
            thanh_pho_id: "",
            quan_huyen: "",
            quan_huyen_id: "",
            phuong_xa: "",
            phuong_xa_id: "",
            la_mac_dinh: false
          };

          this.showAddAddressForm = false;
          // Reload list
          await this.loadAddresses();
          
          // Select newly created address
          if (res.data.id) {
            this.selectedAddressId = res.data.id;
          }
        }
      } catch (error) {
        console.error("Lỗi khi thêm địa chỉ:", error);
        this.showToast("Thêm địa chỉ mới thất bại.", "error");
      } finally {
        this.isSavingAddress = false;
      }
    },

    async loadUserProfile() {
      const token = localStorage.getItem("token_client");
      if (!token) return;
      try {
        const res = await axios.get("/api/thong-tin-ca-nhan/profile", {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (res.data && res.data.status === 1) {
          this.userCoins = res.data.data.diem_thanh_vien || 0;
          this.userTier = res.data.data.hang_thanh_vien || 'dong';
        }
      } catch (error) {
        console.error("Lỗi tải thông tin cá nhân:", error);
      }
    },

    async loadMyVouchers() {
      const token = localStorage.getItem("token_client");
      if (!token) return;
      try {
        const res = await axios.get("/api/khach-hang/ma-giam-gia/my-vouchers", {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (res.data && res.data.status) {
          // Chỉ lấy voucher còn sử dụng được (trang_thai = unused, active = true)
          this.myVouchers = res.data.data.filter(v => v.trang_thai === 'unused' && v.active);
        }
      } catch (error) {
        console.error("Lỗi khi tải ví voucher:", error);
      }
    },

    selectVoucherFromWallet(v) {
      if (this.cartTotal < v.minOrder) {
        this.showToast(`Đơn hàng chưa đạt giá trị tối thiểu ${this.formatCurrency(v.minOrder)}!`, "warning");
        return;
      }
      this.appliedVoucher = {
        id: v.id_ma_giam_gia,
        ma_code: v.code,
        loai_giam_gia: v.type === 'Phần trăm' ? 'phan_tram' : 'tien_mat',
        gia_tri_giam: v.value,
        don_hang_toi_thieu: v.minOrder,
        muc_giam_toi_da: v.maxDiscount,
      };
      this.voucherCode = v.code;
      this.voucherSuccess = "Áp dụng voucher từ ví thành công!";
      this.showVoucherDropdown = false;
      this.showToast("Áp dụng mã giảm giá thành công!", "success");
    },

    async applyVoucher() {
      if (!this.voucherCode.trim()) {
        this.voucherError = "Vui lòng nhập mã giảm giá.";
        this.voucherSuccess = "";
        return;
      }
      this.isValidatingVoucher = true;
      this.voucherError = "";
      this.voucherSuccess = "";
      try {
        const code = this.voucherCode.toUpperCase().trim();
        const token = localStorage.getItem("token_client");
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        const res = await axios.get(`/api/ma-giam-gia/kiem-tra/${code}`, config);
        if (res.data && res.data.status) {
          const voucher = res.data.voucher;
          if (this.cartTotal < voucher.don_hang_toi_thieu) {
            this.voucherError = `Đơn hàng tối thiểu để áp dụng mã này là ${this.formatCurrency(voucher.don_hang_toi_thieu)}.`;
            this.appliedVoucher = null;
          } else {
            this.appliedVoucher = voucher;
            this.voucherSuccess = `Áp dụng mã giảm giá thành công!`;
            this.showToast("Áp dụng mã giảm giá thành công!", "success");
          }
        }
      } catch (error) {
        console.error("Lỗi khi kiểm tra mã giảm giá:", error);
        const errMsg = error.response?.data?.message || "Mã giảm giá không hợp lệ hoặc đã hết hạn.";
        this.voucherError = errMsg;
        this.appliedVoucher = null;
      } finally {
        this.isValidatingVoucher = false;
      }
    },

    removeVoucher() {
      this.appliedVoucher = null;
      this.voucherCode = "";
      this.voucherSuccess = "";
      this.voucherError = "";
    },

    async submitOrder() {
      if (this.isLoggedIn && !this.selectedAddressId) {
        this.showToast("Vui lòng chọn địa chỉ giao hàng!", "warning");
        return;
      }
      if (!this.isLoggedIn && !this.isGuestFormValid) {
        this.showToast("Vui lòng nhập đầy đủ thông tin nhận hàng bắt buộc (*)", "warning");
        return;
      }

      this.isSubmittingOrder = true;
      const token = localStorage.getItem("token_client");
      const headers = {};
      if (token) {
        headers.Authorization = `Bearer ${token}`;
      }

      // Format items payload
      const payloadItems = this.cartItems.map(item => ({
        id_bien_the: item.id_bien_the,
        so_luong: item.so_luong
      }));

      // Construct order payload
      const payload = {
        items: payloadItems,
        phuong_thuc_thanh_toan: this.selectedPaymentMethod === 'cod' ? 'tien_mat' : (this.selectedPaymentMethod === 'online_banking' ? 'chuyen_khoan' : 'vnpay'),
        ghi_chu_khach_hang: this.customerNotes,
        ma_giam_gia: this.appliedVoucher ? this.appliedVoucher.ma_code : null,
        dung_xu: this.useCoins
      };

      if (this.isLoggedIn) {
        payload.id_dia_chi = this.selectedAddressId;
      } else {
        payload.id_dia_chi = null;
        payload.ho_ten = this.guestInfo.ho_ten;
        payload.so_dien_thoai = this.guestInfo.so_dien_thoai;
        payload.thanh_pho = this.guestInfo.thanh_pho;
        payload.quan_huyen = this.guestInfo.quan_huyen;
        payload.phuong_xa = this.guestInfo.phuong_xa;
        payload.dia_chi_chi_tiet = this.guestInfo.dia_chi_chi_tiet;
      }

      try {
        const res = await axios.post("/api/khach-hang/dat-hang", payload, {
          headers: headers
        });

        if (res.data && res.data.order) {
          // Chỉ hiển thị thông báo thành công lập tức nếu KHÔNG phải chuyển khoản (online_banking)
          if (this.selectedPaymentMethod !== 'online_banking') {
            this.showToast("Đặt hàng thành công!", "success");
          }
          
          // Chỉ xóa các sản phẩm đã được chọn thanh toán, giữ lại phần còn lại
          if (this.buyNowItem) {
            sessionStorage.removeItem("buy_now_checkout_item");
            this.buyNowItem = null;
          } else {
            await this.cartStore.removeSelectedItems();
          }
          
          // Show Success View
          const savedOrder = res.data.order;
          this.createdOrder = {
            id: savedOrder.id,
            ma_don_hang: savedOrder.ma_don_hang,
            tong_thanh_toan: savedOrder.tong_thanh_toan,
            phuong_thuc: this.selectedPaymentMethod,
            qr_url: savedOrder.qr_url || null,
            bank_info: savedOrder.bank_info || null
          };

          if (this.selectedPaymentMethod === 'online_banking') {
            this.showQRModal = true;
          } else {
            this.orderSuccess = true;
            window.scrollTo({ top: 0, behavior: "smooth" });
          }
        }
      } catch (error) {
        console.error("Lỗi khi tạo đơn hàng:", error);
        const errMsg = error.response?.data?.message || "Đặt hàng không thành công. Vui lòng thử lại.";
        this.showToast(errMsg, "error");
      } finally {
        this.isSubmittingOrder = false;
      }
    },
    confirmPaid() {
      this.showToast("Đặt hàng thành công!", "success");
      this.showQRModal = false;
      this.orderSuccess = true;
      window.scrollTo({ top: 0, behavior: "smooth" });
    },
    closeQRModal() {
      this.showQRModal = false;
      this.orderSuccess = true;
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }
};
</script>

<style scoped>
.checkout-page {
  background-color: #f7fafc;
  min-height: 100vh;
  padding: 12px 20px 40px 20px;
  font-family: 'Outfit', sans-serif;
  color: #1a202c;
}

.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
}

.checkout-title {
  font-size: 28px;
  font-weight: 800;
  margin-top: 0;
  margin-bottom: 16px;
  text-align: left;
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.checkout-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 100px 0;
  font-size: 16px;
  color: #718096;
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 30px;
  align-items: start;
}

.checkout-section-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.checkout-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #edf2f7;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
  text-align: left;
}

.card-header-with-action {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #edf2f7;
  padding-bottom: 8px;
  margin-bottom: 10px;
}

.section-card-title {
  font-size: 18px;
  font-weight: 800;
  color: #1a202c;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-card-title i {
  color: #3b82f6;
}

.btn-toggle-address-form {
  background: none;
  border: none;
  color: #2563eb;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.15s;
}

.btn-toggle-address-form:hover {
  background-color: #eff6ff;
}

/* Radio Address selection items */
.address-cards-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.address-card-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #fff;
}

.address-card-item:hover {
  border-color: #cbd5e1;
  background-color: #f8fafc;
}

.address-card-item.active {
  border-color: #3b82f6;
  background-color: #eff6ff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.05);
}

.addr-radio-wrapper {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.custom-radio {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #cbd5e1;
  display: inline-block;
  position: relative;
  transition: border-color 0.15s;
}

.custom-radio.checked {
  border-color: #3b82f6;
}

.custom-radio.checked::after {
  content: "";
  width: 10px;
  height: 10px;
  background-color: #3b82f6;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.addr-details {
  flex-grow: 1;
}

.addr-row-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.addr-phone {
  font-size: 14px;
  font-weight: 700;
  color: #2d3748;
}

.addr-badge-default {
  background-color: #ebf8ff;
  color: #2b6cb0;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 99px;
  border: 1px solid #bee3f8;
}

.addr-text {
  font-size: 13.5px;
  color: #718096;
  margin: 0;
  line-height: 1.5;
}

/* Address quick edit/create form */
.add-address-form {
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 20px;
  background-color: #fcfcfc;
}

.form-title {
  font-size: 15px;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 16px 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-group.full-width {
  grid-column: span 2;
}

.input-group label {
  font-size: 12px;
  font-weight: 700;
  color: #000000;
}

.required-asterisk {
  color: #dc2626;
  margin-left: 1px;
  font-size: 14px;
}

.input-group input {
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13.5px;
  font-weight: 600;
  outline: none;
  transition: border-color 0.15s;
}

.input-group input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.input-group select.address-select {
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13.5px;
  font-weight: 600;
  outline: none;
  background-color: #fff;
  transition: border-color 0.15s;
  cursor: pointer;
}

.input-group select.address-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.checkbox-group {
  margin-top: 6px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13.5px !important;
  color: #4a5568 !important;
}

.checkbox-label input {
  width: 16px;
  height: 16px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
  border-top: 1px dashed #edf2f7;
  padding-top: 16px;
}

.btn-form-cancel {
  background-color: #fff;
  border: 1px solid #cbd5e0;
  color: #4a5568;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.btn-form-save {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-form-save:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
}

/* Payment Methods List styling */
.payment-methods-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 16px;
}

.payment-method-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
  display: flex;
  gap: 16px;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s;
  background-color: #fff;
}

.payment-method-card:hover {
  border-color: #cbd5e1;
  background-color: #f8fafc;
}

.payment-method-card.active {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.pay-icon-wrapper {
  font-size: 24px;
  color: #4b5563;
  width: 44px;
  height: 44px;
  background-color: #f3f4f6;
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.payment-method-card.active .pay-icon-wrapper {
  background-color: #3b82f6;
  color: white;
}

.pay-info {
  flex-grow: 1;
  text-align: left;
}

.pay-info h4 {
  margin: 0 0 4px 0;
  font-size: 14.5px;
  font-weight: 700;
  color: #1a202c;
}

.pay-info p {
  margin: 0;
  font-size: 12px;
  color: #718096;
  line-height: 1.4;
}

.pay-checkbox {
  flex-shrink: 0;
}

.bank-details-instruction {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 16px;
  margin-top: 14px;
}

.bank-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 10px;
}

.bank-info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
  font-size: 13px;
  color: #4a5568;
}

.bank-note {
  font-size: 11.5px;
  color: #e53e3e;
  font-weight: 600;
  margin: 10px 0 0 0;
}

/* Notes Textarea */
.customer-notes-textarea {
  width: 100%;
  border: 1px solid #cbd5e0;
  border-radius: 10px;
  padding: 12px;
  font-size: 13.5px;
  font-weight: 600;
  outline: none;
  font-family: inherit;
  resize: vertical;
}

.customer-notes-textarea:focus {
  border-color: #3b82f6;
}

/* Order Summary Column CSS */
.checkout-summary-column {
  position: sticky;
  top: 100px;
}

.summary-card {
  border: 1px solid #edf2f7;
}

.summary-card-title {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 18px 0;
  border-bottom: 1px solid #edf2f7;
  padding-bottom: 12px;
}

.order-items-list-scroll {
  max-height: 280px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 20px;
  padding-right: 6px;
}

.order-items-list-scroll::-webkit-scrollbar {
  width: 5px;
}

.order-items-list-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 4px;
}

.order-item-mini {
  display: flex;
  gap: 12px;
}

.item-mini-img {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid #edf2f7;
  background-color: #f7fafc;
  flex-shrink: 0;
}

.item-mini-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.item-mini-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
  text-align: left;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.item-mini-variant {
  font-size: 11px;
  color: #718096;
  font-weight: 600;
  text-align: left;
}

.item-mini-price-row {
  display: flex;
  justify-content: space-between;
  font-size: 12.5px;
}

.item-mini-qty {
  color: #718096;
  font-weight: 500;
}

.item-mini-price {
  font-weight: 700;
  color: #2d3748;
}

.summary-price-breakdown {
  border-top: 1px dashed #e2e8f0;
  padding-top: 16px;
  margin-bottom: 20px;
}

.price-row {
  display: flex;
  justify-content: space-between;
  font-size: 13.5px;
  color: #4a5568;
  margin-bottom: 12px;
}

.val-bold {
  font-weight: 700;
  color: #2d3748;
}

.val-bold.discount {
  color: #e53e3e;
}

.price-row.total {
  margin-top: 14px;
  font-size: 15px;
  color: #1a202c;
}

.price-row.total span {
  font-weight: 800;
}

.val-total-payable {
  font-size: 19px;
  font-weight: 900;
  color: #dc2626;
}

.btn-place-order {
  width: 100%;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  padding: 14px 20px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
  transition: all 0.2s ease;
}

.btn-place-order:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(220, 38, 38, 0.25);
  background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
}

.btn-place-order:disabled {
  background: #cbd5e0;
  color: #a0aec0;
  cursor: not-allowed;
  box-shadow: none;
}

.address-warning-msg {
  color: #dd6b20;
  font-size: 12px;
  margin: 12px 0 0 0;
  font-weight: 600;
  line-height: 1.4;
}

/* Order Success Splash page styling */
.order-success-card {
  max-width: 580px;
  margin: 0px auto 60px auto;
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #edf2f7;
  padding: 40px;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.04);
  text-align: center;
  animation: modalScaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.success-icon-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 10px;
}

/* Animate checkmark SVG */
.checkmark {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: block;
  stroke-width: 2;
  stroke: #38a169;
  stroke-miterlimit: 10;
  box-shadow: inset 0px 0px 0px #38a169;
  animation: fillCheckmark .4s ease-in-out .4s forwards, scaleCheckmark .3s ease-in-out .9s forwards;
}

.checkmark__circle {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 2;
  stroke-miterlimit: 10;
  stroke: #38a169;
  fill: none;
  animation: strokeCircle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark__check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: strokeCheck 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;
}

@keyframes strokeCircle {
  100% { stroke-dashoffset: 0; }
}

@keyframes strokeCheck {
  100% { stroke-dashoffset: 0; }
}

@keyframes fillCheckmark {
  100% { box-shadow: inset 0px 0px 0px 40px #e6fffa; }
}

@keyframes scaleCheckmark {
  0%, 100% { transform: none; }
  50% { transform: scale3d(1.1, 1.1, 1); }
}

.success-headline {
  font-size: 24px;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 8px 0;
}

.success-subtext {
  font-size: 14px;
  color: #718096;
  margin: 0 0 10px 0;
}

.success-details-box {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 10px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 13.5px;
  color: #4a5568;
  margin-bottom: 12px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.order-code {
  color: #2b6cb0;
  font-weight: 700;
}

.status-badge {
  background-color: #fef3c7;
  color: #d97706;
  font-size: 12px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 99px;
}

.payable-red {
  color: #dc2626;
  font-size: 15px;
  font-weight: 800;
}

.bank-success-box {
  background-color: #fffaf0;
  border: 1px solid #feebc8;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
  text-align: left;
}

.bank-box-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #dd6b20;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.bank-box-details {
  margin-top: 8px;
  font-size: 12.5px;
  color: #4a5568;
  line-height: 1.5;
}

.success-actions {
  display: flex;
  gap: 12px;
}

.btn-success-history {
  flex: 1;
  background-color: #0f172a;
  color: white;
  border: none;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.15s;
}

.btn-success-history:hover {
  background-color: #1e293b;
}

.btn-success-home {
  flex: 1;
  background-color: #fff;
  border: 1px solid #cbd5e1;
  color: #334155;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-success-home:hover {
  background-color: #f8fafc;
  border-color: #94a3b8;
}

/* Animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Checkout Voucher & Coins Styles */
.summary-discount-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 12px 0;
}

.coin-usage-row {
  display: flex;
  align-items: center;
}

.coin-usage-row label {
  display: flex;
  align-items: center;
  gap: 8px;
}

.voucher-usage-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.voucher-input-group {
  display: flex;
  gap: 8px;
}

.voucher-checkout-input {
  flex-grow: 1;
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  outline: none;
  text-transform: uppercase;
}

.voucher-checkout-input:focus {
  border-color: #ef4444;
}

.btn-apply-voucher-checkout {
  background-color: #1f2937;
  color: white;
  border: none;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 700;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.15s;
}

.btn-apply-voucher-checkout:hover {
  background-color: #374151;
}

.btn-apply-voucher-checkout:disabled {
  background-color: #cbd5e0;
  cursor: not-allowed;
}

.voucher-applied-display {
  display: flex;
}

.voucher-applied-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #fee2e2;
  color: #991b1b;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12.5px;
  font-weight: 700;
  border: 1px dashed #fca5a5;
}

.btn-remove-voucher-tag {
  background: none;
  border: none;
  color: #991b1b;
  font-size: 16px;
  cursor: pointer;
  font-weight: 700;
  line-height: 1;
  padding: 0;
}

.voucher-msg-checkout {
  font-size: 12px;
  font-weight: 600;
  margin: 4px 0 0 0;
}

.voucher-msg-checkout.success {
  color: #16a34a;
}

.voucher-msg-checkout.error {
  color: #dc2626;
}

.summary-divider {
  border: 0;
  border-top: 1px dashed #e2e8f0;
  margin: 12px 0;
}

/* Responsive Queries */
@media (max-width: 991px) {
  .checkout-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  .checkout-summary-column {
    position: static;
  }
}

@media (max-width: 768px) {
  .checkout-page {
    padding: 20px 10px;
  }

  .checkout-title {
    font-size: 22px;
    margin-bottom: 20px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .input-group.full-width {
    grid-column: span 1;
  }

  .checkout-card {
    padding: 16px;
  }

  .success-actions {
    flex-direction: column;
  }
}

/* QR Code Modal Overlay */
.qr-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.qr-modal-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalScaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid #e2e8f0;
}

.qr-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #edf2f7;
  background: #f8fafc;
}

.qr-modal-title {
  font-size: 16px;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.qr-modal-title i {
  color: #4f46e5;
}

.qr-modal-close-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: #64748b;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.qr-modal-close-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.qr-modal-body {
  padding: 10px;
  overflow-y: auto;
  max-height: calc(100vh - 180px);
}

.qr-modal-subtitle {
  font-size: 13px;
  color: #475569;
  line-height: 1.5;
  margin: 0 0 8px 0;
  text-align: center;
}

.qr-modal-image-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #f8fafc;
  padding: 16px;
  border-radius: 16px;
  border: 1px dashed #cbd5e0;
  margin-bottom: 5px;
}

.qr-modal-img {
  width: 200px;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  margin-bottom: 0px;
}

.qr-modal-badge {
  font-size: 11px;
  font-weight: 700;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 4px;
}

.qr-modal-bank-info {
  background: #f8fafc;
  border-radius: 18px;
  padding: 14px 20px;
  border: 1px solid #edf2f7;
}

.qr-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  padding: 6px 0;
  border-bottom: 1px dashed #edf2f7;
}

.qr-info-row:last-child {
  border-bottom: none;
}

.qr-info-row .lbl {
  color: #64748b;
  font-weight: 500;
}

.qr-info-row .val {
  color: #0f172a;
  font-weight: 700;
  text-align: right;
}

.qr-info-row .val.highlight {
  color: #e11d48;
}

.qr-info-row .val.uppercase {
  text-transform: uppercase;
}

.qr-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #edf2f7;
  background: #f8fafc;
}

.btn-cancel {
  background: #ffffff;
  border: 1px solid #cbd5e0;
  color: #475569;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #0f172a;
}

.btn-paid {
  background: #10b981;
  border: 1px solid #10b981;
  color: #ffffff;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-paid:hover {
  background: #059669;
  border-color: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

/* Scoped styles for Voucher Wallet dropdown in Checkout */
.select-wallet-voucher {
  position: relative;
  margin-top: 8px;
  width: 100%;
}

.btn-select-voucher-wallet {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #16a34a;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-select-voucher-wallet:hover {
  background: #dcfce7;
  border-color: #86efac;
}

.voucher-wallet-dropdown-panel {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 100;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  margin-top: 4px;
  max-height: 240px;
  overflow-y: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.voucher-dropdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: background-color 0.15s;
}

.voucher-dropdown-item:hover:not(.disabled) {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.voucher-dropdown-item.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.vd-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.vd-code {
  font-size: 12.5px;
  font-weight: 700;
  color: #0f172a;
  text-align: left;
}

.vd-desc {
  font-size: 12px;
  color: #475569;
  text-align: left;
}

.vd-min-order {
  font-size: 11px;
  color: #94a3b8;
  text-align: left;
}

.btn-select-v {
  background: #e30019;
  color: #fff;
  border: none;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  transition: opacity 0.15s;
}

.btn-select-v:hover {
  background: #c20014;
}

.btn-select-v:disabled {
  background: #cbd5e1;
  color: #94a3b8;
  cursor: not-allowed;
}
</style>


