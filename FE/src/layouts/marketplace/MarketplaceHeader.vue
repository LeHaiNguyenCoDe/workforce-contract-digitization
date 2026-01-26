<template>
  <header 
    class="mkp-header"
    :style="{ 
      top: headerStickyTop + 'px'
    }"
  >
    <!-- Top Bar -->
    <div class="container-mkp flex items-center justify-between h-[90px] bg-white">
      <!-- Logo -->
      <router-link to="/marketplace" class="logo shrink-0">
        <img src="@/assets/marketplace/images/wood-logo-dark.svg" alt="WoodMart" class="h-[40px]" />
      </router-link>

      <!-- Search -->
      <div class="search-container flex-grow max-w-[650px] mx-10">
        <div class="relative flex items-center">
          <input 
            type="text" 
            placeholder="Search for products" 
            class="search-input w-full h-[50px] px-6 rounded-full border border-slate-200 focus:border-primary focus:outline-none transition-colors text-[15px]"
          />
          <button class="search-btn absolute right-1.5 w-[38px] h-[38px] bg-primary text-white rounded-full flex items-center justify-center hover:bg-primary-dark transition-colors">
            <i class="fas fa-search text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Support Info -->
      <div class="hidden xl:flex items-center gap-10 shrink-0">
        <div class="flex items-center gap-3">
          <div class="info-icon">
            <i class="fas fa-headset"></i>
          </div>
          <div class="flex flex-col">
            <span class="info-label">24 Support</span>
            <span class="info-value">+1 212-334-0212</span>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="info-icon">
            <i class="fas fa-globe"></i>
          </div>
          <div class="flex flex-col">
            <span class="info-label">Worldwide</span>
            <span class="info-value text-primary">Free Shipping</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Nav Bar -->
    <nav class="nav-bar h-[55px] z-[1000] border-t border-slate-100 shadow-sm">
      <div class="container-mkp flex items-center justify-between h-full">
        <div class="flex items-center h-full gap-10">
          <!-- Categories Button -->
          <div class="relative group h-full flex items-center">
            <button 
              @click="layoutStore.toggleMarketplaceSidebar()"
              class="all-cats-pill bg-white hover:bg-slate-50 transition-colors h-[42px] px-1 pr-6 rounded-full flex items-center gap-3 shadow-sm border border-slate-100"
            >
              <div class="blue-circle w-[34px] h-[34px] bg-primary rounded-full flex items-center justify-center text-white">
                <i class="fas fa-bars text-xs"></i>
              </div>
              <span class="text-[15px] font-bold text-slate-900 tracking-tight">All Categories</span>
            </button>
            <!-- Mock Dropdown -->
            <div class="absolute top-full left-0 w-[280px] bg-white shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border-t-2 border-primary mt-1">
              <ul class="py-2">
                <li v-for="cat in mockCats" :key="cat" class="px-6 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 cursor-pointer flex items-center justify-between group/item">
                  <span class="text-sm font-medium text-slate-700 group-hover/item:text-primary transition-colors">{{ cat }}</span>
                  <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                </li>
              </ul>
            </div>
          </div>

          <!-- Main Menu -->
          <ul class="hidden lg:flex items-center gap-9 h-full">
            <li v-for="item in menuItems" :key="item" class="text-[14px] font-semibold text-slate-700 hover:text-primary cursor-pointer transition-colors whitespace-nowrap h-full flex items-center border-b-2 border-transparent hover:border-primary px-1 pt-0.5">
              {{ item }}
            </li>
          </ul>
        </div>
        
        <div class="flex items-center gap-6 h-full">
          <!-- Locales -->
          <div class="hidden xl:flex items-center gap-4 border-r pr-6 border-slate-200">
            <div class="flex items-center gap-1.5 cursor-pointer text-[12px] font-medium text-slate-600 hover:text-primary tracking-wide">
              <span>USA</span>
              <i class="fas fa-chevron-down text-[8px] opacity-50"></i>
            </div>
            <div class="flex items-center gap-1.5 cursor-pointer text-[12px] font-medium text-slate-600 hover:text-primary tracking-wide">
              <span>USD</span>
              <i class="fas fa-chevron-down text-[8px] opacity-50"></i>
            </div>
          </div>

          <!-- Actions Icons -->
          <div class="flex items-center gap-4">
            <div 
              class="action-btn"
              @click="layoutStore.toggleMarketplaceUser()"
            >
              <i class="far fa-user text-[17px]"></i>
            </div>
            <div class="action-btn relative">
              <i class="fas fa-random text-[14px]"></i>
              <span class="badge">0</span>
            </div>
            <div class="action-btn relative">
              <i class="far fa-heart text-[17px]"></i>
              <span class="badge">0</span>
            </div>
            <router-link 
              to="/marketplace/cart"
              class="flex items-center gap-3 cursor-pointer group/cart"
            >
              <div class="relative w-[40px] h-[40px] bg-primary rounded-full flex items-center justify-center text-white group-hover/cart:bg-primary-dark transition-all">
                <i class="fas fa-shopping-basket text-sm"></i>
                <span class="badge !-top-1 !-right-1 border-2 border-[#f1f6ff]">{{ cartStore.totalItems }}</span>
              </div>
              <span class="text-[14px] font-bold text-slate-900">{{ formatPrice(cartStore.totalPrice) }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script lang="ts">
export default {
  name: 'MarketplaceHeader'
}
</script>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useLayoutStore } from '@/stores/layout'
import { useCartStore } from '@/modules/marketplace/stores/cart'
import { MENU_ITEMS } from '@/modules/marketplace/configs'
import { formatPrice } from '@/modules/marketplace/helpers/format'

