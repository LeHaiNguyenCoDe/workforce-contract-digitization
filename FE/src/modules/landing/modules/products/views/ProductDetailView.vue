<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import { useAuthStore } from '@/stores'

const { t } = useI18n()
const route = useRoute()
const authStore = useAuthStore()

interface ProductImage {
    id: number
    image_url: string
    is_main?: boolean
}

interface Review {
    id: number
    rating: number
    content: string
    user?: { id: number; name: string }
    created_at: string
    replies?: Review[]
    is_admin_reply?: boolean
}

interface Product {
    id: number
    name: string
    slug: string
    price: number
    sale_price?: number
    discount_percentage?: number
    description?: string
    short_description?: string
    thumbnail?: string
    images?: ProductImage[]
    reviews?: Review[]
    average_rating?: number
    reviews_count?: number
    category?: { id: number; name: string }
}

const product = ref<Product | null>(null)
const reviews = ref<Review[]>([])
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
        product.value.images.forEach(img => {
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
        console.log('Product loaded:', product.value)

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

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
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
        console.log('Reviews updated:', reviews.value)
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
                    <span class="text-white">{{ product.name }}</span>
                </nav>

                <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Images -->
                    <div>
                        <div class="aspect-square bg-dark-800 rounded-2xl overflow-hidden mb-4">
                            <img v-if="allImages[selectedImage]" :src="allImages[selectedImage]" :alt="product.name"
                                class="w-full h-full object-cover" />
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
                        <!-- Badge -->
                        <div v-if="product.discount_percentage"
                            class="inline-block px-3 py-1 text-sm font-bold text-white bg-secondary rounded-full mb-4">
                            Giảm {{ product.discount_percentage }}%
                        </div>

                        <h1 class="text-3xl font-bold text-white mb-4">{{ product.name }}</h1>

                        <!-- Rating Summary -->
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
                            <span class="text-slate-400 text-sm">({{ product.reviews_count || reviews.length }} đánh
                                giá)</span>
                        </div>

                        <!-- Price -->
                        <div class="flex items-center gap-4 mb-6">
                            <span class="text-3xl font-bold gradient-text">{{ formatPrice(product.sale_price ||
                                product.price) }}</span>
                            <span v-if="product.sale_price && product.sale_price < product.price"
                                class="text-xl text-slate-500 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.short_description" class="text-slate-400 mb-4">{{ product.short_description }}
                        </p>

                        <!-- Quantity & Add to Cart -->
                        <div class="flex items-center gap-4 mb-8">
                            <div class="flex items-center bg-dark-700 rounded-xl overflow-hidden">
                                <button @click="quantity > 1 && quantity--"
                                    class="px-4 py-3 text-slate-400 hover:text-white hover:bg-dark-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14" />
                                    </svg>
                                </button>
                                <span class="px-6 py-3 font-semibold text-white">{{ quantity }}</span>
                                <button @click="quantity++"
                                    class="px-4 py-3 text-slate-400 hover:text-white hover:bg-dark-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                </button>
                            </div>
                            <button @click="addToCart" :disabled="isAddingToCart" class="btn btn-primary flex-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                {{ isAddingToCart ? t('common.loading') : t('cart.addToCart') }}
                            </button>
                        </div>

                        <!-- Features -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 p-4 bg-dark-700 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-success" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                <span class="text-sm text-slate-300">Miễn phí vận chuyển</span>
                            </div>
                            <div class="flex items-center gap-3 p-4 bg-dark-700 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-success" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                <span class="text-sm text-slate-300">Đổi trả 30 ngày</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div v-if="product.description" class="mt-12">
                    <h2 class="text-2xl font-bold text-white mb-4">Mô tả sản phẩm</h2>
                    <div class="bg-dark-800 rounded-2xl border border-white/10 p-6">
                        <p class="text-slate-300 leading-relaxed whitespace-pre-line">{{ product.description }}</p>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="mt-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-white">Đánh giá sản phẩm</h2>
                        <button v-if="authStore.isAuthenticated" @click="showReviewForm = !showReviewForm"
                            class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                            Viết đánh giá
                        </button>
                    </div>

                    <!-- Review Form -->
                    <div v-if="showReviewForm" class="bg-dark-800 rounded-2xl border border-white/10 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Đánh giá của bạn</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Số sao</label>
                            <div class="flex items-center gap-2">
                                <button v-for="i in 5" :key="i" @click="reviewRating = i" class="focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                        :fill="i <= reviewRating ? 'currentColor' : 'none'"
                                        :class="i <= reviewRating ? 'text-yellow-400' : 'text-slate-600 hover:text-slate-400'"
                                        stroke="currentColor" stroke-width="2">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                </button>
                                <span class="text-slate-400 ml-2">{{ reviewRating }}/5</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Nhận xét</label>
                            <textarea v-model="reviewComment" class="form-input" rows="4"
                                placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button @click="submitReview" :disabled="isSubmittingReview || !reviewComment.trim()"
                                class="btn btn-primary">
                                <span v-if="isSubmittingReview"
                                    class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                                {{ isSubmittingReview ? 'Đang gửi...' : 'Gửi đánh giá' }}
                            </button>
                            <button @click="showReviewForm = false" class="btn btn-secondary">Hủy</button>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="space-y-6">
                        <div v-for="review in reviews" :key="review.id" class="space-y-4">
                            <!-- User Review -->
                            <div class="bg-dark-800 rounded-2xl border border-white/10 p-6">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold flex-shrink-0">
                                        {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <p class="font-medium text-white">{{ review.user?.name || 'Ẩn danh' }}
                                                </p>
                                                <div class="flex items-center gap-1 mt-1">
                                                    <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg"
                                                        width="14" height="14" viewBox="0 0 24 24"
                                                        :fill="i <= review.rating ? 'currentColor' : 'none'"
                                                        :class="i <= review.rating ? 'text-yellow-400' : 'text-slate-600'"
                                                        stroke="currentColor" stroke-width="2">
                                                        <polygon
                                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="text-sm text-slate-500">{{ formatDate(review.created_at)
                                                }}</span>
                                        </div>
                                        <p class="text-slate-300">{{ review.content || (review as any).comment }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Replies (Admin) -->
                            <div v-for="reply in review.replies" :key="reply.id"
                                class="ml-12 bg-primary/5 rounded-2xl border border-primary/20 p-5 relative">
                                <div class="absolute -left-6 top-6 w-6 h-px bg-primary/20"></div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-primary-light">Admin</p>
                                                <span
                                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary text-white">Official</span>
                                            </div>
                                            <span class="text-xs text-slate-500">{{ formatDate(reply.created_at)
                                                }}</span>
                                        </div>
                                        <p class="text-slate-300 text-sm italic">"{{ reply.content || (reply as
                                            any).comment }}"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!reviews.length"
                            class="text-center py-12 bg-dark-800/50 rounded-2xl border border-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            <p class="text-slate-400 mb-4">Chưa có đánh giá nào</p>
                            <p v-if="!authStore.isAuthenticated" class="text-sm text-slate-500">
                                <RouterLink to="/login" class="text-primary hover:underline">Đăng nhập</RouterLink> để
                                viết đánh giá đầu tiên!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Not Found -->
            <div v-else class="text-center py-16">
                <h2 class="text-2xl font-bold text-slate-400">Không tìm thấy sản phẩm</h2>
                <RouterLink to="/products" class="btn btn-primary mt-4">Quay lại danh sách</RouterLink>
            </div>
        </div>
    </div>
</template>
