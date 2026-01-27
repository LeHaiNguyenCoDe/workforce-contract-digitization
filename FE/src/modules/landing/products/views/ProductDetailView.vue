<script setup lang="ts">
/**
 * Product Detail View - Professional E-Commerce Layout
 * Modern design with gradient accents and clean UI
 */
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'
import ImageModal from '@/components/ImageModal.vue'
import { useLandingProducts } from '../composables/useLandingProducts'

// Components
import ProductDetailHeader from '../components/ProductDetailHeader.vue'
import ProductGallery from '../components/ProductGallery.vue'
import ProductInfo from '../components/ProductInfo.vue'
import ProductContent from '../components/ProductContent.vue'
import ProductReviews from '../components/ProductReviews.vue'
import RelatedProducts from '../components/RelatedProducts.vue'
import ProductPolicy from '../components/ProductPolicy.vue'
import ProductSpecs from '../components/ProductSpecs.vue'
import ProductQA from '../components/ProductQA.vue'
import ProductFAQ from '../components/ProductFAQ.vue'
import ProductFloatingBar from '../components/ProductFloatingBar.vue'

// ... existing imports ...

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { formatPrice } = useLandingProducts()

// Product state
const product = ref<any>(null)
const reviews = ref<any[]>([])
const similarProducts = ref<any[]>([])
const isLoading = ref(true)
const isAddingToCart = ref(false)

// Image Modal
const showImageModal = ref(false)
const modalImages = ref<string[]>([])
const modalInitialIndex = ref(0)

// Mock News Data for Widget
const productNews = [
    {
        title: 'Hướng dẫn chọn sản phẩm phù hợp với nhu cầu của bạn',
        time: '2 giờ trước',
        icon: 'ri-article-line',
        bgClass: 'bg-blue-500',
        isVideo: false
    },
    {
        title: 'So sánh các phiên bản mới nhất: Nên chọn loại nào?',
        time: '5 giờ trước',
        icon: 'ri-scales-3-line',
        bgClass: 'bg-blue-600',
        isVideo: false
    },
    {
        title: 'Mở hộp và trải nghiệm thực tế sản phẩm mới',
        time: '1 ngày trước',
        icon: 'ri-play-circle-line',
        bgClass: 'bg-gray-700',
        isVideo: true
    },
]

// Data fetching
const fetchProduct = async () => {
    isLoading.value = true
    const productId = route.params.id
    try {
        const response = await httpClient.get(`/products/${productId}`)
        const data = response.data as any
        product.value = data?.data || data

        // Fetch reviews
        try {
            const reviewsRes = await httpClient.get(`/products/${productId}/reviews`)
            const reviewsData = reviewsRes.data as any
            if (reviewsData?.data?.items) {
                reviews.value = reviewsData.data.items
            } else if (Array.isArray(reviewsData?.data)) {
                reviews.value = reviewsData.data
            } else {
                reviews.value = []
            }
        } catch {
            reviews.value = []
        }

        // Fetch similar products
        if (product.value?.category_id) {
            try {
                const similarRes = await httpClient.get('/products', {
                    params: { category_id: product.value.category_id, per_page: 5 }
                })
                const similarData = similarRes.data as any
                const items = similarData?.data?.items || similarData?.data?.data || []
                similarProducts.value = items.filter((p: any) => p.id !== product.value.id).slice(0, 5)
            } catch (err) {
                console.error('Error fetching similar products:', err)
                similarProducts.value = []
            }
        }
    } catch (error) {
        console.error('Failed to fetch product:', error)
    } finally {
        isLoading.value = false
    }
}

// Computed helpers
const allImages = computed(() => {
    if (!product.value) return []
    const imgs: string[] = []
    if (product.value.thumbnail) imgs.push(product.value.thumbnail)
    if (product.value.images?.length) {
        product.value.images.forEach((img: any) => {
            const url = img.url || img.image_url
            if (url && !imgs.includes(url)) {
                imgs.push(url)
            }
        })
    }
    return imgs
})

const averageRating = computed(() => {
    if (!reviews.value.length) return 0
    const sum = reviews.value.reduce((acc, r) => acc + (r.rating || 0), 0)
    return Math.round((sum / reviews.value.length) * 10) / 10
})

