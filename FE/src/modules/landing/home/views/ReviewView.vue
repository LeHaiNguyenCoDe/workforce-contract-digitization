<script setup lang="ts">
import { onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { useI18n } from 'vue-i18n'
import { useReviewStore } from '../store/review'

const { t } = useI18n()
const reviewStore = useReviewStore()

const modules = [Navigation, Pagination]

onMounted(() => {
    reviewStore.fetchFeaturedReviews()
})

// Render stars
const renderStarsArray = (rating: number) => {
    return Array(rating).fill(true)
}
</script>

<template>
    <section class="py-16 overflow-hidden">
        <div>
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-[#9F7A5F] mb-3">{{ t('home.reviewTitle') || 'Đánh giá từ người dùng'
                }}</h2>
                <p class="text-gray-600 text-lg">
                    {{ t('home.reviewDesc') || 'Những sản phẩm được đánh giá tốt từ người dùng' }}
                </p>
            </div>
            <div v-if="reviewStore.isLoading" class="text-center py-16 text-gray-500">
                {{ t('common.loading') || 'Đang tải...' }}
            </div>

            <!-- Slider -->

            <div v-else-if="reviewStore.hasFeaturedReviews" class="relative flex bg-[#C4C4C480] py-12">
                <div class="w-1/2 text-black px-8 space-y-3 font-sans">
                    <div class="font-semibold text-3xl">Những sản phẩm được đánh giá tốt từ người dùng.</div>
                    <div>
                        <span class="text-lg">Bạn cũng có thể đánh giá và nhận xét được chúng tôi thiết kế ra nhiều sản
                            phẩm tốt hơn nữa.</span>
                    </div>
                    <div class="whitespace-nowrap inline-flex items-center gap-2">
                        <span class="text-lg">{{ reviewStore.getRatingCount(5) }} đánh giá từ 5</span>
                        <img src="../../../../assets/landing/home/star.svg" alt="star" class="w-4 h-4">
                    </div>
                </div>
                <swiper :slides-per-view="3" :space-between="20"
                    :navigation="{ nextEl: '.review-next', prevEl: '.review-prev' }"
                    :pagination="{ el: '.review-pagination', clickable: true }" :modules="modules" :breakpoints="{
                        320: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 15 },
                        1024: { slidesPerView: 3, spaceBetween: 20 }
                    }" class="w-2/3 overflow-hidden">
                    <swiper-slide v-for="review in reviewStore.featuredReviews" :key="review.id">
                        <div class="bg-white rounded-sm shadow-md h-full flex flex-col overflow-hidden">
                            <div class="h-60 overflow-hidden">
                                <img :src="review.image" :alt="review.author"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-2 flex flex-col flex-grow">
                                <div class="flex gap-1">
                                    <img v-for="index in renderStarsArray(review.rating)" :key="index"
                                        src="../../../../assets/landing/home/star.svg" alt="star" class="w-4 h-4">
                                </div>
                                <p class="text-sm text-gray-600 flex-grow">{{ review.comment }}</p>
                                <p class="text-sm font-semibold text-gray-800 text-center">"&nbsp;{{ review.author }} "
                                </p>
                            </div>
                        </div>
                    </swiper-slide>
                </swiper>

                <!-- Custom navigation -->
                <button
                    class="review-prev absolute right-16 top-1 z-10 w-10 h-10 rounded-full bg-white border-2 border-gray-300 shadow-lg flex items-center justify-center hover:border-[#9F7A5F] hover:text-[#9F7A5F] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#9F7A5F]" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <button
                    class="review-next absolute right-4 top-1 z-10 w-10 h-10 rounded-full bg-white border-2 border-gray-300 shadow-lg flex items-center justify-center hover:border-[#9F7A5F] hover:text-[#9F7A5F] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#9F7A5F]" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 6 6 6-6 6" />
                    </svg>
                </button>
                <div class="flex justify-center gap-3 mt-8"></div>
            </div>
            <div v-else class="text-center py-12 text-gray-500">{{ t('common.noData') || 'Chưa có đánh giá nào' }}</div>
        </div>
    </section>
</template>
