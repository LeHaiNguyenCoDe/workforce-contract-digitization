<script setup lang="ts">
/**
 * EmptyState Component
 * Empty state display with customizable icon, title, and message
 */
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

withDefaults(defineProps<{
	/** Icon type to display */
	icon?: 'default' | 'search' | 'cart' | 'review' | 'promo' | 'none'
	/** Title text */
	title?: string
	/** Description message */
	message?: string
	/** Use i18n for title */
	titleKey?: string
	/** Use i18n for message */
	messageKey?: string
}>(), {
	icon: 'default'
})
</script>

<template>
	<div class="empty-state text-center py-12">
		<!-- Icons -->
		<div v-if="icon !== 'none'" class="mb-4">
			<BaseIcon
				v-if="icon === 'default'"
				name="package"
				:size="64"
				:stroke-width="1"
				class="mx-auto text-gray-400"
			/>
			<BaseIcon
				v-else-if="icon === 'search'"
				name="search"
				:size="64"
				:stroke-width="1"
				class="mx-auto text-gray-400"
			/>
			<BaseIcon
				v-else-if="icon === 'cart'"
				name="cart"
				:size="64"
				:stroke-width="1"
				class="mx-auto text-gray-400"
			/>
			<BaseIcon
				v-else-if="icon === 'review'"
				name="star"
				:size="64"
				:stroke-width="1"
				class="mx-auto text-gray-400"
			/>
			<BaseIcon
				v-else-if="icon === 'promo'"
				name="promo"
				:size="64"
				:stroke-width="1"
				class="mx-auto text-gray-400"
			/>
		</div>

		<!-- Title -->
		<h3 v-if="title || titleKey" class="text-lg font-semibold text-gray-600 mb-2">
			{{ titleKey ? t(titleKey) : title }}
		</h3>

		<!-- Message -->
		<p v-if="message || messageKey" class="text-gray-500">
			{{ messageKey ? t(messageKey) : message }}
		</p>

		<!-- Action Slot -->
		<div v-if="$slots.action" class="mt-6">
			<slot name="action" />
		</div>
	</div>
</template>
