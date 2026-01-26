<template>
  <Teleport to="body">
    <!-- Backdrop Overlay -->
    <div 
      class="mkp-side-overlay"
      :class="{ 'is-visible': layoutStore.isMarketplaceUserOpen }"
      @click="layoutStore.isMarketplaceUserOpen = false"
    ></div>

    <!-- User Sidebar Content -->
    <aside 
      class="mkp-right-sidebar user-sidebar"
      :class="{ 'is-open': layoutStore.isMarketplaceUserOpen }"
    >
      <div class="sidebar-top">
        <h2 class="title text-[22px] font-bold text-slate-800">Sign in</h2>
        <button 
          @click="layoutStore.isMarketplaceUserOpen = false"
          class="close-btn flex items-center gap-1.5 text-[12px] font-bold uppercase tracking-wider text-slate-400 hover:text-red-500 transition-colors"
        >
          <i class="fas fa-times text-[14px]"></i>
          <span>Close</span>
        </button>
      </div>

      <div class="sidebar-body p-8 overflow-y-auto h-[calc(100%-80px)]">
        <!-- Form -->
        <form @submit.prevent class="space-y-6">
          <div class="form-group">
            <label class="block text-[13px] font-bold text-slate-700 mb-2">
              Username or email address <span class="text-red-500">*</span>
            </label>
            <input 
              type="text" 
              class="w-full h-[45px] px-4 border border-slate-200 rounded-sm focus:border-primary focus:outline-none transition-colors"
            />
          </div>

          <div class="form-group">
            <label class="block text-[13px] font-bold text-slate-700 mb-2">
              Password <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input 
                type="password" 
                class="w-full h-[45px] px-4 border border-slate-200 rounded-sm focus:border-primary focus:outline-none transition-colors"
              />
              <i class="far fa-eye absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer text-xs"></i>
            </div>
          </div>

          <button class="w-full bg-primary text-white h-[45px] rounded-md font-bold text-[14px] hover:bg-primary-dark transition-colors shadow-sm">
            Log In
          </button>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" class="w-4 h-4 border-slate-200 rounded-sm text-primary focus:ring-primary" />
              <span class="text-[12px] font-bold text-slate-500 group-hover:text-primary transition-colors">Remember me</span>
            </label>
            <a href="#" class="text-[12px] font-bold text-primary hover:underline">Lost your password?</a>
          </div>
        </form>

        <!-- No Account Section -->
        <div class="mt-12 pt-12 border-t border-slate-100 flex flex-col items-center">
          <div class="w-[70px] h-[70px] bg-slate-50 rounded-full flex items-center justify-center mb-6">
            <i class="far fa-user text-[32px] text-slate-200"></i>
          </div>
          <h3 class="text-[18px] font-bold text-slate-800 mb-2">No account yet?</h3>
          <a href="#" class="text-[13px] font-bold text-slate-400 hover:text-primary underline decoration-2 underline-offset-4 decoration-slate-200 hover:decoration-primary transition-all">
            Create An Account
          </a>
        </div>
      </div>
    </aside>
  </Teleport>
</template>

<script setup lang="ts">
import { useLayoutStore } from '@/stores/layout'

const layoutStore = useLayoutStore()
</script>

<style lang="scss" scoped>
.mkp-side-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 2500;
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  backdrop-filter: blur(2px);
  pointer-events: none;

  &.is-visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }
}

.mkp-right-sidebar {
  position: fixed;
  right: 0;
  top: 0;
  bottom: 0;
  width: 400px;
  background: white;
  z-index: 3000;
  box-shadow: -10px 0 50px rgba(0, 0, 0, 0.15);
  transform: translateX(100%);
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: transform;

  &.is-open {
    transform: translateX(0);
  }

  .sidebar-top {
    height: 80px;
    padding: 0 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #eee;
  }
}

@media (max-width: 480px) {
  .mkp-right-sidebar {
    width: 320px;
  }
}
</style>
