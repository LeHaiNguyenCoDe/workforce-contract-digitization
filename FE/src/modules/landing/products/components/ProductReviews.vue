<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  reviews: any[]
  averageRating: number
  reviewCount: number
  product?: any
}>()

const emit = defineEmits(['submit-review', 'submit-reply'])

const reviewComment = ref('')
const reviewRating = ref(5)
const isSubmitting = ref(false)
const activeFilter = ref('all')

// Star Breakdown computed from actual reviews
const starCounts = computed(() => {
    return {
        5: props.reviews.filter(r => r.rating === 5).length,
        4: props.reviews.filter(r => r.rating === 4).length,
        3: props.reviews.filter(r => r.rating === 3).length,
        2: props.reviews.filter(r => r.rating === 2).length,
        1: props.reviews.filter(r => r.rating === 1).length,
    }
})

const totalReviews = computed(() => props.reviewCount || props.reviews.length)
const displayRating = computed(() => props.averageRating?.toFixed(1) || '5.0')

const getStarCount = (star: number) => starCounts.value[star as 1|2|3|4|5] || 0
const getStarPercentage = (star: number) => {
    if (totalReviews.value === 0) return '0%'
    return `${(getStarCount(star) / totalReviews.value) * 100}%`
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

const handleSubmit = () => {
    if (!reviewComment.value.trim()) return
    emit('submit-review', { rating: reviewRating.value, content: reviewComment.value })
    reviewComment.value = ''
    reviewRating.value = 5
}

const filters = [
    { key: 'all', label: 'Tất cả' },
    { key: 'has_image', label: 'Có hình ảnh' },
    { key: 'verified', label: 'Đã mua hàng' },
    { key: '5', label: '5 sao' },
    { key: '4', label: '4 sao' },
    { key: '3', label: '3 sao' },
]

// Mock reviews data for display
const mockReviews = [
    {
        name: 'Nguyễn Văn An',
        rating: 5,
        date: '1 tuần trước',
        content: 'Sản phẩm rất tốt, đóng gói cẩn thận, giao hàng nhanh. Rất hài lòng với chất lượng!',
        tags: ['Chất lượng tốt', 'Đóng gói cẩn thận', 'Giao hàng nhanh'],
        verified: true
    },
    {
        name: 'Trần Thị Mai',
        rating: 5,
        date: '2 tuần trước',
        content: 'Mua cho công ty, mọi người đều thích. Sẽ tiếp tục ủng hộ shop!',
        tags: ['Đáng tiền', 'Nhân viên nhiệt tình'],
        verified: true
    },
    {
        name: 'Lê Hoàng Phúc',
        rating: 4,
        date: '3 tuần trước',
        content: 'Sản phẩm ổn, đúng mô tả. Chỉ tiếc là ship hơi lâu một chút.',
        tags: ['Đúng mô tả'],
        verified: true
    },
]

const avatarColors = [
    'bg-gradient-to-br from-blue-500 to-indigo-600',
    'bg-gradient-to-br from-emerald-500 to-teal-600',
    'bg-gradient-to-br from-amber-500 to-orange-600',
]
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6 mb-6">
      <!-- Header -->
      <div class="flex items-center gap-3 mb-5">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
              <i class="ri-star-smile-line text-white text-lg"></i>
          </div>
          <div>
              <h3 class="text-lg font-bold text-gray-900">Đánh giá sản phẩm</h3>
              <p class="text-xs text-gray-500">Nhận xét từ khách hàng đã mua</p>
          </div>
      </div>
      
      <!-- Rating Overview -->
      <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-5 mb-6 border border-amber-100/50">
          <div class="flex flex-col md:flex-row gap-6">
              <!-- Left: Total Score -->
              <div class="md:w-1/3 flex flex-col items-center justify-center md:border-r border-amber-200/50 md:pr-6">
                   <div class="text-5xl font-extrabold bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent mb-1">
                       {{ displayRating }}<span class="text-2xl text-gray-400 font-normal">/5</span>
                   </div>
                   <div class="flex mb-2 text-amber-400 text-xl gap-0.5">
                        <i v-for="i in 5" :key="i" class="ri-star-fill"></i>
                   </div>
                   <div class="text-sm text-gray-600 font-medium">{{ totalReviews }} lượt đánh giá</div>
                   
                   <!-- Star Bars -->
                   <div class="w-full space-y-1.5 mt-4">
                        <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-2 text-xs">
                            <span class="w-3 font-medium text-gray-600">{{ star }}</span>
                            <i class="ri-star-fill text-amber-400 text-[10px]"></i>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                 <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 rounded-full transition-all duration-500" 
                                      :style="{ width: getStarPercentage(star) }"></div>
                            </div>
                            <span class="text-gray-500 w-6 text-right text-[10px]">{{ getStarCount(star) }}</span>
                        </div>
                   </div>
                   
                   <button class="w-full mt-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold py-2.5 rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all shadow-md hover:shadow-lg">
                       <i class="ri-edit-line mr-1"></i> Viết đánh giá
                   </button>
              </div>
              
              <!-- Right: Rating Categories -->
              <div class="md:w-2/3 flex flex-col justify-center">
                   <h4 class="font-bold text-gray-800 mb-4 text-sm">Đánh giá chi tiết</h4>
                   <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div v-for="category in ['Chất lượng', 'Đóng gói', 'Giao hàng', 'Dịch vụ']" :key="category" 
                             class="flex items-center justify-between bg-white/70 rounded-lg px-3 py-2.5 border border-gray-100">
                            <span class="text-sm text-gray-700">{{ category }}</span>
                            <div class="flex items-center gap-2">
                                <div class="flex text-amber-400 text-xs gap-0.5">
                                    <i v-for="i in 5" :key="i" class="ri-star-fill"></i>
                                </div>
                                <span class="text-amber-600 font-bold text-sm">5.0</span>
                            </div>
                        </div>
                   </div>
              </div>
          </div>
      </div>
      
      <!-- Filters -->
      <div class="mb-5">
           <div class="flex gap-2 flex-wrap">
               <button v-for="filter in filters" :key="filter.key"
                       @click="activeFilter = filter.key"
                       class="px-4 py-1.5 rounded-full text-xs font-medium transition-all"
                       :class="activeFilter === filter.key 
                           ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' 
                           : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                   {{ filter.label }}
               </button>
           </div>
      </div>
      
      <!-- Review List -->
      <div class="space-y-4">
           <div v-for="(review, i) in mockReviews" :key="i" 
                class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100/70 transition-colors">
               <div class="flex gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm"
                         :class="avatarColors[i % avatarColors.length]">
                        {{ review.name.charAt(0) }}
                    </div>
                    <div class="flex-1">
                         <div class="flex items-center gap-2 flex-wrap mb-1">
                              <span class="font-semibold text-gray-900 text-sm">{{ review.name }}</span>
                              <span v-if="review.verified" class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded font-medium">
                                  <i class="ri-checkbox-circle-fill"></i> Đã mua
                              </span>
                         </div>
                         <div class="flex items-center gap-2 mb-2">
                             <div class="flex text-amber-400 text-xs">
                                 <i v-for="j in review.rating" :key="j" class="ri-star-fill"></i>
                                 <i v-for="j in (5 - review.rating)" :key="'empty-'+j" class="ri-star-line text-gray-300"></i>
                             </div>
                             <span class="text-xs text-gray-400"><i class="ri-time-line"></i> {{ review.date }}</span>
                         </div>
                    </div>
               </div>
               
               <!-- Tags -->
               <div class="flex gap-2 mb-3 flex-wrap pl-13">
                   <span v-for="tag in review.tags" :key="tag" 
                         class="px-2 py-1 bg-white rounded-lg text-[11px] text-gray-600 border border-gray-200">
                       {{ tag }}
                   </span>
               </div>
               
               <div class="text-sm text-gray-700 leading-relaxed pl-13">
                    {{ review.content }}
               </div>
               
               <div class="flex gap-4 mt-3 pl-13">
                   <button class="text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                       <i class="ri-thumb-up-line"></i> Hữu ích
                   </button>
                   <button class="text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                       <i class="ri-reply-line"></i> Trả lời
                   </button>
               </div>
           </div>
      </div>
      
      <div class="mt-6 text-center">
            <button class="px-8 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 font-medium transition-all">
                Xem tất cả đánh giá <i class="ri-arrow-right-s-line"></i>
            </button>
      </div>
  </div>
</template>

<style scoped>
.pl-13 {
    padding-left: 52px;
}
</style>
