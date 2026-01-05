<script setup lang="ts">
/**
 * Product Detail View - Redesigned
 * Matches the provided design mockups
 */
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'
import ImageModal from '@/shared/components/ImageModal.vue'
import { useLandingProducts } from '../composables/useLandingProducts'

const { t } = useI18n()
const route = useRoute()
const authStore = useAuthStore()
const { formatPrice } = useLandingProducts()

// Product state
const product = ref<any>(null)
const reviews = ref<any[]>([])
const similarProducts = ref<any[]>([])
const isLoading = ref(true)
const quantity = ref(1)
const isAddingToCart = ref(false)
const selectedImage = ref(0)
const selectedColor = ref<string | null>(null)

// Review state
const reviewRating = ref(5)
const reviewComment = ref('')
const isSubmittingReview = ref(false)
const replyingTo = ref<number | null>(null)
const replyContent = ref('')
const reviewsDisplayCount = ref(5)

// Image Modal
const showImageModal = ref(false)
const modalImages = ref<string[]>([])
const modalInitialIndex = ref(0)

// Get all product images
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

// Get average rating
const averageRating = computed(() => {
    if (!reviews.value.length) return 0
    const sum = reviews.value.reduce((acc, r) => acc + (r.rating || 0), 0)
    return Math.round((sum / reviews.value.length) * 10) / 10
})

// Visible reviews (limited by reviewsDisplayCount)
const visibleReviews = computed(() => {
    return reviews.value.slice(0, reviewsDisplayCount.value)
})

// Toggle reviews (Load more or Hide)
const toggleReviews = () => {
    if (reviewsDisplayCount.value >= reviews.value.length) {
        reviewsDisplayCount.value = 5
        // Scroll to reviews section for better UX when hiding
        const reviewsSection = document.getElementById('reviews-section')
        if (reviewsSection) {
            reviewsSection.scrollIntoView({ behavior: 'smooth' })
        }
    } else {
        reviewsDisplayCount.value += 5
    }
}

// Parse specs from product
const specifications = computed(() => {
    if (!product.value?.specs) return []
    const specs = product.value.specs
    if (typeof specs === 'object') {
        return Object.entries(specs).map(([key, value]) => ({
            label: key.replace(/_/g, ' '),
            value: value as string
        }))
    }
    return []
})

// Demo color options (can be replaced with real data)
const colorOptions = computed(() => {
    return [
        { id: 'beige', name: 'Beige', color: '#E8DDD4' },
        { id: 'gray', name: 'Gray', color: '#8B8B8B' },
        { id: 'brown', name: 'Brown', color: '#8B6914' }
    ]
})

const openImageModal = (imageIndex: number) => {
    modalImages.value = allImages.value
    modalInitialIndex.value = imageIndex
    showImageModal.value = true
}

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
            console.log('Reviews API Response:', reviewsData)
            if (reviewsData?.data?.items) {
                reviews.value = reviewsData.data.items
                console.log('Using data.items:', reviewsData.data.items)
            } else if (reviewsData?.data?.data) {
                reviews.value = reviewsData.data.data
                console.log('Using data.data:', reviewsData.data.data)
            } else if (Array.isArray(reviewsData?.data)) {
                reviews.value = reviewsData.data
                console.log('Using data array:', reviewsData.data)
            } else {
                reviews.value = []
            }
            console.log('Reviews with replies:', reviews.value.map((r: any) => ({ id: r.id, content: r.content, repliesCount: r.replies?.length || 0, replies: r.replies })))
        } catch {
            reviews.value = []
        }

        // Fetch similar products
        console.log('Product category_id:', product.value?.category_id)
        if (product.value?.category_id) {
            try {
                const similarRes = await httpClient.get('/frontend/products', {
                    params: { category_id: product.value.category_id, per_page: 5 }
                })
                const similarData = similarRes.data as any
                console.log('Similar products API response:', similarData)
                const items = similarData?.data?.items || similarData?.data?.data || similarData?.data || []
                similarProducts.value = items.filter((p: any) => p.id !== product.value.id).slice(0, 4)
                console.log('Similar products filtered:', similarProducts.value)
            } catch (err) {
                console.error('Error fetching similar products:', err)
                similarProducts.value = []
            }
        } else {
            console.log('No category_id found, skipping similar products')
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
        alert(t('common.loginRequired'))
        return
    }
    isAddingToCart.value = true
    try {
        await httpClient.post('/frontend/cart/items', {
            product_id: product.value.id,
            qty: quantity.value
        })
        authStore.incrementCartCount(quantity.value)
        alert(t('cart.addedToCart'))
    } catch (error: any) {
        alert(error.response?.data?.message || t('cart.addToCartFailed'))
    } finally {
        isAddingToCart.value = false
    }
}

