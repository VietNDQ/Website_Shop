<template>
  <div class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-h1">Quản lý đơn hàng</h1>
        <p class="page-sub">Theo dõi và xử lý toàn bộ quy trình đặt hàng</p>
      </div>
      <button class="btn-outline" @click="exportOrdersPDF">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Xuất PDF
      </button>
    </div>

    <!-- Status Tabs -->
    <div class="status-tabs">
      <button v-for="tab in tabs" :key="tab.key" class="status-tab" :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key">
        {{ tab.label }}
        <span class="tab-count" :class="tab.key === 'da_huy' ? 'danger' : tab.key === 'hoan_tien' ? 'refund' : ''">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <div class="card">
      <div class="table-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
          <input v-model="search" type="text" placeholder="Tìm theo mã đơn, tên khách..." />
        </div>
        <div class="toolbar-right">
          <select class="sel" v-model="filterPayment">
            <option value="">Thanh toán</option>
            <option value="Tiền mặt">Tiền mặt</option>
            <option value="Chuyển khoản">Chuyển khoản</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th style="color:black">Mã đơn</th>
            <th class="whitespace-nowrap" style="color:black">Khách hàng</th>
            <th style="color:black">Sản phẩm</th>
            <th class="whitespace-nowrap" style="color:black">Tổng tiền</th>
            <th class="whitespace-nowrap" style="color:black">Thanh toán</th>
            <th style="color:black">Ngày đặt</th>
            <th style="color:black">Trạng thái</th>
            <th class="whitespace-nowrap" style="color:black">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in filteredOrders" :key="o.id" class="order-row">
            <td>
              <span class="order-id-text">{{ o.code }}</span>
            </td>
            <td>
              <div class="customer-cell" style="display: block;">
                <p class="cus-name" style="font-weight: 500; margin-bottom: 2px;">{{ o.customer }}</p>
                <p class="cus-phone" style="font-size: 12px; color: #6b7280;" v-if="o.phone && o.phone !== 'N/A'">
                  {{ o.phone }}</p>
                <!-- Badge khách tự hủy -->
                <span v-if="o.nguoi_huy === 'khach'" class="badge-customer-cancel">Khách tự hủy</span>
              </div>
            </td>
            <td>{{ o.product }}</td>
            <td><span class="price-red">{{ o.total }}</span></td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 3px; align-items: center; text-align: center;">
                <span class="payment-badge whitespace-nowrap" style="display:block; width:100%; text-align:center;">{{ o.payment }}</span>
                <span class="payment-status-badge" :class="'ps-' + o.payment_status" style="display:block; width:100%; text-align:center;">
                  {{ o.payment_status_label }}
                </span>
              </div>
            </td>
            <td>{{ o.date }}</td>
            <td><span class="status-pill" :class="'s-' + o.status">{{ statusMap[o.status] }}</span></td>
            <td class="text-center align-middle">
              <div class="action-btns">
                <button class="act-btn view" @click="openDetail(o)" title="Chi tiết">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredOrders.length === 0">
            <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 10px; display: block; opacity: 0.4;">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Không có đơn hàng nào
            </td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-count">{{ filteredOrders.length }} đơn hàng</span>
        <div class="pagination">
          <button class="pg-btn">&lt;</button>
          <button class="pg-btn active">1</button>
          <button class="pg-btn">2</button>
          <button class="pg-btn">&gt;</button>
        </div>
      </div>
    </div>

    <!-- ============================== Order Detail Modal ============================== -->
    <div class="modal-overlay" v-if="showDetail" @click.self="showDetail = false">
      <div class="modal-box modal-lg" v-if="selectedOrder">
        <div class="modal-header">
          <div style="display: flex; align-items: center; gap: 10px;">
            <h2>Chi tiết đơn hàng {{ selectedOrder.code }}</h2>
            <span class="status-pill" :class="'s-' + selectedOrder.status" style="font-size: 12px !important; padding: 4px 10px !important; min-width: unset !important;">
              {{ statusMap[selectedOrder.status] }}
            </span>
          </div>
          <button class="modal-close" @click="showDetail = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="order-detail-grid">
            <!-- Thông tin khách hàng -->
            <div class="detail-section">
              <h3 class="detail-sec-title">Thông tin khách hàng</h3>
              <div class="detail-rows">
                <div class="detail-row" style="display: flex; justify-content: flex-start; gap: 4px;">
                  <span>Họ tên:</span><strong>{{ selectedOrder.customer }}</strong>
                </div>
                <div class="detail-row" style="display: flex; justify-content: flex-start; gap: 4px;">
                  <span>SĐT:</span><strong>{{ selectedOrder.phone }}</strong>
                </div>
                <div class="detail-row" style="display: flex; justify-content: flex-start; gap: 4px;">
                  <span>Địa chỉ:</span><strong>{{ selectedOrder.address }}</strong>
                </div>
                <div class="detail-row" style="display: flex; justify-content: flex-start; gap: 4px;">
                  <span>Thanh toán:</span>
                  <strong>{{ selectedOrder.payment }}
                    <span class="payment-status-badge" :class="'ps-' + selectedOrder.payment_status" style="margin-left: 6px;">
                      {{ selectedOrder.payment_status_label }}
                    </span>
                  </strong>
                </div>
              </div>
            </div>

            <!-- Quy trình & trạng thái -->
            <div class="detail-section">
              <h3 class="detail-sec-title">Quy trình đơn hàng</h3>

              <!-- Đơn đã hủy -->
              <div v-if="selectedOrder.status === 'da_huy'" class="canceled-banner">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>
                  <div style="font-weight: 700;">
                    Đơn hàng đã bị hủy
                    <span v-if="selectedOrder.nguoi_huy === 'khach'" class="badge-customer-cancel" style="margin-left: 6px;">Khách tự hủy</span>
                    <span v-else-if="selectedOrder.nguoi_huy === 'admin'" class="badge-admin-cancel" style="margin-left: 6px;">Admin hủy</span>
                  </div>
                  <div v-if="selectedOrder.ly_do_huy" style="font-size: 13px; margin-top: 4px; color: #991b1b;">
                    Lý do: {{ selectedOrder.ly_do_huy }}
                  </div>
                </div>
              </div>

              <!-- Đơn hoàn tiền -->
              <div v-else-if="selectedOrder.status === 'hoan_tien'" class="refund-banner">
                <i class="fa-solid fa-rotate-left"></i>
                <div>
                  <div style="font-weight: 700;">Đơn hàng đã được hoàn tiền</div>
                  <div v-if="selectedOrder.ly_do_huy" style="font-size: 13px; margin-top: 4px;">
                    Lý do: {{ selectedOrder.ly_do_huy }}
                  </div>
                </div>
              </div>

              <!-- Workflow bình thường -->
              <div v-else class="status-workflow">
                <div v-for="(s, i) in workflow" :key="s.key" class="wf-step"
                  :class="{ done: i < currentWfIdx, current: i === currentWfIdx }">
                  <div class="wf-dot"></div>
                  <span>{{ s.label }}</span>
                </div>
              </div>

              <div class="detail-row" style="margin-top: 16px; display: flex; justify-content: flex-start; gap: 4px;">
                <span>Tổng tiền:</span><strong style="color: #dc2626; font-size: 15px;">{{ selectedOrder.total }}</strong>
              </div>
              <div class="detail-row" style="display: flex; justify-content: flex-start; gap: 4px;">
                <span>Ngày đặt:</span><strong>{{ selectedOrder.date }}</strong>
              </div>
            </div>
          </div>

          <!-- Sản phẩm trong đơn -->
          <div class="detail-section" style="margin-top:16px">
            <h3 class="detail-sec-title">Sản phẩm trong đơn</h3>
            <table class="data-table" style="margin-top:10px">
              <thead>
                <tr style="background-color: #f3f4f6;">
                  <th style="color: #111827;">Sản phẩm</th>
                  <th style="color: #111827;">Đơn giá</th>
                  <th style="color: #111827;">SL</th>
                  <th style="color: #111827;" class="whitespace-nowrap">Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(sp, idx) in selectedOrder.chi_tiets" :key="idx">
                  <td>{{ sp.ten }}</td>
                  <td class="whitespace-nowrap" style="color:#dc2626">{{ sp.gia }}</td>
                  <td>{{ sp.sl }}</td>
                  <td class="whitespace-nowrap" style="color:#dc2626">{{ sp.thanh_tien }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Timeline lịch sử trạng thái -->
          <div class="detail-section" style="margin-top:16px" v-if="selectedOrder.lich_su && selectedOrder.lich_su.length > 0">
            <h3 class="detail-sec-title">Lịch sử trạng thái</h3>
            <div class="timeline">
              <div v-for="(ls, idx) in selectedOrder.lich_su" :key="idx" class="timeline-item"
                :class="{ 'tl-last': idx === selectedOrder.lich_su.length - 1 }">
                <div class="tl-dot" :class="'tl-dot-' + ls.trang_thai"></div>
                <div class="tl-content">
                  <div class="tl-status">
                    <span class="status-pill" :class="'s-' + ls.trang_thai" style="font-size: 11px !important; padding: 3px 9px !important; min-width: unset !important;">
                      {{ statusMap[ls.trang_thai] || ls.trang_thai }}
                    </span>
                    <span class="tl-time">{{ ls.thoi_gian }}</span>
                  </div>
                  <div class="tl-note" v-if="ls.ghi_chu">{{ ls.ghi_chu }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer Buttons -->
        <div class="modal-footer">
          <button class="btn-outline" @click="exportInvoicePDF">In hóa đơn</button>

          <!-- Xác nhận đơn -->
          <button class="btn-primary" style="background-color:#3b82f6; border-color:#3b82f6;"
            v-if="selectedOrder.status === 'cho_xu_ly'"
            @click="updateOrderStatus('dang_chuan_bi')">
            ✓ Xác nhận đơn
          </button>

          <!-- Bắt đầu giao -->
          <button class="btn-primary" style="background-color:#f59e0b; border-color:#f59e0b;"
            v-if="selectedOrder.status === 'dang_chuan_bi'"
            @click="updateOrderStatus('dang_giao')">
            🚚 Giao hàng
          </button>

          <!-- Đã giao thành công -->
          <button class="btn-primary" style="background-color:#10b981; border-color:#10b981;"
            v-if="selectedOrder.status === 'dang_giao'"
            @click="updateOrderStatus('da_giao')">
            ✓ Hoàn tất giao
          </button>

          <!-- Xác nhận hoàn tiền (chỉ sau khi đã giao) -->
          <button class="btn-primary" style="background-color:#8b5cf6; border-color:#8b5cf6;"
            v-if="selectedOrder.status === 'da_giao'"
            @click="updateOrderStatus('hoan_tien')">
            ↩ Xác nhận hoàn tiền
          </button>

          <!-- Hủy đơn (chỉ cho phép từ trạng thái chưa giao) -->
          <button class="btn-outline" style="color:#ef4444; border-color:#ef4444; background-color:#fef2f2;"
            v-if="['cho_xu_ly', 'dang_chuan_bi', 'dang_giao'].includes(selectedOrder.status)"
            @click="openCancelDialog">
            ✕ Huỷ đơn
          </button>

          <button class="btn-ghost" @click="showDetail = false">Đóng</button>
        </div>
      </div>
    </div>

    <!-- ============================== Confirm Modal (Chuyển trạng thái thường) ============================== -->
    <div class="modal-overlay" v-if="showConfirmModal" @click.self="cancelConfirm">
      <div class="modal-box" style="max-width:420px; text-align:center; border-radius:16px; padding:32px 24px;">
        <div style="width:54px; height:54px; border-radius:50%; background:#eff6ff; color:#3b82f6; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" /><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" /><line x1="12" y1="17" x2="12.01" y2="17" />
          </svg>
        </div>
        <h3 style="margin-bottom:12px; font-size:20px; font-weight:700; color:#111827;">Xác nhận thay đổi</h3>
        <p style="color:#4b5563; line-height:1.5; font-size:15px; margin-bottom:16px;">
          Bạn có chắc chắn muốn chuyển trạng thái đơn hàng này thành:
        </p>
        <div style="margin-bottom:28px;">
          <span class="status-pill" :class="'s-' + pendingStatus"
            style="font-size:15px !important; padding:8px 24px !important; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
            {{ statusMap[pendingStatus] }}
          </span>
        </div>
        <div style="display:flex; gap:12px; justify-content:center;">
          <button class="btn-ghost" style="padding:10px 24px; border-radius:8px;" @click="cancelConfirm">Huỷ bỏ</button>
          <button class="btn-primary"
            style="background-color:#3b82f6; border-color:#3b82f6; padding:10px 24px; border-radius:8px; font-weight:600;"
            @click="executeStatusUpdate">Đồng ý</button>
        </div>
      </div>
    </div>

    <!-- ============================== Cancel Dialog (Nhập lý do hủy) ============================== -->
    <div class="modal-overlay" v-if="showCancelDialog" @click.self="showCancelDialog = false">
      <div class="modal-box" style="max-width:460px; border-radius:16px; padding:32px 28px;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
          <div style="width:44px; height:44px; border-radius:50%; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div>
            <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Hủy đơn hàng</h3>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ selectedOrder?.code }}</p>
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
            Lý do hủy đơn <span style="color:#ef4444;">*</span>
          </label>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <label v-for="r in cancelReasons" :key="r" class="radio-option"
              :class="{ selected: cancelReason === r }" @click="cancelReason = r">
              <input type="radio" :value="r" v-model="cancelReason" style="display:none;" />
              <span class="radio-dot"></span>
              <span>{{ r }}</span>
            </label>
            <label class="radio-option" :class="{ selected: cancelReason === '__custom__' }" @click="cancelReason = '__custom__'">
              <span class="radio-dot"></span>
              <span>Lý do khác...</span>
            </label>
          </div>
          <textarea v-if="cancelReason === '__custom__'"
            v-model="customCancelReason"
            placeholder="Nhập lý do hủy đơn..."
            style="margin-top:10px; width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; resize:vertical; min-height:80px; outline:none; font-family:inherit;"
            @focus="$event.target.style.borderColor='#3b82f6'"
            @blur="$event.target.style.borderColor='#d1d5db'">
          </textarea>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
          <button class="btn-ghost" @click="showCancelDialog = false">Hủy bỏ</button>
          <button class="btn-primary"
            style="background-color:#ef4444; border-color:#ef4444;"
            :disabled="!getFinalCancelReason()"
            :style="{ opacity: !getFinalCancelReason() ? 0.5 : 1 }"
            @click="confirmCancelOrder">
            Xác nhận hủy đơn
          </button>
        </div>
      </div>
    </div>

    <!-- ============================== Hoàn tiền Dialog ============================== -->
    <div class="modal-overlay" v-if="showRefundDialog" @click.self="showRefundDialog = false">
      <div class="modal-box" style="max-width:460px; border-radius:16px; padding:32px 28px;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
          <div style="width:44px; height:44px; border-radius:50%; background:#f5f3ff; color:#8b5cf6; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.1"/>
            </svg>
          </div>
          <div>
            <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Xác nhận hoàn tiền</h3>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ selectedOrder?.code }} – {{ selectedOrder?.total }}</p>
          </div>
        </div>

        <div style="background:#f5f3ff; border:1px solid #ddd6fe; border-radius:8px; padding:14px 16px; margin-bottom:20px; font-size:13px; color:#5b21b6; line-height:1.6;">
          ⚠️ Xác nhận hoàn tiền sẽ: <br/>
          • Cập nhật trạng thái đơn thành <strong>Hoàn tiền</strong><br/>
          • Hoàn lại số lượng tồn kho của các sản phẩm<br/>
          • Cập nhật trạng thái thanh toán thành <strong>Đã hoàn tiền</strong>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
            Lý do hoàn tiền (tùy chọn)
          </label>
          <textarea v-model="refundReason"
            placeholder="VD: Khách yêu cầu hoàn tiền, hàng bị lỗi..."
            style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; resize:vertical; min-height:70px; outline:none; font-family:inherit;"
            @focus="$event.target.style.borderColor='#8b5cf6'"
            @blur="$event.target.style.borderColor='#d1d5db'">
          </textarea>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
          <button class="btn-ghost" @click="showRefundDialog = false">Hủy bỏ</button>
          <button class="btn-primary"
            style="background-color:#8b5cf6; border-color:#8b5cf6;"
            @click="confirmRefundOrder">
            ↩ Xác nhận hoàn tiền
          </button>
        </div>
      </div>
    </div>

    <!-- Hidden Print Templates for PDF Export -->
    <!-- 1. Orders Report PDF Template -->
    <div ref="pdfReport" style="position:absolute; left:-9999px; top:-9999px; width:800px; background:white; padding:40px; font-family:'Segoe UI',Arial,sans-serif; color:#1e293b; box-sizing:border-box;">
      <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e2e8f0; padding-bottom:20px; margin-bottom:30px;">
        <div>
          <h1 style="font-size:26px; font-weight:800; color:#0f172a; margin:0; text-transform:uppercase;">Mô Hình BALAB</h1>
          <p style="font-size:13px; color:#64748b; margin:4px 0 0;">Hệ thống quản lý đơn hàng chuyên nghiệp</p>
        </div>
        <div style="text-align:right;">
          <h2 style="font-size:18px; font-weight:700; color:#3b82f6; margin:0; text-transform:uppercase;">Báo Cáo Đơn Hàng</h2>
          <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Ngày xuất: {{ getFormattedCurrentDate() }}</p>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:30px;">
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; text-align:center;">
          <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Tổng số đơn hàng</span>
          <p style="font-size:24px; font-weight:800; color:#0f172a; margin:6px 0 0;">{{ filteredOrders.length }}</p>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; text-align:center;">
          <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Trạng thái xuất</span>
          <p style="font-size:16px; font-weight:800; color:#3b82f6; margin:12px 0 0;">{{ statusMap[activeTab] || 'Tất cả trạng thái' }}</p>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; text-align:center;">
          <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Tổng tiền bộ lọc</span>
          <p style="font-size:20px; font-weight:800; color:#ef4444; margin:8px 0 0;">{{ getFilteredTotalSum() }}</p>
        </div>
      </div>
      <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12px;">
        <thead>
          <tr style="background:#f1f5f9; border-bottom:2px solid #cbd5e1;">
            <th style="padding:12px 8px; font-weight:800; color:#1e293b; width:15%;">Mã đơn</th>
            <th style="padding:12px 8px; font-weight:800; color:#1e293b; width:25%;">Khách hàng</th>
            <th style="padding:12px 8px; font-weight:800; color:#1e293b; width:30%;">Sản phẩm</th>
            <th style="padding:12px 8px; font-weight:800; color:#1e293b; width:15%; text-align:right;">Tổng tiền</th>
            <th style="padding:12px 8px; font-weight:800; color:#1e293b; width:15%; text-align:center;">Ngày đặt</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in filteredOrders" :key="o.id" style="border-bottom:1px solid #e2e8f0;">
            <td style="padding:12px 8px; font-weight:700; color:#0f172a;">{{ o.code }}</td>
            <td style="padding:12px 8px;"><div style="font-weight:700; color:#334155;">{{ o.customer }}</div><div style="font-size:10px; color:#64748b;">SĐT: {{ o.phone }}</div></td>
            <td style="padding:12px 8px; color:#475569; max-width:240px; overflow:hidden; text-overflow:ellipsis;">{{ o.product }}</td>
            <td style="padding:12px 8px; text-align:right; font-weight:800; color:#ef4444;">{{ o.total }}</td>
            <td style="padding:12px 8px; text-align:center; color:#475569;">{{ o.date }}</td>
          </tr>
        </tbody>
      </table>
      <div style="margin-top:50px; text-align:center; border-top:1px solid #e2e8f0; padding-top:20px; font-size:11px; color:#94a3b8;">
        Tài liệu lưu hành nội bộ - Bản quyền © Mô Hình BALAB 2026
      </div>
    </div>

    <!-- 2. Single Invoice PDF Template -->
    <div ref="pdfInvoice" v-if="selectedOrder" style="position:absolute; left:-9999px; top:-9999px; width:420px; background:white; padding:12px; font-family:'Segoe UI',Arial,sans-serif; color:black; box-sizing:border-box; border:2px solid black;">
      <div style="display:flex; flex-direction:column; border-bottom:1px solid black; padding-bottom:6px; margin-bottom:6px; gap:4px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div style="font-size:14px; font-weight:800; text-transform:uppercase;">MÔ HÌNH BALAB</div>
          <div style="display:flex; flex-direction:column; align-items:flex-end;">
            <img :src="'https://bwipjs-api.metafloor.com/?bcid=code128&text=' + encodeURIComponent('SPX-' + selectedOrder.code.replace('#','')) + '&scale=1&height=10&includetext=false'" style="width:120px; height:26px;" alt="Barcode" />
            <span style="font-size:7px; font-weight:700; margin-top:1px;">SPX-{{ selectedOrder.code.replace('#','') }}</span>
          </div>
        </div>
        <div style="font-size:9px; display:flex; justify-content:space-between;">
          <span>Đơn vị vận chuyển: <strong>GHN / Viettel</strong></span>
          <span>Mã vận đơn: <strong>SPX-{{ selectedOrder.code.replace('#','') }}</strong></span>
        </div>
        <div style="text-align:center; font-size:11px; font-weight:800; margin-top:2px;">Mã đơn hàng: {{ selectedOrder.code.replace('#','') }}</div>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid black; padding-bottom:6px; margin-bottom:6px; font-size:9px; line-height:1.35;">
        <div style="border-right:1px solid black; padding-right:6px;">
          <div style="font-weight:800; text-transform:uppercase; margin-bottom:3px;">Từ: Kho Tổng BALAB</div>
          <div>12 Nguyễn Văn Linh, Q. Hải Châu, TP. Đà Nẵng</div>
          <div style="margin-top:2px; font-weight:700;">SĐT: 0900.123.456</div>
        </div>
        <div style="padding-left:6px;">
          <div style="font-weight:800; text-transform:uppercase; margin-bottom:3px;">Đến:</div>
          <div style="font-weight:700; font-size:9.5px; margin-bottom:2px;">{{ selectedOrder.customer }}</div>
          <div style="word-wrap:break-word; min-height:26px;">{{ selectedOrder.address }}</div>
          <div style="margin-top:2px; font-weight:700;">SĐT: {{ selectedOrder.phone }}</div>
        </div>
      </div>
      <div style="border-bottom:1px solid black; padding:8px 0; margin-bottom:6px; text-align:center;">
        <div style="font-size:20px; font-weight:850;">ĐN-TK-01 (MÃ TRẠM GIAO)</div>
      </div>
      <div style="display:grid; grid-template-columns:7.2fr 2.8fr; border-bottom:1px solid black; padding-bottom:6px; margin-bottom:6px; gap:6px;">
        <div style="font-size:9px; line-height:1.35;">
          <div style="font-weight:800; margin-bottom:4px;">Nội dung hàng (Tổng SL: {{ getOrderTotalQty(selectedOrder) }})</div>
          <div v-for="(sp, idx) in selectedOrder.chi_tiets" :key="idx" style="margin-bottom:2px;">{{ idx+1 }}. {{ sp.ten }} - SL: {{ sp.sl }}</div>
          <div style="margin-top:6px; font-size:8px; font-weight:700; color:#4b5563;">Ngày đặt: {{ selectedOrder.date }}</div>
        </div>
        <div style="text-align:center; border-left:1px dashed black; padding-left:6px;">
          <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=' + encodeURIComponent(selectedOrder.code)" style="width:60px; height:60px; border:1px solid black; padding:2px;" alt="QR" />
          <div style="font-size:7px; color:#4b5563; margin-top:2px;">(Quét để xem đơn)</div>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1.2fr 1fr; border-bottom:1px solid black; padding-bottom:6px; margin-bottom:6px; font-size:9px; min-height:60px;">
        <div style="border-right:1px solid black; padding-right:6px;">
          <div>Tiền thu Người nhận:</div>
          <div style="font-size:16px; font-weight:900; margin-bottom:2px;">
            {{ selectedOrder.payment_status === 'da_thanh_toan' ? '0 VNĐ (Đã thanh toán)' : selectedOrder.total }}
          </div>
        </div>
        <div style="padding-left:6px;">
          <div style="border-top:1px dashed black; padding-top:3px; text-align:center; margin-top:auto;">
            <div style="font-weight:700; font-size:8.5px;">Chữ ký người nhận</div>
          </div>
        </div>
      </div>
      <div style="font-size:8px; line-height:1.35;">
        <div><strong>Chỉ dẫn giao hàng:</strong> Không đồng kiểm. Lưu kho tối đa 5 ngày.</div>
        <div style="margin-top:1px; font-style:italic;"><strong>Lưu ý:</strong> Quý khách vui lòng quay video khi bóc hộp để được hỗ trợ bảo hành.</div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

