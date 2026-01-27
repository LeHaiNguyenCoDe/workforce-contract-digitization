<template>
  <Teleport to="body">
    <!-- Backdrop Overlay -->
    <div 
      class="mkp-side-overlay"
      :class="{ 'is-visible': layoutStore.isMarketplaceCartOpen }"
      @click="layoutStore.isMarketplaceCartOpen = false"
    ></div>

    <!-- Cart Sidebar Content -->
    <aside 
      class="mkp-right-sidebar cart-sidebar"
      :class="{ 'is-open': layoutStore.isMarketplaceCartOpen }"
    >
      <div class="sidebar-top">
        <h2 class="title text-[22px] font-bold text-slate-800">Shopping cart</h2>
        <button 
          @click="layoutStore.isMarketplaceCartOpen = false"
          class="close-btn flex items-center gap-1.5 text-[12px] font-bold uppercase tracking-wider text-slate-400 hover:text-red-500 transition-colors"
        >
          <i class="fas fa-times text-[14px]"></i>
          <span>Close</span>
        </button>
      </div>

      <div class="sidebar-body h-[calc(100%-80px)] flex flex-col">
        <!-- Empty State -->
        <div v-if="cartItems.length === 0" class="flex-grow flex flex-col items-center justify-center p-8">
          <div class="w-[120px] h-[120px] mb-6 opacity-10">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 3H5L5.4 5M5.4 5H21L17 13H7M5.4 5L7 13M7 13L4.707 15.293C4.077 15.923 4.523 17 5.414 17H17M17 17C15.8954 17 15 17.8954 15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17ZM9 17C7.89543 17 7 17.8954 7 19C7 20.1046 7.89543 21 9 21C10.1046 21 11 20.1046 11 19C11 17.8954 10.1046 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="10" y1="8" x2="16" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <line x1="16" y1="8" x2="10" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="text-[18px] font-bold text-slate-700 mb-8">No products in the cart.</p>
          <button 
            @click="layoutStore.isMarketplaceCartOpen = false"
            class="bg-primary text-white px-8 py-3 rounded-md font-bold text-[14px] hover:bg-primary-dark transition-colors shadow-sm"
          >
            Return To Shop
          </button>
        </div>

        <!-- Non-Empty State -->
        <template v-else>
          <div class="cart-items flex-grow overflow-y-auto p-6 space-y-6">
            <div v-for="item in cartItems" :key="item.id" class="cart-item flex gap-4 relative group">
              <div class="w-[80px] h-[80px] shrink-0 border border-slate-100 rounded-lg overflow-hidden p-2">
                <img :src="item.image" :alt="item.name" class="w-full h-full object-contain" />
              </div>
              <div class="flex-grow">
                <h4 class="text-[14px] font-bold text-slate-800 leading-snug mb-1 hover:text-primary transition-colors cursor-pointer">
                  {{ item.name }}
                </h4>
                <div class="text-[11px] font-bold text-slate-400 mb-3">SKU: {{ item.sku }}</div>
                
                <div class="flex items-center justify-between">
                  <div class="quantity-control flex items-center border border-slate-200 rounded-md">
                    <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-primary transition-colors">-</button>
                    <span class="w-10 text-center text-[13px] font-bold">{{ item.quantity }}</span>
                    <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-primary transition-colors">+</button>
                  </div>
                  <div class="text-[14px] font-bold text-primary">
                    {{ item.quantity }} × {{ formatCurrency(item.price) }}
                  </div>
                </div>
              </div>
              <button class="absolute -top-1 right-0 text-slate-300 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-[10px]"></i>
              </button>
            </div>
          </div>

          <!-- Bottom Summary -->
          <div class="cart-footer p-6 bg-slate-50/50 border-t border-slate-100 space-y-6">
            <div class="subtotal flex items-center justify-between">
              <span class="text-[20px] font-bold text-slate-800">Subtotal:</span>
              <span class="text-[20px] font-bold text-primary">{{ formatCurrency(subtotal) }}</span>
            </div>

            <div class="shipping-info">
              <div class="flex items-center justify-between mb-2">
                <p class="text-[13px] font-bold text-slate-500">
                  Add <span class="text-primary">{{ formatCurrency(3470) }}</span> to cart and get free shipping!
                </p>
              </div>
              <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-primary" :style="{ width: '15%' }"></div>
              </div>
            </div>

            <div class="actions flex flex-col gap-3">
              <button class="w-full bg-[#f1f6ff] text-primary h-[50px] rounded-md font-bold text-[14px] hover:bg-blue-100 transition-colors">
                View Cart
              </button>
              <button class="w-full bg-primary text-white h-[50px] rounded-md font-bold text-[14px] hover:bg-primary-dark transition-colors shadow-sm">
                Checkout
              </button>
            </div>
          </div>
        </template>
      </div>
    </aside>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLayoutStore } from '@/stores/layout'

const layoutStore = useLayoutStore()

// Mock Cart Items
const cartItems = [
  {
    id: 1,
    name: 'Acer SA100 SATAIII',
    sku: '5334126',
    price: 30,
    quantity: 1,
    image: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&q=80'
  }
]

const subtotal = computed(() => {
  return cartItems.reduce((acc, item) => acc + (item.price * item.quantity), 0)
})

const formatCurrency = (val: number) => {
  return val.toString() + ' ₫'
}
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
