import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { httpClient } from '@/plugins/api/httpClient'
import type {
    Review,
    Product,
    ProductsResponse,
    ReviewsResponse,
    FeaturedReview
} from '../types/reviews'

export const useReviewStore = defineStore('landing-review', () => {
    const reviews = ref<Review[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const isLoading = computed(() => loading.value)

    const fiveStarReviews = computed(() => {
        return reviews.value.filter(review => review.rating === 5)
    })

    const fiveStarReviewsWithImages = computed(() => {
        return reviews.value.filter(review =>
            review.rating === 5 &&
            review.product?.images &&
            review.product.images.length > 0
        )
    })

    const fiveStarWithImagesCount = computed(() => {
        return fiveStarReviews.value.length
    })

    const featuredReviews = computed<FeaturedReview[]>(() => {
        return fiveStarReviews.value.map(review => ({
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

    async function fetchFeaturedReviews() {
        loading.value = true
        error.value = null

        try {
            const allReviews: Review[] = []

            let currentPage = 1
            let totalProducts = 0
            const allProducts: Product[] = []

            while (true) {
                const productsResponse = await httpClient.get<{ status: string; data: ProductsResponse }>(
                    '/frontend/products',
                    { params: { per_page: 100, page: currentPage } }
                )

                if (productsResponse.data.status !== 'success' || !productsResponse.data.data) {
                    break
                }

                const products = productsResponse.data.data.items || []
                const meta = productsResponse.data.data.meta

                if (products.length === 0) {
                    break
                }

                allProducts.push(...products)
                totalProducts = meta.total

                if (allProducts.length >= totalProducts || currentPage >= meta.last_page) {
                    break
                }

                currentPage++
            }

            for (let i = 0; i < allProducts.length; i++) {
                const product = allProducts[i]

                try {
                    let reviewPage = 1

                    while (true) {
                        const reviewsResponse = await httpClient.get<{ status: string; data: ReviewsResponse }>(
                            `/frontend/products/${product.id}/reviews`,
                            { params: { per_page: 100, page: reviewPage } }
                        )

                        if (reviewsResponse.data.status === 'success' && reviewsResponse.data.data) {
                            const productReviews = reviewsResponse.data.data.items || []
                            const meta = reviewsResponse.data.data.meta

                            if (productReviews.length === 0) {
                                break
                            }

                            productReviews.forEach((review: Review) => {
                                review.product = {
                                    id: product.id,
                                    name: product.name,
                                    slug: product.slug,
                                    thumbnail: product.thumbnail,
                                    images: product.images
                                }
                                allReviews.push(review)
                            })

                            if (reviewPage >= meta.last_page) {
                                break
                            }

                            reviewPage++
                        } else {
                            break
                        }
                    }
                } catch (err) {
                    console.warn(`Failed to fetch reviews for product ${product.id}:`, err)
                }
            }

            reviews.value = allReviews

        } catch (err) {
            error.value = 'Failed to fetch reviews'
            console.error('Error fetching reviews:', err)
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
        fiveStarReviews,
        fiveStarReviewsWithImages,
        fiveStarWithImagesCount,
        featuredReviews,
        hasFeaturedReviews,
        getRatingCount,
        fetchFeaturedReviews,
        fetchProductReviews,
        clearReviews,
    }
})