<script setup lang="ts">
import { RouterLink } from 'vue-router'

defineProps<{
  products: any[]
  formatPrice: (price: number) => string
}>()
</script>

<template>
  <div v-if="products.length" class="bg-white rounded-xl border shadow-sm p-4 md:p-6 mb-6">
        <div class="flex items-center gap-6 mb-4 border-b">
             <h3 class="text-lg font-bold text-red-600 border-b-2 border-red-600 pb-2 cursor-pointer uppercase">Sản phẩm tương tự</h3>
             <h3 class="text-lg font-medium text-gray-500 pb-2 cursor-pointer hover:text-red-600 transition-colors uppercase">Tham khảo hàng cũ</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
             <RouterLink v-for="(product, index) in products" :key="product.id" :to="`/products/${product.id}`"
                         class="border rounded-lg p-2 hover:shadow-md transition-shadow group bg-white">
                  <div class="aspect-square mb-2 relative overflow-hidden rounded-md bg-gray-50">
                        <img :src="product.thumbnail || product.images?.[0]?.image_url" 
                             :alt="product.name"
                             class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300" />
                        <!-- Sale Badge -->
                        <div v-if="index < 2" class="absolute top-1 left-1 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm">-15%</div>
                  </div>
                  <h4 class="text-xs font-semibold text-gray-800 line-clamp-2 mb-1 min-h-[2.5em] group-hover:text-red-600 transition-colors">
                      {{ product.name }}
                  </h4>
                  <div class="flex items-end gap-1 flex-wrap">
                      <span class="text-red-600 font-bold text-sm">{{ formatPrice(product.price) }}</span>
                      <span class="text-gray-400 text-[10px] line-through">{{ formatPrice(product.price * 1.15) }}</span>
                  </div>
                  
                  <div class="mt-2 flex items-center gap-1 text-[10px] text-gray-500">
                      <i class="ri-star-fill text-orange-400"></i>
                      <span>4.9 (15)</span>
                  </div>
             </RouterLink>
        </div>
  </div>
</template>
