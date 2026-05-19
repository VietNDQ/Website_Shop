<template>
  <!-- ─── NAVBAR (Row 2) ─── -->
  <nav>
    <div class="nav-inner">
      <!-- Logo -->
      <a href="#" class="nav-logo">
        BALAB
        <div class="nav-logo-s">
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
          >
            <polyline points="5 12 12 5 19 12" />
            <polyline points="5 19 12 12 19 19" />
          </svg>
        </div>
      </a>

      <!-- Danh mục -->
      <button class="nav-cat-btn">
        <svg
          width="15"
          height="15"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <rect x="3" y="3" width="7" height="7" />
          <rect x="14" y="3" width="7" height="7" />
          <rect x="14" y="14" width="7" height="7" />
          <rect x="3" y="14" width="7" height="7" />
        </svg>
        Danh mục
        <svg
          width="12"
          height="12"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>

      <!-- Location -->
      <button class="nav-loc-btn">
        <svg
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
        Đà Nẵng
        <svg
          width="11"
          height="11"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>

      <!-- Search -->
      <div class="nav-search-wrap">
        <svg
          class="nav-search-icon"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#9ca3af"
          stroke-width="2"
        >
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input
          class="nav-search"
          type="text"
          placeholder="Bạn muốn mua gì hôm nay?"
        />
      </div>

      <!-- Cart -->
      <button class="btn-cart">
        <svg
          width="18"
          height="18"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <path d="M16 10a4 4 0 01-8 0" />
        </svg>
        Giỏ hàng
        <div class="cart-badge">3</div>
      </button>

      <!-- Account -->
      <template v-if="isLoggedIn">
        <button class="btn-account">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          {{ userName }}
        </button>
      </template>
      <template v-else>
        <button class="btn-account" @click="$router.push('/login')">
          Đăng nhập
        </button>
      </template>
    </div>
  </nav>
</template>
<script>
export default {
  data() {
    return {
      // Giả lập trạng thái đăng nhập
      isLoggedIn: false,
      userName: "Việt",
    };
  },
};
</script>
<style></style>
