<template>
  <div class="client-layout">
    <header class="header-nav">
      <Header_Client />
      <Menu_Client />
    </header>

    <main class="main-content">
      <router-view />
    </main>

    <Footer_Client />
  </div>
</template>

<script>
import Header_Client from "../components/Client/Header_Client.vue";
import Menu_Client from "../components/Client/Menu_Client.vue";
import Footer_Client from "../components/Client/Footer_Client.vue";

export default {
  name: "ClientLayout",
  components: {
    Header_Client,
    Menu_Client,
    Footer_Client,
  },
};
</script>

<style>
/* Font Awesome cho các icon trong thiết kế */
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css");

.client-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.header-nav {
  position: sticky;
  top: 0;
  z-index: 1000;
}

.main-content {
  flex: 1;
  background-color: var(--bg);
}
</style>
