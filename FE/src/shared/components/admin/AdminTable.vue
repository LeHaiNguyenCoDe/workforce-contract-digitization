<script setup lang="ts">
import { computed } from 'vue'

interface Column {
  key: string
  label: string
  width?: string
  class?: string
  sortable?: boolean
}

interface Props {
  columns: Column[]
  data: any[]
  loading?: boolean
  emptyText?: string
  emptyIcon?: string
  rowKey?: string
  hoverable?: boolean
  striped?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  data: () => [],
  loading: false,
  emptyText: 'Không có dữ liệu',
  emptyIcon: 'inbox',
  rowKey: 'id',
  hoverable: true,
  striped: false
})

const emit = defineEmits<{
  'row-click': [item: any, index: number]
}>()

const handleRowClick = (item: any, index: number) => {
  emit('row-click', item, index)
}
</script>

<template>
  <div class="admin-table-wrapper flex-1 flex flex-col overflow-hidden rounded-xl border border-white/10 bg-dark-800">
    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center py-16">
      <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <!-- Table -->
    <div v-else class="flex-1 overflow-auto">
      <table class="w-full">
        <thead class="bg-dark-700 sticky top-0 z-10">
          <tr class="border-b border-white/10">
            <th
              v-for="col in columns"
              :key="col.key"
              :style="col.width ? { width: col.width } : {}"
              :class="[
                'px-6 py-4 text-left text-sm font-semibold text-slate-400',
                col.class
              ]"
            >
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" class="px-6 py-4 text-right text-sm font-semibold text-slate-400">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr
            v-for="(item, index) in data"
            :key="item[rowKey] || index"
            :class="[
              'transition-colors',
              hoverable && 'hover:bg-white/5 cursor-pointer',
              striped && index % 2 === 1 && 'bg-white/[0.02]'
            ]"
            @click="handleRowClick(item, index)"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="['px-6 py-4', col.class]"
            >
              <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]" :index="index">
                <span class="text-slate-300">{{ item[col.key] ?? '-' }}</span>
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-6 py-4 text-right">
              <slot name="actions" :item="item" :index="index"></slot>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="!data.length" class="py-16 text-center">
        <svg v-if="emptyIcon === 'inbox'" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" 
          fill="none" stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
          <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
          <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
        </svg>
        <p class="text-slate-400">{{ emptyText }}</p>
      </div>
    </div>

    <!-- Footer Slot (for pagination) -->
    <div v-if="$slots.footer && data.length" class="border-t border-white/10">
      <slot name="footer"></slot>
    </div>
  </div>
</template>
