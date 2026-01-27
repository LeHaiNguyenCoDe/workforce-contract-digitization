<script setup lang="ts">
/**
 * ImageHoverPreview Component
 * Wrapper that adds hover overlay with preview button for images
 */
defineProps<{
	/** Height class for the container */
	height?: string
	/** Whether preview is disabled */
	disabled?: boolean
	/** Border radius class */
	rounded?: string
}>()

defineEmits<{
	preview: []
}>()
</script>

<template>
	<div
		class="image-hover-preview group relative overflow-hidden cursor-pointer"
		:class="[rounded || 'rounded-sm', height]"
		@click="!disabled && $emit('preview')"
	>
		<slot />
		
		<!-- Hover Overlay -->
		<div
			v-if="!disabled"
			class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
		>
			<div
				class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center transform translate-y-4 group-hover:translate-y-0 transition-all duration-300"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				>
					<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
					<circle cx="12" cy="12" r="3" />
				</svg>
			</div>
		</div>
	</div>
</template>
