<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  product: any
  formatPrice: (price: number) => string
  isAddingToCart: boolean
}>()

const emit = defineEmits(['add-to-cart', 'buy-now'])

const selectedColor = ref(0)
const selectedVersion = ref(0)
const selectedRegion = ref('hcm')

// Mock versions
const versions = computed(() => [
    { id: 1, name: '128GB', price: props.product.price },
    { id: 2, name: '256GB', price: Math.round(props.product.price * 1.2) },
    { id: 3, name: '512GB', price: Math.round(props.product.price * 1.5) },
])

// Mock colors
const colors = computed(() => [
    { id: 1, name: 'Natural Titanium', hex: '#d4c5b3' },
    { id: 2, name: 'Blue Titanium', hex: '#4b5563' },
    { id: 3, name: 'White Titanium', hex: '#f3f4f6' },
    { id: 4, name: 'Black Titanium', hex: '#1f2937' },
])

const currentPrice = computed(() => {
    return versions.value[selectedVersion.value]?.price || props.product.price
})

const oldPrice = computed(() => {
    return Math.round(currentPrice.value * 1.15)
})

const handleAddToCart = () => {
    emit('add-to-cart', {
        version: versions.value[selectedVersion.value],
        color: colors.value[selectedColor.value]
    })
}

const handleBuyNow = () => {
    emit('buy-now', {
         version: versions.value[selectedVersion.value],
         color: colors.value[selectedColor.value]
    })
}

const promotions = [
    'Giảm thêm 10% khi mua kèm phụ kiện chính hãng',
    'Tặng gói bảo hành VIP 1 đổi 1 trong 12 tháng',
    'Thu cũ đổi mới trợ giá lên đến 2 triệu',
]

const paymentOffers = [
    'Giảm đến 500K thanh toán Kredivo',
    'Hoàn tiền 2 triệu mở thẻ HSBC',
    'Giảm 500K thẻ Sacombank',
]
</script>

