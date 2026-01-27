<script setup lang="ts">
/**
 * SliderNavigation Component
 * Navigation arrows for Swiper sliders
 */
withDefaults(defineProps<{
	/** Custom class for prev button (for Swiper navigation targeting) */
	prevClass?: string
	/** Custom class for next button (for Swiper navigation targeting) */
	nextClass?: string
	/** Whether prev button is disabled */
	prevDisabled?: boolean
	/** Whether next button is disabled */
	nextDisabled?: boolean
	/** Style variant */
	variant?: 'default' | 'rounded' | 'minimal'
	/** Use arrow image instead of SVG */
	useArrowImage?: boolean
}>(), {
	prevClass: 'slider-prev',
	nextClass: 'slider-next',
	variant: 'default',
	useArrowImage: false
})

defineEmits<{
	prev: []
	next: []
}>()
</script>

<template>
	<div class="slider-navigation" :class="variant">
		<button
			:class="[prevClass, { disabled: prevDisabled }]"
			:disabled="prevDisabled"
			@click="$emit('prev')"
		>
			<template v-if="useArrowImage">
				<img
					src="@/assets/landing/home/arrow.svg"
					alt="Previous"
					class="rotate-180 w-5 h-5"
				/>
			</template>
			<template v-else>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="w-5 h-5"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
				>
					<path d="m15 18-6-6 6-6" />
				</svg>
			</template>
		</button>
		<button
			:class="[nextClass, { disabled: nextDisabled }]"
			:disabled="nextDisabled"
			@click="$emit('next')"
		>
			<template v-if="useArrowImage">
				<img
					src="@/assets/landing/home/arrow.svg"
					alt="Next"
					class="w-5 h-5"
				/>
			</template>
			<template v-else>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="w-5 h-5"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
				>
					<path d="m9 6 6 6-6 6" />
				</svg>
			</template>
		</button>
	</div>
</template>
