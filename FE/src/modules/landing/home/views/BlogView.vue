<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { useI18n } from 'vue-i18n'
import { usePostStore } from '../store/post'

const { t } = useI18n()
const postStore = usePostStore()

const modules = [Navigation, Pagination]

// Image Modal State
const showImageModal = ref(false)
const modalImages = ref<string[]>([])
const modalInitialIndex = ref(0)

const openPreview = (imageUrl: string) => {
	if (!imageUrl) return
	modalImages.value = [imageUrl]
	modalInitialIndex.value = 0
	showImageModal.value = true
}

onMounted(() => {
	postStore.fetchFeaturedPosts(4)
})
</script>

<template>
	<div>
		<section class="py-12 bg-white">
			<div class="container">
				<SectionHeader :title="t('home.blogTitle') || 'Mẹo thiết kế'" variant="centered" />

				<!-- Loading state -->
				<LoadingState v-if="postStore.isLoading" variant="text" />

				<!-- Slider -->
				<div v-else-if="postStore.hasFeaturedPosts" class="relative">
					<div class="flex gap-2 justify-end mb-2">
						<SliderNavigation
							prev-class="custom-prev"
							next-class="custom-next"
							variant="default"
							use-arrow-image
						/>
					</div>
					<swiper
						:slides-per-view="3"
						:space-between="20"
						:navigation="{ nextEl: '.custom-next', prevEl: '.custom-prev' }"
						:pagination="{ el: '.custom-pagination', clickable: true }"
						:modules="modules"
						:breakpoints="{
							320: { slidesPerView: 1, spaceBetween: 10 },
							768: { slidesPerView: 2, spaceBetween: 15 },
							1024: { slidesPerView: 3, spaceBetween: 20 }
						}"
						class="w-full pb-12"
					>
						<swiper-slide v-for="post in postStore.featuredPosts" :key="post.id">
							<div class="bg-white hover:shadow-lg transition-shadow duration-300 rounded-sm overflow-hidden block">
								<ImageHoverPreview
									height="h-[32rem]"
									@preview="openPreview(post.image)"
								>
									<img :src="post.image" :alt="post.title" class="w-full h-full object-cover" />
								</ImageHoverPreview>
								<div class="p-4 text-center">
									<h3 class="text-lg font-semibold mb-2 text-slate-800 line-clamp-2" v-html="post.title" />
								</div>
							</div>
						</swiper-slide>
					</swiper>
				</div>

				<!-- Empty state -->
				<EmptyState
					v-else
					icon="default"
					:message="t('common.noData') || 'Chưa có bài viết nào'"
				/>
			</div>
		</section>

		<!-- Image Modal -->
		<ImageModal v-model="showImageModal" :images="modalImages" :currentIndex="modalInitialIndex" />
	</div>
</template>
