<script lang="ts">
import { defineComponent, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFoodieSearch } from '../composables/useFoodieSearch'

export default defineComponent({
  name: 'FoodieHeader',
  setup() {
    const { t } = useI18n()
    const { openSearch } = useFoodieSearch()
    const isMenuOpen = ref(false)
    const headerRef = ref<HTMLElement | null>(null)

    const toggleMenu = () => {
      isMenuOpen.value = !isMenuOpen.value
    }

    const closeMenu = () => {
      isMenuOpen.value = false
    }

    watch(isMenuOpen, (val) => {
      if (val) {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth
        document.body.style.overflow = 'hidden'
        document.body.style.paddingRight = `${scrollbarWidth}px`
        if (headerRef.value) headerRef.value.style.paddingRight = `${scrollbarWidth}px`
      } else {
        document.body.style.overflow = ''
        document.body.style.paddingRight = ''
        if (headerRef.value) headerRef.value.style.paddingRight = ''
      }
    })

    return {
      t,
      openSearch,
      isMenuOpen,
      headerRef,
      toggleMenu,
      closeMenu
    }
  }
})
</script>

<template>
  <header ref="headerRef" class="foodie-header">
    <div class="foodie-header__inner">
      <!-- Logo -->
      <a href="#" class="foodie-header__logo">
        <span class="foodie-header__logo-text">{{ t('foodie.logo') }}</span>
      </a>

      <!-- Actions Group (Right Aligned) -->
      <div class="foodie-header__actions">
        <!-- Desktop Navigation -->
        <nav class="foodie-header__nav">
          <a href="#" class="foodie-header__link" @click.prevent="openSearch">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: middle;">
              <circle cx="11" cy="11" r="8"/>
              <path d="m21 21-4.35-4.35"/>
            </svg>
            {{ t('foodie.search') }}
          </a>
          <a href="#" class="foodie-header__link">{{ t('foodie.celebrate') }}</a>
          <a href="#" class="foodie-header__link">{{ t('foodie.signUp') }}</a>
          <button class="foodie-header__btn foodie-header__btn--primary">{{ t('foodie.logIn') }}</button>
        </nav>

        <!-- Hamburger button -->
        <button class="foodie-header__menu-btn" @click="toggleMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Sidebar Overlay -->
    <Transition name="fade">
      <div v-if="isMenuOpen" class="foodie-sidebar-overlay" @click="closeMenu"></div>
    </Transition>

    <!-- Sidebar Menu -->
    <Transition name="slide">
      <div v-if="isMenuOpen" class="foodie-sidebar">
        <div class="foodie-sidebar__header">
          <button class="foodie-sidebar__close" @click="closeMenu">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="foodie-sidebar__content">
          <div class="foodie-sidebar__auth">
            <button class="foodie-sidebar__signup-btn">{{ t('foodie.signUp') }}</button>
            <button class="foodie-sidebar__login-btn">{{ t('foodie.logIn') }}</button>
          </div>

          <nav class="foodie-sidebar__nav">
            <a href="#" class="foodie-sidebar__link" @click="openSearch(); closeMenu()">{{ t('foodie.search') }}</a>
            <a href="#" class="foodie-sidebar__link">{{ t('foodie.planCelebration') }}</a>
            <a href="#" class="foodie-sidebar__link">{{ t('foodie.aboutUs') }}</a>
            <a href="#" class="foodie-sidebar__link">{{ t('foodie.faqs') }}</a>
            <a href="#" class="foodie-sidebar__link">{{ t('foodie.partners') }}</a>
          </nav>

          <div class="foodie-sidebar__app">
            <h4 class="foodie-sidebar__app-title">{{ t('foodie.getTheApp') }}</h4>
            <div class="foodie-sidebar__app-grid">
              <!-- Cheers App Icon -->
              <div class="foodie-sidebar__app-icon">
                <span class="foodie-sidebar__app-name">ch<span class="ee">ee</span>rs</span>
              </div>

              <!-- Store Buttons Container -->
              <div class="foodie-sidebar__app-stores">
                <a href="#" class="foodie-sidebar__store-btn">
                  <svg class="icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.1 2.48-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5 1.13 3.28 1.13.78 0 2.21-1.15 3.74-1 1.15.02 2.15.42 2.89 1.48-2.55 1.5-2.13 4.88.58 6.03-.69 1.73-1.61 3.49-2.6 4.98zM13 5c.44-2.34-1.38-4.44-3.5-4.44-.06 2.3 2.14 4.4 3.5 4.44z"/>
                  </svg>
                  App Store
                </a>
                <a href="#" class="foodie-sidebar__store-btn">
                  <svg class="icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                  </svg>
                  Playstore
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </header>
</template>

<style lang="scss" scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}
</style>
