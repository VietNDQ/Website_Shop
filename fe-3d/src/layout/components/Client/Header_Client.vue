<template>
  <div class="topbar">
    <div class="topbar-inner">
      
      <div class="marquee-wrapper">
        <div class="topbar-promos marquee-content">
           <div class="topbar-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
              <path d="M12 16a4 4 0 100-8 4 4 0 000 8z" />
              <line x1="4.93" y1="4.93" x2="9.17" y2="9.17" />
              <line x1="14.83" y1="14.83" x2="19.07" y2="19.07" />
            </svg>
            <strong>Nguyen Quoc Viet</strong>
          </div>
          
          <div class="topbar-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" />
              <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
              <line x1="12" y1="22.08" x2="12" y2="12" />
            </svg>
            <strong>Nhận in 3D theo yêu cầu</strong> · Gửi file là có mô hình
          </div>
          <div class="topbar-dot"></div>
          
          <div class="topbar-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
              <path d="M12 16a4 4 0 100-8 4 4 0 000 8z" />
              <line x1="4.93" y1="4.93" x2="9.17" y2="9.17" />
              <line x1="14.83" y1="14.83" x2="19.07" y2="19.07" />
            </svg>
            <strong>Công nghệ in Resin & PLA mới nhất</strong> · Độ chi tiết và sắc nét cao
          </div>
          <div class="topbar-dot"></div>
          
          <div class="topbar-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 5h2M12 9v4m-7.18 3.82l1.42-1.42M19.6 19.6l-1.42-1.42M3 12h2m14 0h2M5.76 5.76l1.42 1.42M18.24 5.76l-1.42 1.42" />
            </svg>
            Tối ưu chi phí theo kích thước · Báo giá nhanh qua Zalo
          </div>
          <div class="topbar-dot"></div>
          
          <div class="topbar-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
            <strong>Hỗ trợ bảo hành gãy vỡ 1-đổi-1</strong> do vận chuyển
          </div>
        </div>
      </div>

      <div class="topbar-utils">
        <a href="#" class="topbar-util">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
          </svg>
          Tra cứu đơn hàng
        </a>
        <a href="#" class="topbar-util font-bold text-yellow-300">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.7A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
          </svg>
          Hotline/Zalo: 1800 2097
        </a>
      </div>
      
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.topbar {
  background-color: #d70018; 
  color: #ffffff;
  font-size: 13px;
  padding: 7px 0;
}

.topbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 15px;
}

/* --- Khung chứa chữ chạy --- */
.marquee-wrapper {
  flex: 1;
  overflow: hidden;
  position: relative;
  margin-right: 30px;
  white-space: nowrap;
}

/* --- Animation chữ chạy mượt mà --- */
.marquee-content {
  display: flex;
  align-items: center;
  gap: 30px;
  animation: scrollText 22s linear infinite; 
}

.marquee-wrapper:hover .marquee-content {
  animation-play-state: paused;
}

@keyframes scrollText {
  0% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(-100%);
  }
}

.topbar-item, .topbar-util {
  display: flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  color: #ffffff;
}

.topbar-utils {
  display: flex;
  gap: 20px;
  flex-shrink: 0; 
  background-color: #d70018; 
  z-index: 10;
  padding-left: 10px;
}

.topbar-util:hover {
  opacity: 0.85;
}

.topbar-dot {
  width: 4px;
  height: 4px;
  background-color: rgba(255, 255, 255, 0.4);
  border-radius: 50%;
  flex-shrink: 0;
}
</style>