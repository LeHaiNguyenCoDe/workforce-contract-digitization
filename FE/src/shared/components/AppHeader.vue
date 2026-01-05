<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}
</script>

<template>
  <header class="header glass">
    <div class="container header-container">
      <!-- Logo -->
      <RouterLink to="/" class="logo">
        <span class="logo-text gradient-text">Store</span>
      </RouterLink>

      <!-- Navigation -->
      <nav class="nav" :class="{ 'nav-open': isMobileMenuOpen }">
        <RouterLink to="/" class="nav-link">Trang chủ</RouterLink>
        <RouterLink to="/products" class="nav-link">Sản phẩm</RouterLink>
        <RouterLink to="/articles" class="nav-link">Bài viết</RouterLink>
        <RouterLink to="/promotions" class="nav-link">Khuyến mãi</RouterLink>
      </nav>

      <!-- Actions -->
      <div class="header-actions">
        <RouterLink to="/cart" class="action-btn" title="Giỏ hàng">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="21" r="1" />
            <circle cx="19" cy="21" r="1" />
            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
          </svg>
          <span class="cart-badge" v-if="authStore.cartCount > 0">{{ authStore.cartCount }}</span>
        </RouterLink>

        <template v-if="authStore.isAuthenticated">
          <RouterLink to="/wishlist" class="action-btn" title="Yêu thích">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
          </RouterLink>
          <RouterLink to="/profile" class="action-btn user-btn">
            <span class="user-avatar">{{ authStore.user?.name?.charAt(0) || 'U' }}</span>
          </RouterLink>
        </template>
        <template v-else>
          <RouterLink to="/login" class="btn btn-primary btn-sm">Đăng nhập</RouterLink>
        </template>

        <!-- Mobile menu button -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="4" x2="20" y1="12" y2="12" />
            <line x1="4" x2="20" y1="6" y2="6" />
            <line x1="4" x2="20" y1="18" y2="18" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="M6 6 18 18" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  position: sticky;
  top: 0;
  z-index: var(--z-sticky);
  padding: var(--space-4) 0;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-8);
}

.logo {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.logo-text {
  font-family: var(--font-display);
  font-size: var(--text-2xl);
  font-weight: 700;
}

.nav {
  display: flex;
  align-items: center;
  gap: var(--space-6);
}

.nav-link {
  color: var(--color-text-secondary);
  font-weight: 500;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--color-text-primary);
  background: rgba(99, 102, 241, 0.1);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.action-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  color: var(--color-text-secondary);
  border-radius: var(--radius-full);
  transition: all var(--transition-fast);
}

.action-btn:hover {
  color: var(--color-text-primary);
  background: rgba(99, 102, 241, 0.1);
}

.cart-badge {
  position: absolute;
  top: 0;
  right: 0;
  width: 18px;
  height: 18px;
  font-size: var(--text-xs);
  font-weight: 600;
  color: white;
  background: var(--color-secondary);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-avatar {
  width: 32px;
  height: 32px;
  font-size: var(--text-sm);
  font-weight: 600;
  color: white;
  background: var(--gradient-primary);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-sm {
  padding: var(--space-2) var(--space-4);
  font-size: var(--text-sm);
}

.mobile-menu-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  color: var(--color-text-secondary);
  background: transparent;
  border: none;
  cursor: pointer;
}

@media (max-width: 768px) {
  .nav {
    position: fixed;
    top: 72px;
    left: 0;
    right: 0;
    flex-direction: column;
    padding: var(--space-4);
    background: var(--color-bg-secondary);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none;
    transition: all var(--transition-normal);
  }

  .nav-open {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
  }

  .mobile-menu-btn {
    display: flex;
  }
}
</style>
