<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  images: string[]
  thumbnail: string | null
  productName: string
}>()

const emit = defineEmits(['open-modal'])

const selectedIndex = ref(0)
const activeImages = computed(() => {
    const imgs = props.thumbnail ? [props.thumbnail] : []
    const otherImgs = props.images || []
    return [...new Set([...imgs, ...otherImgs])]
})

const handleImageClick = () => {
    emit('open-modal', selectedIndex.value, activeImages.value)
}

const features = [
    { icon: 'ri-vidicon-line', label: 'Video trên tay' },
    { icon: 'ri-box-3-line', label: 'Trong hộp có gì' },
    { icon: 'ri-360-line', label: 'Xoay 360°' },
    { icon: 'ri-camera-lens-line', label: 'Ảnh thực tế' },
]

const warrantyItems = [
    { icon: 'ri-shield-check-line', text: 'Bảo hành chính hãng 12 tháng' },
    { icon: 'ri-refresh-line', text: '1 đổi 1 trong 30 ngày nếu lỗi' },
    { icon: 'ri-truck-line', text: 'Miễn phí giao hàng toàn quốc' },
]
</script>

<template>
  <div class="product-gallery">
    <!-- Main Image -->
    <div class="relative aspect-[4/3] bg-gray-50 rounded-2xl border border-gray-200 mb-4 overflow-hidden group cursor-zoom-in"
         @click="handleImageClick">
        <!-- Badges -->
        <div class="absolute top-3 left-3 z-10 flex gap-2">
           <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
               Giảm 15%
           </span>
        </div>
        
        <!-- Favorite button -->
        <button class="absolute top-3 right-3 z-10 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm border border-gray-100">
            <i class="ri-heart-line text-gray-500 text-xl hover:text-red-500"></i>
        </button>
        
        <img v-if="activeImages[selectedIndex]" 
             :src="activeImages[selectedIndex]" 
             :alt="productName" 
             class="w-full h-full object-contain p-6 group-hover:scale-105 transition-transform duration-500 ease-out" />
        
        <!-- Placeholder when no image -->
        <div v-else class="w-full h-full flex items-center justify-center">
            <div class="text-center">
                <i class="ri-image-line text-6xl text-gray-300"></i>
                <p class="text-gray-400 text-sm mt-2">Hình ảnh sản phẩm</p>
            </div>
        </div>
             
        <!-- Navigation Arrows -->
        <button v-if="activeImages.length > 1"
                @click.stop="selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : activeImages.length - 1"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white text-gray-700 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-md hover:bg-gray-50">
            <i class="ri-arrow-left-s-line text-xl"></i>
        </button>
        <button v-if="activeImages.length > 1"
                @click.stop="selectedIndex = selectedIndex < activeImages.length - 1 ? selectedIndex + 1 : 0"
                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white text-gray-700 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-md hover:bg-gray-50">
            <i class="ri-arrow-right-s-line text-xl"></i>
        </button>
        
        <!-- Zoom hint -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-white text-xs px-4 py-1.5 rounded-full pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5">
            <i class="ri-zoom-in-line"></i> Click để phóng to
        </div>
        
        <!-- Image counter -->
        <div v-if="activeImages.length > 1" class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full">
            {{ selectedIndex + 1 }} / {{ activeImages.length }}
        </div>
    </div>

    <!-- Features / Quick Actions -->
    <div class="grid grid-cols-4 gap-2 mb-4">
        <div v-for="(feature, index) in features" :key="index"
             class="text-center p-2.5 rounded-xl border border-gray-200 hover:border-blue-500 cursor-pointer transition-all bg-white hover:bg-blue-50 group">
             <i :class="feature.icon" class="text-xl text-gray-500 group-hover:text-blue-600 block mb-1"></i>
             <span class="text-[10px] font-medium text-gray-600 group-hover:text-blue-600 block">{{ feature.label }}</span>
        </div>
    </div>

    <!-- Thumbnails -->
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button v-for="(img, index) in activeImages" :key="index"
                @click="selectedIndex = index"
                class="w-16 h-16 rounded-xl border-2 overflow-hidden flex-shrink-0 transition-all bg-white"
                :class="selectedIndex === index ? 'border-blue-500 shadow-sm' : 'border-gray-200 hover:border-blue-300'">
            <img :src="img" :alt="`${productName} ${index}`" class="w-full h-full object-contain p-1" />
        </button>
        
        <!-- Placeholder thumbnails -->
        <div v-if="activeImages.length === 0" v-for="i in 4" :key="'placeholder-'+i"
             class="w-16 h-16 rounded-xl border border-gray-200 overflow-hidden flex-shrink-0 bg-gray-50 flex items-center justify-center">
            <i class="ri-image-line text-gray-300"></i>
        </div>
    </div>
    
    <!-- Warranty Info Box -->
    <div class="mt-5 bg-blue-50 rounded-2xl overflow-hidden border border-blue-100">
        <div class="bg-blue-600 px-4 py-2.5 flex items-center gap-2">
            <i class="ri-verified-badge-fill text-white"></i>
            <span class="font-bold text-white text-sm">Cam kết & Bảo hành</span>
        </div>
        <div class="p-4 space-y-3">
             <div v-for="(item, index) in warrantyItems" :key="index" class="flex gap-3 items-start">
                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0">
                    <i :class="item.icon" class="text-blue-600 text-lg"></i>
                </div>
                <span class="text-sm text-gray-700 leading-relaxed">{{ item.text }}</span>
             </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
