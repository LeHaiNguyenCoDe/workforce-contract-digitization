<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import simplebar from "simplebar-vue";
import { useLayoutStore } from '@/stores/layout';
import { useAuthStore } from '@/stores';
import { useI18n } from 'vue-i18n';

import NavBar from "@/components/admin-panel/nav-bar.vue";
import Menu from "@/components/admin-panel/menu.vue";
import RightBar from "@/components/admin-panel/right-bar.vue";
import Footer from "@/components/admin-panel/footer.vue";

localStorage.setItem('hoverd', 'false');

const { t } = useI18n();
const router = useRouter();
const layoutStore = useLayoutStore();
const authStore = useAuthStore();

const isMenuCondensed = ref(false);

const layoutType = computed(() => layoutStore.layoutType);

// User computed values from auth store
const userName = computed(() => authStore.userName || 'User');
const userAvatar = computed(() => (authStore.user as any)?.avatar || null);
const userInitials = computed(() => {
  const name = userName.value;
  if (!name) return 'U';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return parts[0][0] + parts[1][0];
  }
  return name.substring(0, 2);
});

// Logout function
const handleLogout = async () => {
  await authStore.logout();
  router.push('/');
};

// Apply layout settings to DOM
const applyLayoutSettings = () => {
  document.documentElement.setAttribute('data-layout', layoutStore.layoutType);
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

// Subscribe to store changes instead of using watch
layoutStore.$subscribe(() => {
  applyLayoutSettings();
});


const updateSidebarSize = () => {
  let sidebarSize = '';
  if (window.innerWidth < 1025) {
    sidebarSize = 'sm';
  } else {
    sidebarSize = layoutStore.sidebarSize;
  }
  document.documentElement.setAttribute("data-sidebar-size", sidebarSize);
};

const initActiveMenu = () => {
  const currentSize = document.documentElement.getAttribute('data-sidebar-size');
  if (currentSize === 'sm-hover') {
    localStorage.setItem('hoverd', 'true');
    document.documentElement.setAttribute('data-sidebar-size', 'sm-hover-active');
  } else if (currentSize === 'sm-hover-active') {
    localStorage.setItem('hoverd', 'false');
    document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
  } else {
    document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
  }
};

const toggleMenu = () => {
  document.body.classList.toggle("sidebar-enable");
  if (window.innerWidth >= 992) {
    router.afterEach(() => {
      document.body.classList.remove("sidebar-enable");
      document.body.classList.remove("vertical-collpsed");
    });
    document.body.classList.toggle("vertical-collpsed");
  } else {
    router.afterEach(() => {
      document.body.classList.remove("sidebar-enable");
    });
    document.body.classList.remove("vertical-collpsed");
  }
  isMenuCondensed.value = !isMenuCondensed.value;
};

const toggleRightSidebar = () => {
  document.body.classList.toggle("right-bar-enabled");
};

onMounted(() => {
  // Apply initial layout settings from store
  applyLayoutSettings();

  if (localStorage.getItem('hoverd') === 'true') {
    document.documentElement.setAttribute('data-sidebar-size', 'sm-hover-active');
  }

  const overlay = document.getElementById('overlay');
  if (overlay) {
    overlay.addEventListener('click', () => {
      document.body.classList.remove('vertical-sidebar-enable');
    });
  }

  if (window.innerWidth < 1025) {
    document.documentElement.setAttribute("data-sidebar-size", "sm");
  }

  window.addEventListener("resize", () => {
    document.body.classList.remove('vertical-sidebar-enable');
    const hamburgerIcon = document.querySelector(".hamburger-icon");
    if (hamburgerIcon) hamburgerIcon.classList.add("open");
    updateSidebarSize();
  });
});

onUnmounted(() => {
  window.removeEventListener("resize", updateSidebarSize);
});
</script>
  
<template>
  <div id="layout-wrapper">
    <NavBar />
    <div>
      <!-- ========== Left Sidebar Start ========== -->
      <!-- ========== App Menu ========== -->
      <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box">
          <!-- Dark Logo-->
          <router-link to="/" class="logo logo-dark">
            <span class="logo-sm">
              <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/admin-panel/images/logo-dark.png" alt="" height="17" />
            </span>
          </router-link>
          <!-- Light Logo-->
          <router-link to="/" class="logo logo-light">
            <span class="logo-sm">
              <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/admin-panel/images/logo-light.png" alt="" height="17" />
            </span>
          </router-link>
          <BButton size="sm" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover" @click="initActiveMenu">
            <i class="ri-record-circle-line"></i>
          </BButton>
        </div>

        <BDropdown variant="link" class="sidebar-user m-1 rounded" toggle-class="rounded-circle" no-caret
          id="page-header-user-dropdown" menu-class="dropdown-menu-end"
          :offset="{ alignmentAxis: -14, crossAxis: 0, mainAxis: 0 }">
          <template #button-content>
            <span class="d-flex align-items-center gap-2">
              <template v-if="userAvatar">
                <img class="rounded header-profile-user" :src="userAvatar" alt="Header Avatar">
              </template>
              <template v-else>
                <span class="avatar-xs">
                  <span class="avatar-title rounded-circle bg-primary text-white">
                    {{ userInitials.toUpperCase() }}
                  </span>
                </span>
              </template>
              <span class="text-start">
                <span class="d-block fw-medium sidebar-user-name-text">{{ userName }}</span>
                <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                    class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                    class="align-middle">Online</span></span>
              </span>
            </span>
          </template>
          <h6 class="dropdown-header">{{ t('t-welcome') }}, {{ userName }}!</h6>
          <router-link class="dropdown-item" to="/admin/profile">
            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">{{ t('t-profile') }}</span>
          </router-link>
          <router-link class="dropdown-item" to="/admin/chat">
            <i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">{{ t('t-messages') }}</span>
          </router-link>
          <router-link class="dropdown-item" to="/admin/tasks">
            <i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">{{ t('t-taskboard') }}</span>
          </router-link>
          <div class="dropdown-divider"></div>
          <router-link class="dropdown-item" to="/admin/settings">
            <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">{{ t('t-settings') }}</span>
          </router-link>
          <BLink class="dropdown-item" @click="handleLogout">
            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">{{ t('t-logout') }}</span>
          </BLink>
        </BDropdown>

        <simplebar id="scrollbar" class="h-100" ref="scrollbar">
          <Menu></Menu>
        </simplebar>
        <div class="sidebar-background"></div>
      </div>
      <!-- Left Sidebar End -->
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