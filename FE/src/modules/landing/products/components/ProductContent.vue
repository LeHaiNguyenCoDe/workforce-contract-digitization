<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  product: any
}>()

const isExpanded = ref(false)
</script>

<template>
   <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase border-b pb-2">Đặc điểm nổi bật</h3>
        
        <div class="relative">
            <div v-if="product.description" 
                id="product-desc"
                class="prose prose-sm max-w-none text-gray-700 leading-relaxed overflow-hidden transition-all duration-300"
                :class="{'max-h-[300px]': !isExpanded}"
                v-html="product.description"></div>
                
            <div v-if="!product.description" class="text-center py-8 text-gray-500 italic">
                Đang cập nhật nội dung chi tiết...
            </div>

            <!-- Gradient Fade for collapsed state -->
            <div v-if="product.description && !isExpanded" 
                 class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white via-white/80 to-transparent">
            </div>
        </div>
        
        <!-- Toggle Button -->
        <div v-if="product.description" class="text-center mt-4 pt-2">
            <button @click="isExpanded = !isExpanded"
                    class="px-8 py-2 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 mx-auto">
                <span>{{ isExpanded ? 'Thu gọn' : 'Xem thêm' }}</span>
                <i class="ri-arrow-down-s-line transition-transform duration-300" :class="{'rotate-180': isExpanded}"></i>
            </button>
        </div>
   </div>
</template>