// Handlers
const handleOpenModal = (index: number, images: string[]) => {
    modalImages.value = images
    modalInitialIndex.value = index
    showImageModal.value = true
}

const handleAddToCart = async (payload: any) => {
    if (!product.value) return
    if (!authStore.isAuthenticated) {
        alert(t('common.loginRequired'))
        return
    }
    isAddingToCart.value = true
    try {
        await httpClient.post('/cart/items', {
            product_id: product.value.id,
            qty: 1
        })
        authStore.incrementCartCount(1)
        alert(t('cart.addedToCart'))
    } catch (error: any) {
        alert(error.response?.data?.message || t('cart.addToCartFailed'))
    } finally {
        isAddingToCart.value = false
    }
}

const handleBuyNow = async (payload: any) => {
    await handleAddToCart(payload)
    router.push('/cart')
}

const handleSubmitReview = async (payload: { rating: number, content: string }) => {
    if (!authStore.isAuthenticated) {
        alert(t('common.loginToReview'))
        return
    }
    try {
        await httpClient.post(`/products/${product.value.id}/reviews`, {
            rating: payload.rating,
            content: payload.content
        })
        alert(t('product.thankForReview'))
        fetchProduct()
    } catch (error: any) {
        alert(error.response?.data?.message || t('product.reviewFailed'))
    }
}

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(fetchProduct)
watch(() => route.params.id, fetchProduct)
</script>

