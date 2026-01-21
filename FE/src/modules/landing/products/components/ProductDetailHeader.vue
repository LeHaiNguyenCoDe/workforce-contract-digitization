<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps<{
  product: any
  averageRating: number
  reviewCount: number
}>()
</script>

<template>
  <div class="product-header mb-4">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3 overflow-x-auto whitespace-nowrap">
      <RouterLink to="/" class="hover:text-red-600 flex items-center gap-1">
        <i class="ri-home-4-line"></i> {{ t('nav.home') }}
      </RouterLink>
      <span>/</span>
      <RouterLink v-if="product.category" :to="`/categories/${product.category.id}`" class="hover:text-red-600">
        {{ product.category.name }}
      </RouterLink>
      <span v-if="product.category">/</span>
      <span class="text-gray-900 font-medium">{{ product.name }}</span>
    </nav>

    <!-- Title & Rating -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2 flex items-center gap-2">
                {{ product.name }}
                <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Chính hãng</span>
            </h1>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-1 text-orange-400">
                    <span class="font-bold text-base">{{ averageRating || 5 }}</span>
                    <div class="flex">
                        <i v-for="i in 5" :key="i" 
                           class="ri-star-fill" 
                           :class="i <= (averageRating || 5) ? 'text-orange-400' : 'text-gray-300'"></i>
                    </div>
                    <span class="text-gray-500 underline ml-1">({{ reviewCount }} đánh giá)</span>
                </div>
                <button class="text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                    <i class="ri-add-circle-line"></i> So sánh
                </button>
            </div>
        </div>
        
        <!-- Social Actions -->
        <div class="flex gap-2">
            <button class="px-3 py-1.5 border hover:bg-gray-50 rounded-md text-sm text-blue-600 flex items-center gap-2 transition-colors">
                <i class="ri-thumb-up-line"></i> Thích
            </button>
            <button class="px-3 py-1.5 border hover:bg-gray-50 rounded-md text-sm text-blue-600 flex items-center gap-2 transition-colors">
                Chia sẻ
            </button>
        </div>
    </div>
  </div>
</template>
