<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  specs: any
}>()

const specifications = computed(() => {
    if (!props.specs) return []
    if (typeof props.specs === 'object') {
        return Object.entries(props.specs).map(([key, value]) => ({
            label: key.replace(/_/g, ' '),
            value: value as string
        }))
    }
    return []
})
</script>

<template>
  <div class="mb-6">
       <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 text-lg">Thông số kỹ thuật</h3>
            <a href="#" class="text-sm text-blue-600 hover:underline flex items-center gap-1">Xem tất cả <i class="ri-arrow-right-s-line"></i></a>
       </div>
       
       <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
            <div v-if="specifications.length" class="text-sm">
                <div v-for="(spec, index) in specifications" :key="index"
                        class="flex border-b border-gray-200 last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="w-[35%] bg-gray-50 p-3 text-gray-600 border-r border-gray-200 flex items-center">
                        {{ spec.label }}
                    </div>
                    <div class="w-[65%] p-3 text-gray-900 font-medium">
                        {{ spec.value }}
                    </div>
                </div>
            </div>
            <div v-else class="text-center text-gray-400 py-8 text-sm">
                Đang cập nhật thông số...
            </div>
       </div>
  </div>
</template>
