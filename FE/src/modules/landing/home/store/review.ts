import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { httpClient } from '@/plugins/api/httpClient'
import type {
    Review,
    ReviewsResponse,
    FeaturedReview
} from '../types/reviews'

export const useReviewStore = defineStore('landing-review', () => {
    const reviews = ref<Review[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const isLoading = computed(() => loading.value)

    const highRatedReviews = computed(() => {
        return reviews.value.filter(review => review.rating >= 4)
    })

    const fiveStarReviews = computed(() => {
        return reviews.value.filter(review => review.rating === 5)
    })

    const fourStarReviews = computed(() => {
        return reviews.value.filter(review => review.rating === 4)
    })

    const highRatedReviewsWithImages = computed(() => {
        return reviews.value.filter(review =>
            review.rating >= 4 &&
            review.product?.images &&
            review.product.images.length > 0
        )
    })

    const fiveStarReviewsWithImages = computed(() => {
        return reviews.value.filter(review =>
            review.rating === 5 &&
            review.product?.images &&
            review.product.images.length > 0
        )
    })

    const highRatedWithImagesCount = computed(() => {
        return highRatedReviews.value.length
    })

    const fiveStarWithImagesCount = computed(() => {
        return fiveStarReviews.value.length
    })

    const featuredReviews = computed<FeaturedReview[]>(() => {
        return highRatedReviews.value.map(review => ({
            id: review.id,
            image: review.product?.images?.[0]?.image_url ||
                review.product?.thumbnail ||
                'https://via.placeholder.com/400x300?text=No+Image',
            comment: review.content || '',
            rating: review.rating,
            author: review.user?.name || 'Anonymous'
        }))
    })

    const hasFeaturedReviews = computed(() => featuredReviews.value.length > 0)

    const getRatingCount = (rating: number) => {
        return reviews.value.filter(r => r.rating === rating).length
    }

    async function fetchFeaturedReviews(limit: number = 20) {
        loading.value = true
        error.value = null

        try {
            // Single optimized API call - replaces N+1 queries
            const response = await httpClient.get<{ status: string; data: Review[] }>(
                '/frontend/featured-reviews',
                { params: { limit } }
            )

            if (response.data.status === 'success' && response.data.data) {
                reviews.value = response.data.data
            } else {
                reviews.value = []
            }
        } catch (err) {
            error.value = 'Failed to fetch reviews'
            console.error('Error fetching featured reviews:', err)
            reviews.value = []
        } finally {
            loading.value = false
        }
    }

    async function fetchProductReviews(productId: number, perPage: number = 100) {
        loading.value = true
        error.value = null

        try {
            const response = await httpClient.get<{ status: string; data: ReviewsResponse }>(
                `/frontend/products/${productId}/reviews`,
                { params: { per_page: perPage } }
            )

            if (response.data.status === 'success' && response.data.data) {
                reviews.value = response.data.data.items || []
            } else {
                reviews.value = []
            }
        } catch (err) {
            error.value = 'Failed to fetch reviews'
            console.error('Error fetching reviews:', err)
            reviews.value = []
        } finally {
            loading.value = false
        }
    }

    function clearReviews() {
        reviews.value = []
        error.value = null
    }

    return {
        reviews,
        loading,
        error,
        isLoading,
        highRatedReviews,
        fiveStarReviews,
        fourStarReviews,
        highRatedReviewsWithImages,
        fiveStarReviewsWithImages,
        highRatedWithImagesCount,
        fiveStarWithImagesCount,
        featuredReviews,
        hasFeaturedReviews,
        getRatingCount,
        fetchFeaturedReviews,
        fetchProductReviews,
        clearReviews,
    }
})