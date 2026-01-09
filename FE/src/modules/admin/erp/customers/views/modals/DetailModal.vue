<script setup lang="ts">
import { computed } from 'vue'
import { formatPrice, formatDate } from '@/utils'
import type { Customer } from '../../models/customer'

interface Props {
  modelValue: boolean
  customer: Customer | null
  orders: any[]
}

const props = defineProps<Props>()
const emit = defineEmits<{ 'update:modelValue': [boolean]; 'close': [] }>()

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
  <DModal v-model="show" title="Chi tiết khách hàng" size="lg" @close="close">
    <div v-if="customer" class="space-y-6">
      <!-- Info -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-400 mb-1">Tên</label>
          <p class="text-white font-medium text-lg">{{ customer.name }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Email</label>
          <p class="text-slate-300">{{ customer.email }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Điện thoại</label>
          <p class="text-slate-300">{{ customer.phone || '-' }}</p>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Trạng thái</label>
          <span :class="customer.active ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400'" class="px-2.5 py-1 rounded-full text-xs font-medium">
            {{ customer.active ? 'Hoạt động' : 'Không HĐ' }}
          </span>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4 p-4 bg-dark-700 rounded-lg">
        <div>
          <p class="text-slate-400 text-sm">Tổng đơn hàng</p>
          <p class="text-xl font-bold text-info">{{ customer.total_orders || 0 }}</p>
        </div>
        <div>
          <p class="text-slate-400 text-sm">Tổng chi tiêu</p>
          <p class="text-xl font-bold text-success">{{ formatPrice(customer.total_spent || 0) }}</p>
        </div>
      </div>

      <!-- Orders -->
      <div>
        <h4 class="text-white font-medium mb-3">Đơn hàng gần đây</h4>
        <div v-if="orders.length" class="space-y-2">
          <div v-for="order in orders" :key="order.id" class="flex items-center justify-between p-3 bg-dark-700 rounded-lg">
            <div>
              <span class="text-primary-light font-medium">#{{ order.id }}</span>
              <span class="text-slate-400 text-sm ml-3">{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="text-right">
              <span class="text-success font-medium">{{ formatPrice(order.total_amount) }}</span>
              <span class="ml-2 px-2 py-0.5 rounded text-xs bg-info/10 text-info">{{ order.status }}</span>
            </div>
          </div>
        </div>
        <p v-else class="text-slate-500 text-sm">Chưa có đơn hàng</p>
      </div>
    </div>

    <template #footer>
      <DButton variant="secondary" @click="close">Đóng</DButton>
    </template>
  </DModal>
</template>