export default {
  name: 'AdminOrders',
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      search: '',
      filterPayment: '',
      activeTab: 'cho_xu_ly',
      showDetail: false,
      selectedOrder: null,

      // Confirm modal (chuyển trạng thái thường)
      showConfirmModal: false,
      pendingStatus: null,

      // Cancel dialog (nhập lý do hủy)
      showCancelDialog: false,
      cancelReason: '',
      customCancelReason: '',
      cancelReasons: [
        'Khách hàng đổi ý, không muốn mua',
        'Đặt nhầm sản phẩm / biến thể',
        'Hàng hết / không đủ hàng',
        'Thông tin giao hàng không hợp lệ',
        'Yêu cầu từ khách hàng',
      ],

      // Refund dialog
      showRefundDialog: false,
      refundReason: '',

      orders: [],

      statusMap: {
        cho_xu_ly:      'Chờ xác nhận',
        dang_chuan_bi:  'Đang chuẩn bị',
        dang_giao:      'Đang giao',
        da_giao:        'Đã giao',
        da_huy:         'Đã huỷ',
        hoan_tien:      'Hoàn tiền',
      },
      workflow: [
        { key: 'cho_xu_ly',     label: 'Chờ xác nhận' },
        { key: 'dang_chuan_bi', label: 'Đang chuẩn bị' },
        { key: 'dang_giao',     label: 'Đang giao hàng' },
        { key: 'da_giao',       label: 'Đã giao' },
      ],
    };
  },
  computed: {
    tabs() {
      return [
        { key: 'cho_xu_ly',   label: 'Chờ xác nhận', count: this.orders.filter(o => o.status === 'cho_xu_ly').length },
        { key: 'dang_giao',   label: 'Đang giao',     count: this.orders.filter(o => ['dang_chuan_bi', 'dang_giao'].includes(o.status)).length },
        { key: 'da_giao',     label: 'Đã giao',       count: this.orders.filter(o => o.status === 'da_giao').length },
        { key: 'da_huy',      label: 'Đã huỷ',        count: this.orders.filter(o => o.status === 'da_huy').length },
        { key: 'hoan_tien',   label: 'Hoàn tiền',     count: this.orders.filter(o => o.status === 'hoan_tien').length },
        { key: 'all',         label: 'Tất cả',         count: this.orders.length },
      ];
    },
    filteredOrders() {
      return this.orders.filter(o => {
        const ms = !this.search
          || o.code.toLowerCase().includes(this.search.toLowerCase())
          || o.customer.toLowerCase().includes(this.search.toLowerCase());

        let mt = false;
        if (this.activeTab === 'all') {
          mt = true;
        } else if (this.activeTab === 'dang_giao') {
          mt = o.status === 'dang_chuan_bi' || o.status === 'dang_giao';
        } else {
          mt = o.status === this.activeTab;
        }

        let mp = true;
        if (this.filterPayment) {
          const pu = (o.payment || '').toUpperCase();
          if (this.filterPayment === 'Tiền mặt') {
            mp = pu === 'TIỀN MẶT' || pu === 'COD' || pu === 'TIEN_MAT';
          } else if (this.filterPayment === 'Chuyển khoản') {
            mp = pu === 'CHUYỂN KHOẢN' || pu === 'CHUYEN_KHOAN' || pu === 'VNPAY' || pu === 'MOMO';
          } else {
            mp = o.payment === this.filterPayment;
          }
        }

        return ms && mt && mp;
      });
    },
    currentWfIdx() {
      const wfMap = { cho_xu_ly: 0, dang_chuan_bi: 1, dang_giao: 2, da_giao: 3 };
      return wfMap[this.selectedOrder?.status] ?? 0;
    },
  },
  mounted() {
    this.fetchOrders();
    this.initPusher();
  },
  beforeUnmount() {
    if (this.pusher) {
      try { this.pusher.unsubscribe('my-channel'); } catch (err) { console.error(err); }
    }
  },
  methods: {
    // ===================== Pusher =====================
    initPusher() {
      if (window.Pusher) {
        this.setupPusher();
      } else {
        const script = document.createElement('script');
        script.src = 'https://js.pusher.com/8.4.0/pusher.min.js';
        script.onload = () => this.setupPusher();
        document.head.appendChild(script);
      }
    },
    setupPusher() {
      try {
        this.pusher = new window.Pusher('794a0b225fca675fc9a7', { cluster: 'ap1' });
        const channel = this.pusher.subscribe('my-channel');
        channel.bind('new-order', (data) => {
          this.playNotificationSound();
          this.toast.success(`Có đơn hàng mới: #${data.ma_don_hang} (${data.customer} - ${data.total})!`);
          this.fetchOrders();
        });
      } catch (err) {
        console.error('Pusher error:', err);
      }
    },
    playNotificationSound() {
      try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [{ freq: 587.33, delay: 0, dur: 0.4 }, { freq: 880, delay: 120, dur: 0.6 }].forEach(({ freq, delay, dur }) => {
          setTimeout(() => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + dur);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + dur);
          }, delay);
        });
      } catch (e) { /* silent */ }
    },

    // ===================== API =====================
    async fetchOrders() {
      try {
        const res = await axios.get('/api/quan-ly/don-hang', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token_admin')}` }
        });
        if (res.data.status === 1) this.orders = res.data.data;
      } catch (err) {
        console.error(err);
        this.toast.error('Không thể tải danh sách đơn hàng');
      }
    },

    // ===================== Detail Modal =====================
    openDetail(order) {
      this.selectedOrder = { ...order };
      this.showDetail = true;
    },

    // ===================== Chuyển trạng thái thường =====================
    updateOrderStatus(newStatus) {
      if (newStatus === 'da_huy') {
        // Mở dialog nhập lý do hủy thay vì confirm thông thường
        this.openCancelDialog();
        return;
      }
      if (newStatus === 'hoan_tien') {
        this.openRefundDialog();
        return;
      }
      this.pendingStatus = newStatus;
      this.showConfirmModal = true;
    },
    cancelConfirm() {
      this.showConfirmModal = false;
      this.pendingStatus = null;
    },
    async executeStatusUpdate() {
      if (!this.pendingStatus) return;
      const newStatus = this.pendingStatus;
      this.showConfirmModal = false;
      this.pendingStatus = null;

      await this.doUpdateStatus(newStatus, '');
    },

    // ===================== Cancel Dialog =====================
    openCancelDialog() {
      this.cancelReason = '';
      this.customCancelReason = '';
      this.showCancelDialog = true;
    },
    getFinalCancelReason() {
      if (!this.cancelReason) return '';
      if (this.cancelReason === '__custom__') return this.customCancelReason.trim();
      return this.cancelReason;
    },
    async confirmCancelOrder() {
      const reason = this.getFinalCancelReason();
      if (!reason) {
        this.toast.warning('Vui lòng chọn hoặc nhập lý do hủy đơn.');
        return;
      }
      this.showCancelDialog = false;
      await this.doUpdateStatus('da_huy', reason);
    },

    // ===================== Refund Dialog =====================
    openRefundDialog() {
      this.refundReason = '';
      this.showRefundDialog = true;
    },
    async confirmRefundOrder() {
      this.showRefundDialog = false;
      await this.doUpdateStatus('hoan_tien', this.refundReason.trim());
    },

    // ===================== Core status update =====================
    async doUpdateStatus(newStatus, lyDo) {
      try {
        const payload = { trang_thai: newStatus };
        if (lyDo) payload.ly_do = lyDo;

        const res = await axios.patch(
          `/api/quan-ly/don-hang/${this.selectedOrder.id}/trang-thai`,
          payload,
          { headers: { Authorization: `Bearer ${localStorage.getItem('token_admin')}` } }
        );

        if (res.data.status === 1) {
          this.toast.success(res.data.message);
          // Cập nhật local state
          this.selectedOrder.status = newStatus;
          if (lyDo && (newStatus === 'da_huy' || newStatus === 'hoan_tien')) {
            this.selectedOrder.ly_do_huy = lyDo;
            this.selectedOrder.nguoi_huy = 'admin';
          }
          // Refresh danh sách
          await this.fetchOrders();
          window.dispatchEvent(new CustomEvent('orderStatusUpdated'));
          if (newStatus === 'da_huy' || newStatus === 'hoan_tien') {
            this.showDetail = false;
          }
        } else {
          this.toast.error(res.data.message);
        }
      } catch (err) {
        console.error(err);
        this.toast.error(err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật đơn hàng');
      }
    },

    // ===================== Utilities =====================
    getFilteredTotalSum() {
      const total = this.filteredOrders.reduce((s, o) => s + (o.raw_total || 0), 0);
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0 }).format(total);
    },
    getFormattedCurrentDate() {
      const n = new Date();
      return `${String(n.getDate()).padStart(2,'0')}/${String(n.getMonth()+1).padStart(2,'0')}/${n.getFullYear()} ${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}`;
    },
    getOrderTotalQty(order) {
      if (!order?.chi_tiets) return 0;
      return order.chi_tiets.reduce((s, i) => s + (i.sl || 0), 0);
    },

    // ===================== PDF Export =====================
    async exportOrdersPDF() {
      if (!this.filteredOrders.length) { this.toast.warning('Không có đơn hàng nào để xuất PDF!'); return; }
      const tid = this.toast.info('Đang khởi tạo PDF...', { timeout: false });
      try {
        await this.$nextTick();
        const el = this.$refs.pdfReport;
        const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#fff', logging: false });
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: [canvas.width/2, canvas.height/2] });
        pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, canvas.width/2, canvas.height/2);
        pdf.save(`Bao_cao_don_hang_${Date.now()}.pdf`);
        this.toast.dismiss(tid);
        this.toast.success('Xuất báo cáo PDF thành công!');
      } catch (e) {
        this.toast.dismiss(tid);
        this.toast.error('Có lỗi xảy ra khi xuất PDF!');
      }
    },
    async exportInvoicePDF() {
      if (!this.selectedOrder) return;
      const tid = this.toast.info('Đang xuất hóa đơn...', { timeout: false });
      try {
        await this.$nextTick();
        const el = this.$refs.pdfInvoice;
        const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#fff', logging: false });
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: [canvas.width/2, canvas.height/2] });
        pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, canvas.width/2, canvas.height/2);
        pdf.save(`Hoa_don_${this.selectedOrder.code.replace('#','')}.pdf`);
        this.toast.dismiss(tid);
        this.toast.success('Xuất hóa đơn PDF thành công!');
      } catch (e) {
        this.toast.dismiss(tid);
        this.toast.error('Có lỗi xảy ra khi xuất hóa đơn PDF!');
      }
    },
  },
};
</script>

