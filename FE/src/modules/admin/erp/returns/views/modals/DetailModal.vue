<script setup lang="ts">
import { computed } from 'vue'
import { formatPrice, formatDateTime } from '@/utils'
import { returnStatusLabels, returnStatusClasses } from '../../configs/columns'
import type { Return } from '../../models/return'

interface Props {
  modelValue: boolean
  item: Return | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'close': []
}>()

const show = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

function close() {
  show.value = false
  emit('close')
}
</script>

<template>
  <DModal v-model="show" title="Chi tiết phiếu RMA" size="lg" @close="close">
    <div v-if="item" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-400 mb-1">Mã RMA</label>
          <p class="text-white font-medium">#RMA-{{ item.id }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Mã đơn hàng</label>
          <p class="text-primary-light">#{{ item.order_id }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Khách hàng</label>
          <p class="text-white">{{ item.customer?.name || 'N/A' }}</p>
          <p class="text-xs text-slate-500">{{ item.customer?.email }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Trạng thái</label>
          <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', returnStatusClasses[item.status]]">
            {{ returnStatusLabels[item.status] }}
          </span>
        </div>
      </div>

      <div>
        <label class="block text-sm text-slate-400 mb-1">Lý do trả hàng</label>
        <p class="text-white">{{ item.reason }}</p>
      </div>

      <div v-if="item.notes">
        <label class="block text-sm text-slate-400 mb-1">Ghi chú</label>
        <p class="text-slate-300">{{ item.notes }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-400 mb-1">Số tiền hoàn</label>
          <p class="text-success font-semibold">{{ formatPrice(item.refund_amount || 0) }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Phương thức</label>
          <p class="text-white">{{ item.refund_method || 'Chưa xác định' }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Ngày tạo</label>
          <p class="text-slate-300">{{ formatDateTime(item.created_at) }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Cập nhật</label>
          <p class="text-slate-300">{{ formatDateTime(item.updated_at) }}</p>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end">
        <DButton variant="secondary" @click="close">Đóng</DButton>
      </div>
    </template>
  </DModal>
</template>