const layoutStore = useLayoutStore()
const cartStore = useCartStore()
const menuItems = ref(MENU_ITEMS)
const headerStickyTop = ref(-90)
let lastScrollY = 0

const handleScroll = () => {
  const scrollY = window.scrollY
  const sections = document.querySelectorAll('.mkp-section')
  const popularSection = sections[1] || sections[0]
  const boundaryTop = popularSection ? (popularSection as HTMLElement).offsetTop : 800
  
  // Directional visibility logic
  const scrollingDown = scrollY > lastScrollY
  
  if (scrollY < 90) {
    // Top state: Normal sticking boundary
    headerStickyTop.value = -90 
  } else if (scrollingDown) {
    if (scrollY > (boundaryTop + 200)) { 
      // Deep scroll down: Hide entirely
      headerStickyTop.value = -145
    } else {
      // Near top or initial scroll: Stick the nav bar
      headerStickyTop.value = -90
    }
  } else {
    // Scrolling up: Reveal the sticky nav bar instantly
    headerStickyTop.value = -90
  }
  
  lastScrollY = scrollY
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

const mockCats = [
  'Accessories',
  'Camera & Video',
  'Computer & Laptop',
  'Consumer Electrics',
  'Gadgets',
  'Health & Beauty',
  'Home & Kitchen',
  'Smart Phones',
  'Video Games',
  'Watches'
]
</script>

<style lang="scss" scoped>
.mkp-header {
  border-bottom: 1px solid #eee;
  position: sticky;
  z-index: 1000;
  transition: top 0.4s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease;
  background-color: white;

  .logo-text {
    font-size: 32px;
    font-weight: 800;
    color: #000;
    letter-spacing: -1.5px;
    span { color: var(--mkp-primary); }
  }

  .search-input {
    background: #fbfbfb; // Very light gray from sample
    &::placeholder {
      font-size: 14px;
      color: #999;
    }
  }

  .info-icon {
    font-size: 36px;
    color: #000;
    opacity: 0.8;
  }
  
  .info-label {
    font-size: 11px;
    font-weight: 700;
    color: #777;
    text-transform: uppercase;
    line-height: 1.2;
  }

  .info-value {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
  }

  .nav-bar {
    background-color: #f1f6ff;
    transition: background-color 0.3s ease;
  }

  .action-btn {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #000;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.2s;
    &:hover {
      color: var(--mkp-primary);
      transform: translateY(-2px);
    }
  }

  .badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--mkp-primary);
    color: white;
    font-size: 9px;
    font-weight: 700;
    height: 16px;
    width: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-style: italic;
  }
}
</style>
