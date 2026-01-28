<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useLayoutStore } from '@/stores/layout';
import NavBar from "@/components/admin-panel/nav-bar.vue";
import Menu from "@/components/admin-panel/menu.vue";
import RightBar from "@/components/admin-panel/right-bar.vue";
import Footer from "@/components/admin-panel/footer.vue";
import simplebar from "simplebar-vue";

const route = useRoute();
const router = useRouter();
const layoutStore = useLayoutStore();

const layoutType = computed(() => layoutStore.layoutType);

// Apply layout settings to DOM
const applyLayoutSettings = () => {
  document.documentElement.setAttribute('data-layout', 'horizontal');
  document.documentElement.setAttribute('data-layout-style', 'default');
  document.documentElement.setAttribute('data-layout-position', layoutStore.position);
  document.documentElement.setAttribute('data-layout-width', layoutStore.layoutWidth);
  document.documentElement.setAttribute('data-topbar', layoutStore.topbar);
  document.documentElement.setAttribute('data-sidebar', layoutStore.sidebarColor);
  document.documentElement.setAttribute('data-sidebar-size', layoutStore.sidebarSize);
  document.documentElement.setAttribute('data-sidebar-image', layoutStore.sidebarImage);
  document.documentElement.setAttribute('data-preloader', layoutStore.preloader);
  document.documentElement.setAttribute('data-bs-theme', layoutStore.mode);
  document.documentElement.setAttribute('data-sidebar-visibility', layoutStore.visibility);
  document.documentElement.setAttribute('data-sidebar-user-show', layoutStore.sidebarUserProfile ? 'true' : 'false');
  document.documentElement.setAttribute('data-theme', layoutStore.layoutTheme);
};

onMounted(() => {
  applyLayoutSettings();
});

// Sync layout with store changes
layoutStore.$subscribe(() => {
  applyLayoutSettings();
});


</script>

<template>
  <div>
    <div id="layout-wrapper">
      <NavBar />
      <!-- ========== Horizontal Top Menu ========== -->
      <div class="app-menu navbar-menu">
        <!-- LOGO (hidden in horizontal by CSS) -->
        <div class="navbar-brand-box">
          <router-link to="/" class="logo logo-dark">
            <span class="logo-sm">
              <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/admin-panel/images/logo-dark.png" alt="" height="17" />
            </span>
          </router-link>
          <router-link to="/" class="logo logo-light">
            <span class="logo-sm">
              <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/admin-panel/images/logo-light.png" alt="" height="17" />
            </span>
          </router-link>
        </div>
        <div id="scrollbar">
          <Menu></Menu>
        </div>
        <div class="sidebar-background"></div>
      </div>
      <!-- Vertical Overlay-->
      <div class="vertical-overlay" id="overlay"></div>
    </div>
    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="main-content">
      <div class="page-content">
        <!-- Start Content-->
        <BContainer fluid>
          <slot />
        </BContainer>
      </div>
      <Footer />
    </div>
    <RightBar />
  </div>
</template>