<template>
    <div class="min-h-screen bg-white pb-8 relative">
        <div class="container py-4 max-w-[1240px] mx-auto">
            <!-- Loading -->
            <div v-if="isLoading" class="animate-pulse space-y-4">
                <div class="h-6 w-1/3 bg-gray-200 rounded"></div>
                <div class="grid md:grid-cols-12 gap-6">
                    <div class="md:col-span-7 h-[600px] bg-gray-200 rounded-xl"></div>
                    <div class="md:col-span-5 h-[600px] bg-gray-200 rounded-xl"></div>
                </div>
            </div>

            <div v-else-if="product">
                 <!-- Header -->
                 <ProductDetailHeader 
                    :product="product" 
                    :averageRating="averageRating" 
                    :reviewCount="reviews.length" 
                 />

                 <!-- Main Content Grid -->
                 <div class="space-y-8">
                     <!-- TOP SECTION: Gallery & Info -->
                     <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
                          <!-- LEFT: Gallery & Policy & Specs & FAQ -->
                          <div class="lg:col-span-7 xl:col-span-7 space-y-6">
                               <ProductGallery 
                                    :images="allImages" 
                                    :thumbnail="product.thumbnail" 
                                    :productName="product.name"
                                    @open-modal="handleOpenModal"
                               />
                               <ProductPolicy />
                               <ProductSpecs :specs="product.specs" />
                               <ProductFAQ />
                          </div>
                          
                          <!-- RIGHT: Info (Rich) -->
                          <div class="lg:col-span-5 xl:col-span-5">
                               <ProductInfo 
                                    :product="product"
                                    :formatPrice="formatPrice"
                                    :isAddingToCart="isAddingToCart"
                                    @add-to-cart="handleAddToCart"
                                    @buy-now="handleBuyNow"
                               />
                          </div>
                     </div>

                     <!-- MIDDLE SECTION: Related Products -->
                     <RelatedProducts 
                         :products="similarProducts" 
                         :formatPrice="formatPrice" 
                     />

                     <!-- BOTTOM SECTION: Content vs News -->
                     <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                          <!-- LEFT: Description -->
                          <div class="lg:col-span-7 xl:col-span-7 space-y-6">
                               <ProductContent :product="product" />
                          </div>
                          
                          <!-- RIGHT: News / Sticky -->
                          <div class="lg:col-span-5 xl:col-span-5 sticky top-24">
                               <!-- News / Highlights Widget -->
                               <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="bg-blue-600 px-4 py-3 flex justify-between items-center">
                                        <span class="text-white font-bold text-sm flex items-center gap-2">
                                            <i class="ri-newspaper-line"></i> Tin tức nổi bật
                                        </span>
                                        <a href="#" class="text-xs text-white/80 hover:text-white transition-colors">Xem tất cả →</a>
                                    </div>
                                    <div class="p-4 space-y-3">
                                         <div v-for="(news, idx) in productNews" :key="idx" class="flex gap-3 group cursor-pointer hover:bg-gray-50 rounded-lg p-2 -mx-2 transition-colors">
                                             <div class="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                                                  :class="news.bgClass">
                                                  <i :class="news.icon" class="text-2xl text-white/90"></i>
                                             </div>
                                             <div class="flex-1 min-w-0">
                                                 <div class="text-xs font-semibold text-gray-800 line-clamp-2 group-hover:text-blue-600 transition-colors mb-1">
                                                     {{ news.title }}
                                                 </div>
                                                 <div class="text-[10px] text-gray-400 flex items-center gap-2">
                                                     <span><i class="ri-time-line"></i> {{ news.time }}</span>
                                                     <span v-if="news.isVideo" class="bg-red-100 text-red-600 px-1.5 rounded">Video</span>
                                                 </div>
                                             </div>
                                         </div>
                                    </div>
                               </div>
                               
                               <!-- Quick Support -->
                               <div class="mt-4 bg-blue-50 rounded-xl border border-blue-100 p-4">
                                   <div class="flex items-center gap-3 mb-3">
                                       <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                                           <i class="ri-customer-service-2-line text-white text-lg"></i>
                                       </div>
                                       <div>
                                           <div class="font-bold text-gray-800 text-sm">Hỗ trợ 24/7</div>
                                           <div class="text-xs text-gray-500">Sẵn sàng giải đáp mọi thắc mắc</div>
                                       </div>
                                   </div>
                                   <div class="grid grid-cols-2 gap-2">
                                       <button class="bg-white border border-gray-200 rounded-lg py-2 px-3 text-xs font-medium text-gray-700 hover:border-blue-500 hover:text-blue-600 transition-all flex items-center justify-center gap-1">
                                           <i class="ri-phone-line"></i> Gọi ngay
                                       </button>
                                       <button class="bg-white border border-gray-200 rounded-lg py-2 px-3 text-xs font-medium text-gray-700 hover:border-blue-500 hover:text-blue-600 transition-all flex items-center justify-center gap-1">
                                           <i class="ri-chat-3-line"></i> Chat trực tiếp
                                       </button>
                                   </div>
                               </div>
                          </div>
                     </div>

                     <!-- FULL WIDTH SECTION: Reviews & QA -->
                     <ProductReviews 
                          :product="product"
                          :reviews="reviews" 
                          :averageRating="averageRating" 
                          :reviewCount="reviews.length"
                          @submit-review="handleSubmitReview"
                     />
                     <ProductQA />
                 </div>
            </div>
            
            <!-- Not Found -->
            <div v-else class="text-center py-20">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ t('product.notExist') }}</h2>
                <button @click="router.push('/products')" class="text-red-600 hover:underline">
                    {{ t('product.backToStore') }}
                </button>
            </div>
        </div>

        <!-- Image Modal -->
        <ImageModal v-model="showImageModal" :images="modalImages" :currentIndex="modalInitialIndex" />
        
        <!-- Floating Buttons -->
        <div class="fixed bottom-24 right-4 z-50 flex flex-col gap-2">
            <button @click="scrollToTop" 
                    class="bg-gray-800 text-white w-10 h-10 rounded shadow-lg flex items-center justify-center hover:bg-gray-700 transition-colors opacity-80 hover:opacity-100">
                <i class="ri-arrow-up-line"></i>
                <div class="absolute right-full mr-2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 hover:opacity-100 whitespace-nowrap pointer-events-none">Lên đầu</div>
            </button>
             <button class="bg-red-600 text-white w-10 h-10 rounded shadow-lg flex items-center justify-center hover:bg-red-700 transition-colors animate-bounce-slow">
                <i class="ri-headphone-line text-lg"></i>
                <div class="absolute right-full mr-2 bg-red-600 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Liên hệ</div>
            </button>
        </div>
        
        <!-- Sticky Action Bar -->
        <ProductFloatingBar v-if="product" :product="product" :formatPrice="formatPrice" />
    </div>
</template>

<style scoped>
.animate-bounce-slow {
  animation: bounce 3s infinite;
}
@keyframes bounce {
  0%, 100% {
    transform: translateY(-5%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: translateY(0);
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}
</style>
