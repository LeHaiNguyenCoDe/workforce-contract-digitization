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
          <BaseIcon name="cart" />
          <span class="cart-badge" v-if="authStore.cartCount > 0">{{ authStore.cartCount }}</span>
        </RouterLink>

        <template v-if="authStore.isAuthenticated">
          <RouterLink to="/wishlist" class="action-btn" title="Yêu thích">
            <BaseIcon name="heart" />
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
          <BaseIcon v-if="!isMobileMenuOpen" name="menu" />
          <BaseIcon v-else name="x" />
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
