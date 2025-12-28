<script setup lang="ts">
/**
 * Product Detail View
 * Uses useProducts composable for product details and management
 */
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import { useProducts } from '../composables/useProducts'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()
const route = useRoute()
const authStore = useAuthStore()

// Use composable
const { formatPrice } = useProducts()

const product = ref<any>(null)
const reviews = ref<any[]>([])
const isLoading = ref(true)
const quantity = ref(1)
const isAddingToCart = ref(false)
const selectedImage = ref(0)

// Review form
const showReviewForm = ref(false)
const reviewRating = ref(5)
const reviewComment = ref('')
const isSubmittingReview = ref(false)

// Get all product images including thumbnail
const allImages = computed(() => {
    if (!product.value) return []
    const imgs: string[] = []
    if (product.value.thumbnail) imgs.push(product.value.thumbnail)
    if (product.value.images?.length) {
        product.value.images.forEach((img: any) => {
            if (img.image_url && !imgs.includes(img.image_url)) {
                imgs.push(img.image_url)
            }
        })
    }
    return imgs
})

const fetchProduct = async () => {
    isLoading.value = true
    const productId = route.params.id
    try {
        const response = await httpClient.get(`/frontend/products/${productId}`)
        const data = response.data as any
        product.value = data?.data || data

        // Fetch reviews
        try {
            const reviewsRes = await httpClient.get(`/frontend/products/${productId}/reviews`)
            const reviewsData = reviewsRes.data as any
            if (reviewsData?.data?.data) reviews.value = reviewsData.data.data
            else if (Array.isArray(reviewsData?.data)) reviews.value = reviewsData.data
            else reviews.value = product.value?.reviews || []
        } catch {
            reviews.value = product.value?.reviews || []
        }
    } catch (error) {
        console.error('Failed to fetch product:', error)
    } finally {
        isLoading.value = false
    }
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

const addToCart = async () => {
    if (!product.value) return

    if (!authStore.isAuthenticated) {
        alert('Vui lòng đăng nhập để thêm vào giỏ hàng!')
        return
    }

    isAddingToCart.value = true
    try {
        await httpClient.post('/frontend/cart/items', {
            product_id: product.value.id,
            qty: quantity.value
        })
        authStore.incrementCartCount(quantity.value)
        alert('Đã thêm vào giỏ hàng!')
    } catch (error: any) {
        console.error('Failed to add to cart:', error)
        const message = error.response?.data?.message || 'Thêm vào giỏ hàng thất bại!'
        alert(message)
    } finally {
        isAddingToCart.value = false
    }
}

const submitReview = async () => {
    if (!product.value || !reviewComment.value.trim()) return
    if (!authStore.isAuthenticated) {
        alert('Vui lòng đăng nhập để đánh giá!')
        return
    }

    isSubmittingReview.value = true
    try {
        await httpClient.post(`/frontend/products/${product.value.id}/reviews`, {
            rating: reviewRating.value,
            content: reviewComment.value
        })
        alert('Cảm ơn bạn đã đánh giá!')
        reviewComment.value = ''
        reviewRating.value = 5
        showReviewForm.value = false
        // Refetch reviews
        const reviewsRes = await httpClient.get(`/frontend/products/${product.value.id}/reviews`)
        const reviewsData = reviewsRes.data as any
        if (reviewsData?.data?.data) reviews.value = reviewsData.data.data
        else if (Array.isArray(reviewsData?.data)) reviews.value = reviewsData.data
    } catch (error: any) {
        alert(error.response?.data?.message || 'Gửi đánh giá thất bại!')
    } finally {
        isSubmittingReview.value = false
    }
}

onMounted(fetchProduct)
</script>

<template>
    <div class="min-h-screen">
        <div class="container py-8">
            <!-- Loading -->
            <div v-if="isLoading" class="animate-pulse">
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="aspect-square bg-dark-700 rounded-2xl"></div>
                    <div class="space-y-4">
                        <div class="h-10 bg-dark-700 rounded-lg w-3/4"></div>
                        <div class="h-6 bg-dark-700 rounded-lg w-1/2"></div>
                        <div class="h-32 bg-dark-700 rounded-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Product -->
            <div v-else-if="product">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
                    <RouterLink to="/" class="hover:text-white">{{ t('nav.home') }}</RouterLink>
                    <span>/</span>
                    <RouterLink to="/products" class="hover:text-white">{{ t('nav.products') }}</RouterLink>
                    <span v-if="product.category">/</span>
                    <RouterLink v-if="product.category" :to="`/categories/${product.category.id}`"
                        class="hover:text-white">{{ product.category.name }}</RouterLink>
                    <span>/</span>
                    <span class="text-white line-clamp-1">{{ product.name }}</span>
                </nav>

                <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Images -->
                    <div>
                        <div class="aspect-square bg-dark-800 rounded-2xl overflow-hidden mb-4 border border-white/5">
                            <img v-if="allImages[selectedImage]" :src="allImages[selectedImage]" :alt="product.name"
                                class="w-full h-full object-contain" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1">
                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>
                        </div>
                        <!-- Thumbnails -->
                        <div v-if="allImages.length > 1" class="flex gap-3 flex-wrap">
                            <button v-for="(img, index) in allImages" :key="index" @click="selectedImage = index"
                                class="w-20 h-20 rounded-lg overflow-hidden border-2 transition-all flex-shrink-0"
                                :class="selectedImage === index ? 'border-primary' : 'border-white/10 hover:border-white/30'">
                                <img :src="img" :alt="`${product.name} ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div>
                        <div v-if="product.discount_percentage"
                            class="inline-block px-3 py-1 text-sm font-bold text-white bg-secondary rounded-full mb-4">
                            Giảm {{ product.discount_percentage }}%
                        </div>

                        <h1 class="text-3xl font-bold text-white mb-4">{{ product.name }}</h1>

                        <div v-if="product.average_rating || reviews.length" class="flex items-center gap-2 mb-4">
                            <div class="flex items-center gap-1">
                                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24"
                                    :fill="i <= (product.average_rating || 0) ? 'currentColor' : 'none'"
                                    :class="i <= (product.average_rating || 0) ? 'text-yellow-400' : 'text-slate-600'"
                                    stroke="currentColor" stroke-width="2">
                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </div>
                            <span class="text-slate-400 text-sm">({{ product.reviews_count || reviews.length }} đánh giá)</span>
                        </div>

                        <div class="flex items-center gap-4 mb-6">
                            <span class="text-3xl font-bold gradient-text">{{ formatPrice(product.sale_price || product.price) }}</span>
                            <span v-if="product.sale_price && product.sale_price < product.price"
                                class="text-xl text-slate-500 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <p v-if="product.short_description" class="text-slate-400 mb-6 leading-relaxed">{{ product.short_description }}</p>

                        <div class="flex items-center gap-4 mb-8">
                            <div class="flex items-center bg-dark-700 rounded-xl overflow-hidden border border-white/5">
                                <button @click="quantity > 1 && quantity--"
                                    class="px-4 py-3 text-slate-400 hover:text-white hover:bg-dark-600 transition-colors">
                                    -
                                </button>
                                <span class="px-6 py-3 font-bold text-white min-w-[3rem] text-center">{{ quantity }}</span>
                                <button @click="quantity++"
                                    class="px-4 py-3 text-slate-400 hover:text-white hover:bg-dark-600 transition-colors">
                                    +
                                </button>
                            </div>
                            <button @click="addToCart" :disabled="isAddingToCart" class="btn btn-primary flex-1 py-4 font-bold flex items-center justify-center gap-2">
                                <svg v-if="!isAddingToCart" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" />
                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <span v-else class="w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                {{ isAddingToCart ? 'Đang thêm...' : t('cart.addToCart') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 p-4 bg-dark-700/50 rounded-xl border border-white/5">
                                <span class="text-2xl">🚚</span>
                                <span class="text-xs text-slate-300 font-medium">Miễn phí vận chuyển</span>
                            </div>
                            <div class="flex items-center gap-3 p-4 bg-dark-700/50 rounded-xl border border-white/5">
                                <span class="text-2xl">🛡️</span>
                                <span class="text-xs text-slate-300 font-medium">Bảo hành chính hãng</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="product.description" class="mt-12">
                    <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-8 bg-primary rounded-full"></span>
                        Mô tả sản phẩm
                    </h2>
                    <div class="card bg-dark-800/50 border-white/5 p-8">
                        <div class="prose prose-invert max-w-none text-slate-300 leading-relaxed whitespace-pre-line">
                            {{ product.description }}
                        </div>
                    </div>
                </div>

                <div class="mt-16">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <span class="w-1.5 h-8 bg-secondary rounded-full"></span>
                            Đánh giá sản phẩm
                        </h2>
                        <button v-if="authStore.isAuthenticated" @click="showReviewForm = !showReviewForm"
                            class="btn btn-secondary border border-white/10">
                            {{ showReviewForm ? 'Hủy đánh giá' : 'Viết đánh giá' }}
                        </button>
                    </div>

                    <div v-if="showReviewForm" class="card bg-dark-800 border-primary/20 mb-12 animate-in fade-in slide-in-from-top-4 duration-300">
                        <h3 class="text-lg font-bold text-white mb-6">Đánh giá của bạn</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-3">Chất lượng sản phẩm</label>
                                <div class="flex gap-2">
                                    <button v-for="i in 5" :key="i" @click="reviewRating = i" class="transition-transform active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                            :fill="i <= reviewRating ? 'currentColor' : 'none'"
                                            :class="i <= reviewRating ? 'text-yellow-400' : 'text-slate-700'"
                                            stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-2">Lời nhắn</label>
                                <textarea v-model="reviewComment" class="form-input min-h-[120px]" 
                                    placeholder="Bạn thấy sản phẩm này thế nào? Chia sẻ trải nghiệm của bạn nhé..."></textarea>
                            </div>
                            <button @click="submitReview" :disabled="isSubmittingReview || !reviewComment.trim()" class="btn btn-primary px-8">
                                <span v-if="isSubmittingReview" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                                Gửi đánh giá ngay
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div v-for="review in reviews" :key="review.id" class="group">
                            <div class="card bg-dark-800/30 border-white/5 hover:border-white/10 transition-colors p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-primary flex-shrink-0 flex items-center justify-center font-bold text-white ring-4 ring-primary/10">
                                        {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-4 mb-2">
                                            <div>
                                                <h4 class="font-bold text-white">{{ review.user?.name || 'Ẩn danh' }}</h4>
                                                <div class="flex gap-1 mt-1">
                                                    <svg v-for="i in 5" :key="i" width="12" height="12" viewBox="0 0 24 24"
                                                        :fill="i <= review.rating ? 'currentColor' : 'none'"
                                                        :class="i <= review.rating ? 'text-yellow-400' : 'text-slate-700'"
                                                        stroke="currentColor" stroke-width="2">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="text-xs text-slate-500 whitespace-nowrap">{{ formatDate(review.created_at) }}</span>
                                        </div>
                                        <p class="text-slate-300 text-sm leading-relaxed">{{ review.content || (review as any).comment }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!reviews.length" class="text-center py-20 bg-dark-800/10 rounded-2xl border border-dashed border-white/5">
                            <div class="text-5xl mb-4 opacity-20">💬</div>
                            <p class="text-slate-500 font-medium">Chưa có đánh giá nào cho sản phẩm này.</p>
                            <p v-if="!authStore.isAuthenticated" class="text-xs text-slate-600 mt-2">
                                Vui lòng đăng nhập để trở thành người đầu tiên đánh giá!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-32">
                <div class="text-6xl mb-6">🔍</div>
                <h2 class="text-2xl font-bold text-white mb-2">Sản phẩm không tồn tại</h2>
                <p class="text-slate-400 mb-8">Có vẻ như sản phẩm bạn tìm kiếm đã bị gỡ bỏ hoặc không tồn tại.</p>
                <RouterLink to="/products" class="btn btn-primary px-8">Quay lại cửa hàng</RouterLink>
            </div>
        </div>
    </div>
</template>