<template>
  <div class="product-info space-y-4">
    <!-- Price Section -->
    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
        <div class="flex items-end gap-3 flex-wrap mb-2">
            <span class="text-3xl font-bold text-red-600">{{ formatPrice(currentPrice) }}</span>
            <span class="text-lg text-gray-400 line-through mb-0.5">{{ formatPrice(oldPrice) }}</span>
            <span class="bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full ml-auto">-15%</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <i class="ri-checkbox-circle-fill text-green-500"></i>
            <span>Giá đã bao gồm VAT</span>
        </div>
    </div>

    <!-- Trade-in Box -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex justify-between items-center group cursor-pointer hover:bg-blue-100 transition-colors">
         <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="ri-arrow-up-circle-line text-xl text-white"></i>
            </div>
            <div>
               <div class="text-sm font-medium text-gray-700">Thu cũ lên đời chỉ từ</div>
               <div class="text-lg font-bold text-blue-600">{{ formatPrice(currentPrice * 0.8) }}</div>
            </div>
         </div>
         <div class="text-blue-600 text-sm font-medium flex items-center gap-1">
             Chi tiết <i class="ri-arrow-right-s-line group-hover:translate-x-1 transition-transform"></i>
         </div>
    </div>

    <!-- Versions (Storage) -->
    <div>
        <div class="text-sm font-medium mb-2 flex justify-between items-center">
            <span>Dung lượng</span>
            <span class="text-blue-600 cursor-pointer text-xs hover:underline">So sánh chi tiết</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <button v-for="(v, index) in versions" :key="v.id"
                    @click="selectedVersion = index"
                    class="border-2 rounded-lg p-2.5 text-center transition-all relative overflow-hidden"
                    :class="selectedVersion === index 
                        ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium' 
                        : 'border-gray-200 hover:border-blue-300 text-gray-700'">
                <div class="text-sm font-semibold">{{ v.name }}</div>
                <div class="text-xs mt-0.5" :class="selectedVersion === index ? 'text-blue-600' : 'text-gray-500'">
                    {{ formatPrice(v.price) }}
                </div>
                <div v-if="selectedVersion === index" class="absolute top-0 right-0 w-4 h-4 bg-blue-500 flex items-center justify-center rounded-bl">
                    <i class="ri-check-line text-white text-[10px]"></i>
                </div>
            </button>
        </div>
    </div>

    <!-- Colors -->
    <div>
        <div class="text-sm font-medium mb-2">
            Màu sắc: <span class="text-gray-600">{{ colors[selectedColor].name }}</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <button v-for="(c, index) in colors" :key="c.id"
                    @click="selectedColor = index"
                    class="border-2 rounded-lg p-2 flex items-center gap-3 transition-all relative overflow-hidden"
                    :class="selectedColor === index 
                        ? 'border-blue-500 bg-white' 
                        : 'border-gray-200 hover:border-blue-300'">
                <div class="w-10 h-10 rounded-lg shadow-inner border border-gray-200 flex-shrink-0" 
                     :style="{ backgroundColor: c.hex }"></div>
                <div class="flex flex-col text-left">
                    <span class="text-xs font-semibold text-gray-900">{{ c.name }}</span>
                    <span class="text-[10px] text-gray-500">{{ formatPrice(currentPrice) }}</span>
                </div>
                <div v-if="selectedColor === index" class="absolute top-0 right-0 w-4 h-4 bg-blue-500 flex items-center justify-center rounded-bl">
                    <i class="ri-check-line text-white text-[10px]"></i>
                </div>
            </button>
        </div>
    </div>

    <!-- Location Selector -->
    <div class="border border-gray-200 rounded-lg p-3">
        <div class="flex items-center gap-3 mb-2">
             <i class="ri-map-pin-2-fill text-blue-600"></i>
             <div class="flex-1">
                 <div class="text-xs text-gray-500">Xem giá tại</div>
                 <select v-model="selectedRegion" class="bg-transparent border-none text-sm font-semibold text-gray-800 focus:ring-0 w-full cursor-pointer p-0">
                     <option value="hcm">Hồ Chí Minh</option>
                     <option value="hn">Hà Nội</option>
                     <option value="dn">Đà Nẵng</option>
                 </select>
             </div>
        </div>
        <div class="text-xs text-green-600 flex items-center gap-1">
             <i class="ri-check-double-line"></i> Có <b class="mx-0.5">45</b> cửa hàng còn hàng
        </div>
    </div>

    <!-- Promotion Box -->
    <div class="border border-red-200 rounded-lg overflow-hidden">
        <div class="bg-red-600 text-white px-4 py-2 font-bold text-sm flex items-center gap-2">
            <i class="ri-gift-fill"></i> Khuyến mãi
        </div>
        <div class="p-3 space-y-2 bg-red-50">
            <div v-for="(promo, index) in promotions" :key="index" class="flex gap-2 text-sm text-gray-700">
                <span class="w-5 h-5 rounded-full bg-red-600 text-white flex items-center justify-center text-xs flex-shrink-0">
                    {{ index + 1 }}
                </span>
                <span>{{ promo }}</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-2">
        <button @click="handleBuyNow" 
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-lg shadow-sm transition-all flex flex-col items-center justify-center">
            <span class="text-lg uppercase">Mua Ngay</span>
            <span class="text-xs font-normal opacity-90 mt-0.5">Giao nhanh 2 giờ hoặc nhận tại cửa hàng</span>
        </button>
        
        <div class="grid grid-cols-2 gap-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-sm flex flex-col items-center justify-center">
                <span>Trả góp 0%</span>
                <span class="text-[10px] font-normal opacity-80">Duyệt nhanh qua ĐT</span>
            </button>
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-sm flex flex-col items-center justify-center">
                <span>Trả góp thẻ</span>
                <span class="text-[10px] font-normal opacity-80">Visa, Mastercard</span>
            </button>
        </div>
    </div>
    
    <!-- Add to Cart -->
    <button @click="handleAddToCart" :disabled="isAddingToCart"
            class="w-full border-2 border-blue-600 text-blue-600 font-bold py-3 rounded-lg hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
        <i v-if="!isAddingToCart" class="ri-shopping-cart-2-line text-xl"></i>
        <i v-else class="ri-loader-4-line animate-spin text-xl"></i>
        {{ isAddingToCart ? 'Đang thêm...' : 'Thêm vào giỏ hàng' }}
    </button>

    <!-- Payment Offers -->
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b font-medium text-gray-800 text-sm flex items-center gap-2">
            <i class="ri-bank-card-line text-blue-600"></i> Ưu đãi thanh toán
        </div>
        <div class="p-3 space-y-2">
            <div v-for="(offer, index) in paymentOffers" :key="index" class="flex gap-2 text-sm text-gray-700 items-center">
                <i class="ri-check-line text-green-500"></i>
                <span>{{ offer }}</span>
            </div>
        </div>
    </div>

    <!-- Warranty Packages -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="text-sm font-medium text-gray-800 mb-3 flex items-center gap-2">
            <i class="ri-shield-check-fill text-blue-600"></i> Gói bảo hành mở rộng
        </div>
        <div class="grid grid-cols-2 gap-2">
             <div v-for="pkg in ['1 đổi 1 VIP 12T', 'Rơi vỡ - Nước', 'S24+ 12 tháng', 'Mở rộng 24T']" :key="pkg"
                  class="bg-white border border-gray-200 hover:border-blue-400 cursor-pointer rounded-lg p-3 transition-all">
                  <div class="text-xs font-medium text-gray-800 mb-1">{{ pkg }}</div>
                  <div class="text-blue-600 font-bold text-sm">{{ formatPrice(1200000 + Math.random() * 600000) }}</div>
             </div>
        </div>
    </div>
  </div>
</template>
