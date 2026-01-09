<script setup lang="ts">
/**
 * StarRating Component
 * Reusable star rating display and input
 */
withDefaults(defineProps<{
	/** Current rating value */
	modelValue: number
	/** Maximum number of stars */
	max?: number
	/** Whether rating can be edited */
	editable?: boolean
	/** Size variant */
	size?: 'sm' | 'md' | 'lg'
}>(), {
	max: 5,
	editable: false,
	size: 'md'
})

const emit = defineEmits<{
	'update:modelValue': [value: number]
}>()

const handleClick = (value: number) => {
	emit('update:modelValue', value)
}

const sizeMap = {
	sm: 14,
	md: 18,
	lg: 24
}
</script>

<template>
	<div class="star-rating" :class="size">
		<button
			v-for="i in max"
			:key="i"
			type="button"
			class="star-btn"
			:class="{ editable }"
			:disabled="!editable"
			@click="editable && handleClick(i)"
		>
			<svg
				xmlns="http://www.w3.org/2000/svg"
				:width="sizeMap[size]"
				:height="sizeMap[size]"
				viewBox="0 0 24 24"
				:fill="i <= modelValue ? '#FFB800' : 'none'"
				:stroke="i <= modelValue ? '#FFB800' : '#D1D5DB'"
				stroke-width="2"
			>
				<polygon
					points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
				/>
			</svg>
		</button>
	</div>
</template>
