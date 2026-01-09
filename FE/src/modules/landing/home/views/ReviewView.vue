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
</script>

<template>
	<section class="py-16 overflow-hidden">
		<div>
			<div class="text-center mb-12">
				<SectionHeader
					:title="t('home.reviewTitle') || 'Đánh giá từ người dùng'"
					:subtitle="t('home.reviewDesc') || 'Những sản phẩm được đánh giá tốt từ người dùng'"
					variant="centered"
				/>
			</div>

			<LoadingState v-if="reviewStore.isLoading" variant="text" />

			<!-- Slider -->
			<div v-else-if="reviewStore.hasFeaturedReviews" class="relative flex bg-[#C4C4C480] py-12">
				<div class="w-1/2 text-black px-8 space-y-3 font-sans">
					<div class="font-semibold text-3xl">Những sản phẩm được đánh giá tốt từ người dùng.</div>
					<div>
						<span class="text-lg">Bạn cũng có thể đánh giá và nhận xét được chúng tôi thiết kế ra nhiều sản phẩm tốt hơn nữa.</span>
					</div>
					<div class="whitespace-nowrap inline-flex items-center gap-2">
						<span class="text-lg">{{ reviewStore.getRatingCount(5) }} đánh giá từ 5</span>
						<img src="../../../../assets/landing/home/star.svg" alt="star" class="w-4 h-4" />
					</div>
				</div>

				<swiper
					:slides-per-view="3"
					:space-between="20"
					:navigation="{ nextEl: '.review-next', prevEl: '.review-prev' }"
					:pagination="{ el: '.review-pagination', clickable: true }"
					:modules="modules"
					:breakpoints="{
						320: { slidesPerView: 1, spaceBetween: 10 },
						768: { slidesPerView: 2, spaceBetween: 15 },
						1024: { slidesPerView: 3, spaceBetween: 20 }
					}"
					class="w-2/3 overflow-hidden"
				>
					<swiper-slide v-for="review in reviewStore.featuredReviews" :key="review.id">
						<div class="bg-white rounded-sm shadow-md h-full flex flex-col overflow-hidden">
							<div class="h-60 overflow-hidden">
								<img
									:src="review.image"
									:alt="review.author"
									class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
								/>
							</div>
							<div class="p-2 flex flex-col flex-grow">
								<StarRating :model-value="review.rating" size="sm" />
								<p class="text-sm text-gray-600 flex-grow">{{ review.comment }}</p>
								<p class="text-sm font-semibold text-gray-800 text-center">"&nbsp;{{ review.author }} "</p>
							</div>
						</div>
					</swiper-slide>
				</swiper>

				<!-- Custom navigation -->
				<SliderNavigation
					prev-class="review-prev absolute right-16 top-1 z-10"
					next-class="review-next absolute right-4 top-1 z-10"
					variant="rounded"
				/>

				<div class="flex justify-center gap-3 mt-8" />
			</div>

			<EmptyState
				v-else
				icon="review"
				:message="t('common.noData') || 'Chưa có đánh giá nào'"
			/>
		</div>
	</section>
</template>

