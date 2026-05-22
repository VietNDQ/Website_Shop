<template>
  <div class="admin-layout">
    <!-- Sidebar dọc -->
    <Sidebar_Admin />

    <!-- Phần nội dung bên phải -->
    <div class="admin-body">
      <!-- Topbar -->
      <Topbar_Admin :page-title="$route.meta.title || 'Dashboard'" />

      <!-- Main content -->
      <main class="admin-main">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import Sidebar_Admin from "../components/Admin/Sidebar_Admin.vue";
import Topbar_Admin from "../components/Admin/Topbar_Admin.vue";

export default {
  name: "AdminLayout",
  components: {
    Sidebar_Admin,
    Topbar_Admin,
  },
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Barlow+Condensed:wght@500;700;800;900&display=swap");
@import "/style_admin/admin-common.css";

.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f1f5f9;
  font-family: 'DM Sans', sans-serif;
}

.admin-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.admin-main {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
}
</style>
