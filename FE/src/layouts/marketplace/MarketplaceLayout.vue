<template>
  <div class="marketplace-wrapper min-h-screen flex flex-col">
    <MarketplaceSidebar />
    <MarketplaceHeader class="ml-[60px]" />
    <main class="flex-grow pl-[60px]">
      <RouterView v-slot="{ Component }">
        <transition 
          name="fade" 
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </RouterView>
    </main>
    <MarketplaceFooter class="ml-[60px]" />
    
    <!-- Sidebars -->
    <MarketplaceUserSidebar />
    <MarketplaceCartSidebar />
    
    <!-- Floating Back to Top Button -->
    <button 
      v-show="showBackToTop"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 w-12 h-12 bg-primary flex items-center justify-center rounded-full shadow-lg text-white z-50 transition-all hover:scale-110"
    >
      <i class="fas fa-chevron-up"></i>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import { useLayoutStore } from '@/stores/layout'
import MarketplaceHeader from './MarketplaceHeader.vue'
import MarketplaceFooter from './MarketplaceFooter.vue'
import MarketplaceSidebar from './MarketplaceSidebar.vue'
import MarketplaceUserSidebar from './MarketplaceUserSidebar.vue'
import MarketplaceCartSidebar from './MarketplaceCartSidebar.vue'

// Woodmart Assets
import '@/assets/marketplace/css/bootstrap.css'
import '@/assets/marketplace/css/style-elementor.css'

const layoutStore = useLayoutStore()
const showBackToTop = ref(false)

const handleScroll = () => {
  showBackToTop.value = window.scrollY > 500
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
