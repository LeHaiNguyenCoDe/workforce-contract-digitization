<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { useI18n } from 'vue-i18n'
import { usePostStore } from '../store/post'
import ImageModal from '@/shared/components/ImageModal.vue'

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
	<section class="py-12 bg-white">
		<div class="container">
			<h1 class="text-3xl font-bold text-center text-[#9F7A5F] mb-8">{{ t('home.blogTitle') || 'Mẹo thiết kế' }}
			</h1>
			<!-- Loading state -->
			<div v-if="postStore.isLoading" class="text-center py-12 text-gray-500">
				{{ t('common.loading') || 'Đang tải...' }}
			</div>

			<!-- Slider -->
			<div v-else-if="postStore.hasFeaturedPosts" class="relative">
				<div class="flex gap-2 justify-end mb-2">
					<!-- Custom Navigation -->
					<button
						class="custom-prev z-10 w-8 h-8 rounded-full bg-white shadow-lg flex items-center justify-center cursor-pointer hover:bg-gray-100 transition-colors">
						<img src="../../../../assets/landing/home/arrow.svg" alt="Prev products"
							class="rotate-180 w-5 h-5" />
					</button>
					<button
						class="custom-next z-10 w-8 h-8 rounded-full bg-white shadow-lg flex items-center justify-center cursor-pointer hover:bg-gray-100 transition-colors">
						<img src="../../../../assets/landing/home/arrow.svg" alt="Next products" class="w-5 h-5" />
					</button>
				</div>
				<swiper :slides-per-view="3" :space-between="20"
					:navigation="{ nextEl: '.custom-next', prevEl: '.custom-prev' }"
					:pagination="{ el: '.custom-pagination', clickable: true }" :modules="modules" :breakpoints="{
						320: { slidesPerView: 1, spaceBetween: 10 },
						768: { slidesPerView: 2, spaceBetween: 15 },
						1024: { slidesPerView: 3, spaceBetween: 20 }
					}" class="w-full pb-12">
					<swiper-slide v-for="post in postStore.featuredPosts" :key="post.id">
						<div
							class="bg-white hover:shadow-lg transition-shadow duration-300 rounded-sm overflow-hidden block">
							<div class="overflow-hidden h-[32rem] cursor-pointer relative group"
								@click="openPreview(post.image)">
								<img :src="post.image" :alt="post.title"
									class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
								<div
									class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
									<div
										class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
											viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round">
											<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
											<circle cx="12" cy="12" r="3"></circle>
										</svg>
									</div>
								</div>
							</div>
							<div class="p-4 text-center">
								<h3 class="text-lg font-semibold mb-2 text-slate-800 line-clamp-2" v-html="post.title">
								</h3>
							</div>
						</div>
					</swiper-slide>
				</swiper>
			</div>

			<!-- Empty state -->
			<div v-else class="text-center py-12 text-gray-500">{{ t('common.noData') || 'Chưa có bài viết nào' }}</div>
		</div>
	</section>

	<!-- Image Modal -->
	<ImageModal v-model="showImageModal" :images="modalImages" :currentIndex="modalInitialIndex" />
</template>
