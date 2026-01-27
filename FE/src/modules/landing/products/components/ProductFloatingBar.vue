<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

defineProps<{
  product: any
  formatPrice: (price: number) => string
}>()

const isVisible = ref(false)

const handleScroll = () => {
    // Show after scrolling down 600px
    isVisible.value = window.scrollY > 600
}

onMounted(() => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))
</script>

<template>
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] py-2 px-4 z-40 transition-transform duration-300 transform"
       :class="isVisible ? 'translate-y-0' : 'translate-y-full'">
       <div class="container max-w-[1240px] mx-auto flex items-center justify-between gap-4">
           <!-- Product Info -->
           <div class="flex items-center gap-3">
                <div class="w-10 h-10 border rounded overflow-hidden">
                    <img :src="product?.thumbnail" class="w-full h-full object-contain" />
                </div>
                <div class="hidden md:block">
                     <div class="text-sm font-bold text-gray-900 line-clamp-1">{{ product?.name }}</div>
                     <div class="text-xs text-gray-500">Chính hãng - Cam Vũ Trụ</div>
                </div>
           </div>
           
           <!-- Actions -->
           <div class="flex items-center gap-2">
               <div class="text-right mr-2 hidden md:block">
                    <div class="text-red-600 font-bold text-sm">{{ formatPrice(product?.price || 0) }}</div>
                    <div class="text-[10px] text-gray-400 line-through">{{ formatPrice((product?.price || 0) * 1.1) }}</div>
               </div>
               
               <button class="border border-blue-600 text-blue-600 px-3 py-1.5 rounded text-xs font-bold hover:bg-blue-50 whitespace-nowrap hidden sm:block">
                   Trả góp 0%
               </button>
               
               <button class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-1.5 rounded text-sm whitespace-nowrap">
                   Mua Ngay
               </button>
               
               <button class="border border-red-600 text-red-600 px-3 py-1.5 rounded hover:bg-red-50">
                   <i class="ri-shopping-cart-2-line"></i>
               </button>
           </div>
       </div>
  </div>
</template>