const submitReview = async () => {
    if (!product.value || !reviewComment.value.trim()) return
    if (!authStore.isAuthenticated) {
        alert(t('common.loginToReview'))
        return
    }
    isSubmittingReview.value = true
    try {
        await httpClient.post(`/frontend/products/${product.value.id}/reviews`, {
            rating: reviewRating.value,
            content: reviewComment.value
        })
        alert(t('product.thankForReview'))
        reviewComment.value = ''
        reviewRating.value = 5
        // Refetch reviews
        const reviewsRes = await httpClient.get(`/frontend/products/${product.value.id}/reviews`)
        const reviewsData = reviewsRes.data as any
        if (reviewsData?.data?.items) reviews.value = reviewsData.data.items
        else if (Array.isArray(reviewsData?.data)) reviews.value = reviewsData.data
    } catch (error: any) {
        alert(error.response?.data?.message || t('product.reviewFailed'))
    } finally {
        isSubmittingReview.value = false
    }
}

const toggleReply = (reviewId: number) => {
    if (replyingTo.value === reviewId) {
        replyingTo.value = null
        replyContent.value = ''
    } else {
        replyingTo.value = reviewId
        replyContent.value = ''
    }
}

const isSubmittingReply = ref(false)

const submitReply = async (parentReviewId: number) => {
    if (!product.value || !replyContent.value.trim()) return
    if (!authStore.isAuthenticated) {
        alert(t('common.loginRequired'))
        return
    }

    isSubmittingReply.value = true
    try {
        await httpClient.post(`/frontend/products/${product.value.id}/reviews`, {
            rating: 5,
            content: replyContent.value,
            parent_id: parentReviewId
        })
        alert('Đã gửi trả lời!')
        replyContent.value = ''
        replyingTo.value = null
        // Refetch reviews
        const reviewsRes = await httpClient.get(`/frontend/products/${product.value.id}/reviews`)
        const reviewsData = reviewsRes.data as any
        if (reviewsData?.data?.items) reviews.value = reviewsData.data.items
        else if (Array.isArray(reviewsData?.data)) reviews.value = reviewsData.data
    } catch (error: any) {
        alert(error.response?.data?.message || 'Gửi trả lời thất bại')
    } finally {
        isSubmittingReply.value = false
    }
}


onMounted(fetchProduct)
watch(() => route.params.id, fetchProduct)
</script>

