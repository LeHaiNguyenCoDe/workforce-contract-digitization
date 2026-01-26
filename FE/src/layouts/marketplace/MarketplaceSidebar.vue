<template>
  <aside 
    class="mkp-sidebar" 
    :class="{ 'is-open': layoutStore.isMarketplaceSidebarOpen || isHovered }"
    @mouseenter="isHovered = true" 
    @mouseleave="isHovered = false"
  >
    <!-- Header: The Blue Button -->
    <div class="sidebar-header">
      <div class="all-cats-wrapper">
        <div class="all-cats-btn">
          <i class="fas fa-bars"></i>
          <span class="label">All Categories</span>
        </div>
      </div>
    </div>

    <!-- Category List -->
    <ul class="category-list">
      <li 
        v-for="cat in categories" 
        :key="cat.name" 
        class="category-item group/cat"
      >
        <div class="item-link">
          <div class="icon-box">
            <i :class="cat.icon"></i>
          </div>
          <span class="name">{{ cat.name }}</span>
          <i v-if="cat.hasMega" class="fas fa-chevron-right arrow"></i>
        </div>

        <div v-if="cat.hasMega" class="mega-menu">
           <div class="mega-content h-full flex flex-col p-8">
              <div class="flex flex-col mb-6">
                <div v-for="(col, index) in cat.megaData || []" :key="col.title" 
                     :class="['py-6', index !== (cat.megaData?.length || 0) - 1 ? 'border-b border-slate-100' : '']">
                  <h4 class="text-[13px] font-bold text-slate-800 uppercase tracking-wider mb-4">{{ col.title }}</h4>
                  <ul class="space-y-3">
                    <li v-for="link in col.items" :key="link" class="text-[14px] text-slate-500 hover:text-primary cursor-pointer transition-colors">
                      {{ link }}
                    </li>
                  </ul>
                </div>
              </div>
              
              <!-- Bottom Promo Banner -->
              <div class="mt-auto relative rounded-xl overflow-hidden bg-slate-900 h-[380px] group/promo">
                <img src="https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-menu-banner-1.jpg" alt="Promo" class="w-full h-full object-cover group-hover/promo:scale-105 transition-transform duration-700 opacity-80" />
                <div class="absolute inset-0 flex flex-col justify-end p-8 bg-gradient-to-t from-black/60 to-transparent">
                  <div class="text-[10px] font-bold uppercase tracking-[2px] text-white/90 mb-2">On Sale</div>
                  <h3 class="text-2xl font-bold text-white mb-4">HP Envy 34</h3>
                  <button class="bg-[#3771e0] text-white w-fit px-6 py-2.5 rounded-md text-[12px] font-bold uppercase tracking-wider hover:bg-blue-600 transition-colors">To Shop</button>
                </div>
              </div>
           </div>
        </div>
      </li>
    </ul>

    <!-- Bottom Placeholder Icons -->
    <div class="sidebar-footer mt-auto pb-6">
       <div v-for="i in 3" :key="i" class="footer-icon">
         <i :class="footerIcons[i-1]"></i>
       </div>
    </div>

    <!-- Background Overlay -->
    <Teleport to="body">
      <div 
        class="mkp-sidebar-overlay"
        :class="{ 'visible': layoutStore.isMarketplaceSidebarOpen || isHovered }"
        @click="closeSidebar"
      ></div>
    </Teleport>
  </aside>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useLayoutStore } from '@/stores/layout'
import { SIDEBAR_CATEGORIES, POPULAR_CATEGORIES, STORES } from '@/modules/marketplace/configs'

const layoutStore = useLayoutStore()
const isHovered = ref(false)

const closeSidebar = () => {
  layoutStore.isMarketplaceSidebarOpen = false
  isHovered.value = false
}

const footerIcons = ['fas fa-shopping-bag', 'fas fa-heart', 'fas fa-cog']
const categories = ref(SIDEBAR_CATEGORIES)
const popularCategories = POPULAR_CATEGORIES
const stores = STORES
</script>

