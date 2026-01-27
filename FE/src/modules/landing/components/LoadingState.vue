<script setup lang="ts">
/**
 * LoadingState Component
 * Skeleton loading with various variants
 */
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

withDefaults(defineProps<{
	/** Display variant */
	variant?: 'text' | 'grid' | 'card' | 'product'
	/** Number of skeleton items */
	count?: number
	/** Grid columns (tailwind classes) */
	cols?: string
	/** Height for skeleton items */
	height?: string
	/** Custom loading message */
	message?: string
}>(), {
	variant: 'text',
	count: 4,
	cols: 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
	height: '12rem'
})
</script>

<template>
	<!-- Text Loading -->
	<div v-if="variant === 'text'" class="text-center py-12 text-gray-500">
		{{ message || t('common.loading') || 'Đang tải...' }}
	</div>

	<!-- Grid Skeleton -->
	<div v-else-if="variant === 'grid'" class="grid gap-4" :class="cols">
		<div
			v-for="i in count"
			:key="i"
			class="bg-gray-200 rounded-xl animate-pulse"
			:style="{ height }"
		/>
	</div>

	<!-- Card Skeleton -->
	<div v-else-if="variant === 'card'" class="grid gap-6" :class="cols">
		<div v-for="i in count" :key="i" class="animate-pulse">
			<div class="aspect-square bg-gray-200 rounded-lg mb-4" />
			<div class="h-4 bg-gray-200 rounded w-3/4 mb-2" />
			<div class="h-4 bg-gray-200 rounded w-1/2" />
		</div>
	</div>

	<!-- Product Skeleton -->
	<div v-else-if="variant === 'product'" class="grid gap-6" :class="cols">
		<div v-for="i in count" :key="i" class="animate-pulse">
			<div class="aspect-square bg-gray-100 rounded mb-4" />
			<div class="h-4 bg-gray-100 rounded w-3/4 mb-2" />
			<div class="h-4 bg-gray-100 rounded w-1/2" />
		</div>
	</div>
</template>
