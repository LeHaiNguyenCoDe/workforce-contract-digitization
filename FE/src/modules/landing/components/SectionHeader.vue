<script setup lang="ts">
/**
 * SectionHeader Component
 * Reusable section header with title, subtitle, and style variants
 */
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

withDefaults(defineProps<{
	/** Main title text or i18n key */
	title: string
	/** Optional subtitle text or i18n key */
	subtitle?: string
	/** Style variant */
	variant?: 'default' | 'centered' | 'italic'
	/** Text color theme */
	color?: 'primary' | 'dark' | 'light'
	/** Whether title is an i18n key */
	i18n?: boolean
}>(), {
	variant: 'centered',
	color: 'primary',
	i18n: false
})
</script>

<template>
	<div class="section-header" :class="[variant, `color-${color}`]">
		<h2 class="title">{{ i18n ? t(title) : title }}</h2>
		<p v-if="subtitle" class="subtitle">{{ i18n ? t(subtitle) : subtitle }}</p>
		<slot name="extra" />
	</div>
</template>