<style lang="scss" scoped>
.mkp-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 2000;
  background: white;
  width: 60px;
  display: flex;
  flex-direction: column;
  transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border-right: 1px solid #eeeeee;
  overflow: visible;
  will-change: width;

  &:hover,
  &.is-open {
    width: 320px;
    box-shadow: 20px 0 50px rgba(0,0,0,0.12);

    .sidebar-header .all-cats-btn {
      width: 100%;
      border-radius: 22px; // Keep pill shape
    }

    .sidebar-header .all-cats-btn .label {
      opacity: 1 !important;
      visibility: visible !important;
      transform: none !important; // Remove shift
      max-width: 200px !important;
      transition-delay: 0s;
    }

    .category-item .name, .category-item .arrow {
      opacity: 1;
      visibility: visible;
      transform: translateX(0);
    }
  }

  .sidebar-header {
    height: 90px;
    display: flex;
    align-items: center;
    padding: 0 8px; // Static padding for absolute stability
    // No transition on padding to avoid icon wobble
    .all-cats-wrapper {
      width: 100%;
      height: 48px;
    }

    .all-cats-btn {
      width: 44px;
      height: 44px;
      background: var(--mkp-primary);
      color: white;
      border-radius: 22px; 
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 0 10px; 
      cursor: pointer;
      transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), 
                  background 0.2s;
      overflow: hidden;
      white-space: nowrap;
      flex-shrink: 0;
      will-change: width;
      
      i { 
        font-size: 16px; 
        width: 24px; // Fixed width for the icon to keep it stable
        text-align: center;
        flex-shrink: 0; 
      }
      
      .label {
        opacity: 0;
        visibility: hidden;
        max-width: 0;
        font-size: 15px;
        font-weight: 700;
        text-transform: none;
        margin-left: 12px;
        transform: none; // No shift, just expand
        transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), 
                    max-width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        display: inline-block;
        overflow: hidden;
        will-change: opacity, max-width;
      }
    }
  }

  .category-list {
    flex: 1;
    padding: 10px 0;
  }

  .category-item {
    position: relative;
    
    .item-link {
      height: 54px;
      display: flex;
      align-items: center;
      padding: 0;
      cursor: pointer;
      transition: background 0.2s;
      
      &:hover {
        background: #f9f9f9;
        .icon-box { color: var(--mkp-primary); }
        .name { color: var(--mkp-primary); }
      }
    }

    .icon-box {
      width: 60px;
      height: 54px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: #333;
      transition: color 0.2s;
      flex-shrink: 0;
    }

    .name {
      font-size: 14px;
      font-weight: 600;
      color: #333;
      opacity: 0;
      visibility: hidden;
      white-space: nowrap;
      transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      transform: none;
      will-change: opacity;
    }

    .arrow {
      margin-left: auto;
      margin-right: 25px;
      font-size: 10px;
      color: #ccc;
      opacity: 0;
      transition: opacity 0.3s;
    }

    // Mega Menu (Stage 3)
    .mega-menu {
      position: fixed;
      left: 320px;
      top: 0;
      width: 320px;
      height: 100vh;
      background: white;
      box-shadow: 15px 0 40px rgba(0,0,0,0.08);
      border-left: 1px solid #eeeeee;
      opacity: 0;
      visibility: hidden;
      transform: none;
      transition: opacity 0.2s ease;
      z-index: 2100;
      pointer-events: none;
    }

    &:hover .mega-menu {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
    }
  }

  .footer-icon {
    width: 60px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #ccc;
    cursor: pointer;
    &:hover { color: var(--mkp-primary); }
  }
}

// Background Overlay Styles
.mkp-sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4); // Slightly lighter for better contrast
  z-index: 1500; // Above main content but below marketplace sidebar
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(1.5px);
  pointer-events: none;

  &.visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }
}

// Adjust Sidebar height to match first category top
.category-item:first-child .mega-menu {
  top: 0; 
}

.category-list {
  position: static;
}
</style>
