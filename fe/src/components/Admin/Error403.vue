<template>
  <div class="error-403-container">
    <!-- Animated background elements -->
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="glass-error-card">
      <div class="error-icon-wrapper">
        <svg
          width="48"
          height="48"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 9.9-1" />
        </svg>
      </div>
      
      <h1 class="error-title">403</h1>
      <h2 class="error-subtitle">Access Denied</h2>
      <p class="error-message">Your privileges are insufficient!</p>
      <p class="error-desc">Bạn không có quyền truy cập vào trang này. Vui lòng liên hệ với Quản trị hệ thống để biết thêm chi tiết.</p>

      <div class="action-buttons">
        <button @click="goHome" class="btn btn-secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Quay lại Trang chủ
        </button>
        <button @click="goDashboard" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>
          Dashboard Admin
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Error403",
  methods: {
    goHome() {
      this.$router.push("/");
    },
    goDashboard() {
      // Chuyển về dashboard hoặc trang đầu tiên của role được phép
      const role = Number(localStorage.getItem("vai_tro"));
      if (role === 4) {
        this.$router.push("/nhan-vien/products");
      } else if (role === 5) {
        this.$router.push("/nhan-vien/orders");
      } else {
        this.$router.push("/nhan-vien/dashboard");
      }
    }
  }
};
</script>

<style scoped>
.error-403-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
  position: relative;
  overflow: hidden;
  padding: 20px;
  font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Background glows for premium premium aesthetics */
.bg-glow-1 {
  position: absolute;
  top: 20%;
  left: 25%;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(215, 0, 24, 0.15) 0%, transparent 70%);
  filter: blur(40px);
  z-index: 1;
  pointer-events: none;
}

.bg-glow-2 {
  position: absolute;
  bottom: 20%;
  right: 25%;
  width: 350px;
  height: 350px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(124, 58, 237, 0.12) 0%, transparent 70%);
  filter: blur(50px);
  z-index: 1;
  pointer-events: none;
}

.glass-error-card {
  position: relative;
  z-index: 2;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 48px 32px;
  width: 100%;
  max-width: 500px;
  text-align: center;
  box-shadow: 
    0 4px 30px rgba(0, 0, 0, 0.2),
    inset 0 0 20px rgba(255, 255, 255, 0.02);
  animation: floatCard 4s ease-in-out infinite alternate;
}

.error-icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: rgba(215, 0, 24, 0.1);
  color: #d70018;
  border-radius: 50%;
  margin-bottom: 24px;
  box-shadow: 0 0 20px rgba(215, 0, 24, 0.15);
}

.error-title {
  font-size: 72px;
  font-weight: 800;
  margin: 0;
  line-height: 1;
  background: linear-gradient(135deg, #ff6b7a, #d70018);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -2px;
}

.error-subtitle {
  font-size: 24px;
  font-weight: 700;
  color: #f1f5f9;
  margin: 8px 0 16px;
}

.error-message {
  font-size: 18px;
  font-weight: 600;
  color: #ff6b7a;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.error-desc {
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 32px;
}

.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  font-family: inherit;
}

.btn-primary {
  background: linear-gradient(135deg, #d70018, #b90014);
  color: #fff;
  border: none;
  box-shadow: 0 4px 14px rgba(215, 0, 24, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(215, 0, 24, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border-color: rgba(255, 255, 255, 0.2);
}

@keyframes floatCard {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(-8px);
  }
}

@media (max-width: 576px) {
  .action-buttons {
    flex-direction: column;
  }
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