<style scoped>
@import "../../../public/style_admin/orders.css";

/* ========== Table row red divider ========== */
.order-row td {
  border-bottom: 0.3px solid #767F9E !important;
}


/* ========== Status Pills ========== */
.status-pill {
  white-space: nowrap !important;
  padding: 6px 12px !important;
  border-radius: 20px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  display: inline-block !important;
  min-width: 120px !important;
  text-align: center !important;
  vertical-align: middle !important;
}
.status-pill.s-cho_xu_ly    { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid #fde68a !important; }
.status-pill.s-dang_chuan_bi{ background-color: #eff6ff !important; color: #2563eb !important; border: 1px solid #bfdbfe !important; }
.status-pill.s-dang_giao    { background-color: #fefce8 !important; color: #ca8a04 !important; border: 1px solid #fef08a !important; }
.status-pill.s-da_giao      { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid #a7f3d0 !important; }
.status-pill.s-da_huy       { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
.status-pill.s-hoan_tien    { background-color: #f5f3ff !important; color: #7c3aed !important; border: 1px solid #ddd6fe !important; }

/* ========== Tab count badges ========== */
.tab-count.danger { background: #fef2f2 !important; color: #dc2626 !important; }
.tab-count.refund { background: #f5f3ff !important; color: #7c3aed !important; }

/* ========== Badge: Khách tự hủy ========== */
.badge-customer-cancel {
  display: inline-block;
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 600;
  padding: 1px 7px;
}
.badge-admin-cancel {
  display: inline-block;
  background: #fff7ed;
  color: #ea580c;
  border: 1px solid #fed7aa;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 600;
  padding: 1px 7px;
}

/* ========== Payment Status Badge ========== */
.payment-status-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
  white-space: nowrap;
}
.ps-da_thanh_toan  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.ps-hoan_tien      { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.ps-that_bai       { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.ps-cho_thanh_toan { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.ps-chua_thanh_toan{ background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.ps-cho_xu_ly      { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

/* ========== Canceled & Refund Banners ========== */
.canceled-banner {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 600;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 8px;
}
.refund-banner {
  background: #f5f3ff;
  border: 1px solid #ddd6fe;
  color: #7c3aed;
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 600;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 7px;
}

/* ========== Timeline Lịch sử ========== */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin-top: 10px;
  padding-left: 4px;
}
.timeline-item {
  display: flex;
  gap: 14px;
  position: relative;
  padding-bottom: 10px;
}
.timeline-item:not(.tl-last)::before {
  content: '';
  position: absolute;
  left: 9px;
  top: 18px;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}
.tl-dot {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 2px;
  border: 2px solid #d1d5db;
  background: white;
  z-index: 1;
}
.tl-dot-cho_xu_ly     { border-color: #d97706; background: #fffbeb; }
.tl-dot-dang_chuan_bi { border-color: #2563eb; background: #eff6ff; }
.tl-dot-dang_giao     { border-color: #ca8a04; background: #fefce8; }
.tl-dot-da_giao       { border-color: #059669; background: #ecfdf5; }
.tl-dot-da_huy        { border-color: #dc2626; background: #fef2f2; }
.tl-dot-hoan_tien     { border-color: #7c3aed; background: #f5f3ff; }
.tl-content { flex: 1; }
.tl-status {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.tl-time {
  font-size: 12px;
  color: #9ca3af;
}
.tl-note {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
  font-style: italic;
}

/* ========== Radio Options (Cancel Dialog) ========== */
.radio-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 14px;
  color: #374151;
  user-select: none;
}
.radio-option:hover { border-color: #ef4444; background: #fef2f2; }
.radio-option.selected { border-color: #ef4444; background: #fef2f2; color: #dc2626; font-weight: 600; }
.radio-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #d1d5db;
  flex-shrink: 0;
  transition: all 0.15s;
}
.radio-option.selected .radio-dot {
  border-color: #ef4444;
  background: #ef4444;
  box-shadow: inset 0 0 0 3px white;
}
</style>