<template>
    <div class="min-h-screen bg-white">
        <div class="container py-6">
            <!-- Loading -->
            <div v-if="isLoading" class="animate-pulse">
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="aspect-square bg-gray-200 rounded-lg"></div>
                    <div class="space-y-4">
                        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-6 bg-gray-200 rounded w-1/4"></div>
                        <div class="h-24 bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Product Content -->
            <div v-else-if="product">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                    <RouterLink to="/" class="hover:text-[#9F7A5F]">{{ t('nav.home') }}</RouterLink>
                    <span>/</span>
                    <RouterLink v-if="product.category" :to="`/categories/${product.category.id}`"
                        class="hover:text-[#9F7A5F]">
                        {{ product.category.name }}
                    </RouterLink>
                    <span v-if="product.category">/</span>
                    <span class="text-[#9F7A5F] font-medium">{{ product.name }}</span>
                </nav>

                <!-- Section 1: Product Info -->
                <div class="grid md:grid-cols-2 gap-8 lg:gap-12 mb-12">
                    <!-- Image Gallery -->
                    <div>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4 cursor-zoom-in"
                            @click="openImageModal(selectedImage)">
                            <img v-if="allImages[selectedImage]" :src="allImages[selectedImage]" :alt="product.name"
                                class="w-full h-full object-contain hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1">
                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>
                        </div>
                        <!-- Thumbnails -->
                        <div v-if="allImages.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                            <button v-for="(img, index) in allImages" :key="index" @click="selectedImage = index"
                                class="w-16 h-16 rounded-lg overflow-hidden border-2 flex-shrink-0 transition-all"
                                :class="selectedImage === index ? 'border-[#9F7A5F]' : 'border-gray-200 hover:border-gray-300'">
                                <img :src="img" :alt="`${product.name} ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                        <p class="text-2xl font-bold text-[#9F7A5F] mb-4">{{ formatPrice(product.price) }}</p>

                        <!-- Rating -->
                        <div v-if="reviews.length" class="flex items-center gap-2 mb-4">
                            <div class="flex gap-0.5">
                                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" :fill="i <= averageRating ? '#FFB800' : 'none'"
                                    :stroke="i <= averageRating ? '#FFB800' : '#D1D5DB'" stroke-width="2">
                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500">{{ reviews.length }} lượt đánh giá</span>
                        </div>

                        <p v-if="product.short_description" class="text-gray-600 mb-6 leading-relaxed">{{
                            product.short_description }}</p>

                        <!-- Color Options -->
                        <div v-if="colorOptions.length" class="mb-6">
                            <p class="text-sm font-medium text-gray-700 mb-2">Màu sắc:</p>
                            <div class="flex gap-2">
                                <button v-for="color in colorOptions" :key="color.id" @click="selectedColor = color.id"
                                    class="w-8 h-8 rounded-full border-2 transition-all"
                                    :style="{ backgroundColor: color.color }"
                                    :class="selectedColor === color.id ? 'border-[#9F7A5F] ring-2 ring-[#9F7A5F]/20' : 'border-gray-300'">
                                </button>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-6">
                            <p class="text-sm font-medium text-gray-700 mb-2">Số lượng:</p>
                            <div class="inline-flex items-center border border-gray-300 rounded-lg">
                                <button @click="quantity > 1 && quantity--"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition-colors">−</button>
                                <span class="px-4 py-2 min-w-[3rem] text-center font-medium">{{ quantity }}</span>
                                <button @click="quantity++"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition-colors">+</button>
                            </div>
                        </div>

                        <!-- Add to Cart -->
                        <button @click="addToCart" :disabled="isAddingToCart"
                            class="w-full md:w-auto px-8 py-3 bg-[#9F7A5F] text-white font-medium rounded-lg hover:bg-[#8B6B51] transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="!isAddingToCart" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="8" cy="21" r="1" />
                                <circle cx="19" cy="21" r="1" />
                                <path
                                    d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                            </svg>
                            <span v-else
                                class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ isAddingToCart ? 'Đang thêm...' : 'Thêm vào giỏ hàng' }}
                        </button>
                    </div>
                </div>

                <!-- Section 2: Thông tin chi tiết -->
                <div class="border-t border-gray-200 pt-8 mb-12">
                    <h2 class="text-xl font-semibold text-[#9F7A5F] text-center mb-8 italic">Thông tin chi tiết</h2>

                    <div v-if="product.description" class="max-w-4xl mx-auto">
                        <!-- Main product image -->
                        <div v-if="allImages[0]" class="mb-6">
                            <img :src="allImages[0]" :alt="product.name" class="w-full rounded-lg" />
                        </div>

                        <p class="text-center text-gray-600 leading-relaxed mb-8">{{ product.description }}</p>

                        <!-- Additional images grid -->
                        <div v-if="allImages.length > 1" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                            <div v-for="(img, index) in allImages.slice(1, 5)" :key="index" class="cursor-pointer"
                                @click="openImageModal(index + 1)">
                                <img :src="img" :alt="`${product.name} ${index + 2}`"
                                    class="w-full aspect-square object-cover rounded-lg hover:opacity-90 transition-opacity" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Thông số chi tiết -->
                <div v-if="specifications.length" class="border-t border-gray-200 pt-8 mb-12">
                    <h2 class="text-xl font-semibold text-[#9F7A5F] text-center mb-8 italic">Thông số chi tiết</h2>

                    <div class="max-w-3xl mx-auto">
                        <div class="grid grid-cols-2 gap-x-12 gap-y-4">
                            <template v-for="(spec, index) in specifications" :key="index">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600 capitalize">{{ spec.label }}</span>
                                    <span class="text-gray-900 font-medium">{{ spec.value }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Đánh giá từ khách hàng -->
                <div class="border-t border-gray-200 pt-8 mb-12">
                    <h2 class="text-xl font-semibold text-[#9F7A5F] text-center mb-2 italic">Đánh giá từ khách hàng</h2>

                    <!-- Overall Rating -->
                    <div class="flex items-center justify-center gap-2 mb-8">
                        <span class="text-2xl font-bold text-gray-900">{{ averageRating || 0 }}/5</span>
                        <div class="flex gap-0.5">
                            <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" :fill="i <= averageRating ? '#FFB800' : 'none'"
                                :stroke="i <= averageRating ? '#FFB800' : '#D1D5DB'" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-400">{{ reviews.length }} lượt đánh giá</span>
                    </div>

                    <!-- Reviews List -->
                    <div class="max-w-3xl mx-auto">
                        <div v-for="review in visibleReviews" :key="review.id" class="mb-6">
                            <!-- Main Review -->
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-[#9F7A5F] text-white flex items-center justify-center font-semibold flex-shrink-0 text-sm">
                                    {{ review.user?.name?.charAt(0)?.toUpperCase() || 'K' }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 text-sm">{{ review.user?.name || 'Khách hàng'
                                    }}</h4>
                                    <p class="text-gray-600 text-sm mb-1">{{ review.content }}</p>
                                    <div class="flex items-center gap-3">
                                        <button @click="toggleReply(review.id)"
                                            class="text-xs text-[#9F7A5F] hover:underline font-medium">Trả lời</button>
                                        <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Replies with House Icon -->
                            <div v-if="review.replies?.length" class="mt-4 ml-14 space-y-4">
                                <div v-for="reply in review.replies" :key="reply.id" class="flex items-start gap-3">
                                    <!-- House Icon -->
                                    <div
                                        class="w-8 h-8 rounded-full bg-[#F5EFE9] flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="#9F7A5F" stroke-width="2">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            <polyline points="9 22 9 12 15 12 15 22" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-semibold text-gray-800 text-sm">{{ reply.user?.name || 'House'
                                        }}</h5>
                                        <p class="text-gray-600 text-sm mb-1">{{ reply.content }}</p>
                                        <div class="flex items-center gap-3">
                                            <button class="text-xs text-[#9F7A5F] hover:underline font-medium">Trả
                                                lời</button>
                                            <span class="text-xs text-gray-400">{{ formatDate(reply.created_at)
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reply Form - Shows inline when clicking Trả lời -->
                            <div v-if="replyingTo === review.id" class="mt-4 ml-14">
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <input v-model="replyContent" type="text"
                                        class="w-full px-4 py-3 text-sm focus:outline-none"
                                        placeholder="Mời bạn bình luận..." @keyup.enter="submitReply(review.id)">
                                    <div class="flex justify-end px-2 py-2 bg-gray-50 border-t border-gray-100">
                                        <button @click="submitReply(review.id)"
                                            :disabled="isSubmittingReply || !replyContent.trim()"
                                            class="px-4 py-1.5 bg-[#9F7A5F] text-white text-xs rounded disabled:opacity-50">
                                            {{ isSubmittingReply ? '...' : 'Gửi' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Reviews -->
                        <div v-if="!reviews.length" class="text-center py-8 text-gray-500">
                            Chưa có đánh giá nào
                        </div>
                    </div>

                    <!-- Review Form - Horizontal Layout -->
                    <div class="max-w-3xl mx-auto mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-start gap-8">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Bình luận của bạn</label>
                                <input v-model="reviewComment" type="text"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"
                                    placeholder="Mời bạn bình luận">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Đánh giá của bạn</label>
                                <div class="flex items-center gap-3">
                                    <div class="flex gap-0.5">
                                        <button v-for="i in 5" :key="i" @click="reviewRating = i"
                                            class="transition-transform hover:scale-110">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" :fill="i <= reviewRating ? '#FFB800' : 'none'"
                                                :stroke="i <= reviewRating ? '#FFB800' : '#D1D5DB'" stroke-width="2">
                                                <polygon
                                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        </button>
                                    </div>
                                    <button @click="submitReview"
                                        :disabled="isSubmittingReview || !reviewComment.trim()"
                                        class="px-4 py-2 bg-[#9F7A5F] text-white text-sm rounded-lg hover:bg-[#8B6B51] disabled:opacity-50">
                                        Gửi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle reviews - only show if there are more than 5 reviews total -->
                    <div v-if="reviews.length > 5" id="reviews-section" class="text-center mt-6">
                        <button @click="toggleReviews" class="text-sm text-gray-500 hover:text-[#9F7A5F] underline">
                            {{ reviewsDisplayCount >= reviews.length ? 'Ẩn bớt' : 'Xem thêm' }}
                        </button>
                    </div>
                </div>

                <!-- Section 5: Sản phẩm tương tự -->
                <div v-if="similarProducts.length" class="border-t border-gray-200 pt-8">
                    <h1 class="text-black text-xl font-semibold text-center mb-8">Sản phẩm tương tự</h1>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <RouterLink v-for="(item, index) in similarProducts" :key="item.id" :to="`/products/${item.id}`"
                            class="group">
                            <div class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                                <!-- NEW/SALE Badge -->
                                <span v-if="index === 0"
                                    class="absolute top-2 left-2 px-2 py-0.5 bg-green-500 text-white text-xs font-medium rounded">NEW</span>
                                <span v-else-if="index === 1"
                                    class="absolute top-2 left-2 px-2 py-0.5 bg-orange-500 text-white text-xs font-medium rounded">SALE</span>
                                <img :src="item.thumbnail || item.images?.[0]?.image_url" :alt="item.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1 line-clamp-1">{{ item.name }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-[#9F7A5F]">{{ formatPrice(item.price) }}</span>
                                <span v-if="index === 1" class="text-xs text-gray-400 line-through">{{
                                    formatPrice(item.price * 1.2)
                                }}</span>
                            </div>
                        </RouterLink>
                    </div>
                </div>
            </div>

            <!-- Not Found -->
            <div v-else class="text-center py-32">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ t('product.notExist') }}</h2>
                <p class="text-gray-500 mb-8">{{ t('product.productNotFoundDesc') }}</p>
                <RouterLink to="/products" class="px-8 py-3 bg-[#9F7A5F] text-white rounded-lg">{{
                    t('product.backToStore') }}
                </RouterLink>
            </div>
        </div>

        <!-- Image Modal -->
        <ImageModal v-model="showImageModal" :images="modalImages" :currentIndex="modalInitialIndex" />
    </div>
</template>
