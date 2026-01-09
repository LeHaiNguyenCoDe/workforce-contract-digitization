<script setup lang="ts">
/**
 * Order Detail Modal
 * Professional order detail view with customer info, products, stock status, and actions
 */
import { computed } from 'vue'
import type { Order } from '../store/store'

interface Props {
  modelValue: boolean
  order: Order | null
  isUpdating?: number | null
}

const props = withDefaults(defineProps<Props>(), {
  isUpdating: null
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'confirm': [order: Order]
  'deliver': [order: Order]
  'complete': [order: Order]
  'cancel': [order: Order]
}>()

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Status configuration
const statusConfig: Record<string, { text: string; color: string; bgColor: string }> = {
  draft: { text: 'Nháp', color: 'text-slate-400', bgColor: 'bg-slate-500/10' },
  pending: { text: 'Chờ xử lý', color: 'text-warning', bgColor: 'bg-warning/10' },
  confirmed: { text: 'Đã xác nhận', color: 'text-info', bgColor: 'bg-info/10' },
  processing: { text: 'Đang xử lý', color: 'text-info', bgColor: 'bg-info/10' },
  shipped: { text: 'Đang giao', color: 'text-primary', bgColor: 'bg-primary/10' },
  delivered: { text: 'Đã giao', color: 'text-success', bgColor: 'bg-success/10' },
  completed: { text: 'Hoàn thành', color: 'text-emerald-400', bgColor: 'bg-emerald-500/10' },
  cancelled: { text: 'Đã hủy', color: 'text-error', bgColor: 'bg-error/10' }
}

const paymentStatusConfig: Record<string, { text: string; color: string }> = {
  pending: { text: 'Chờ thanh toán', color: 'text-warning' },
  paid: { text: 'Đã thanh toán', color: 'text-success' },
  failed: { text: 'Thất bại', color: 'text-error' },
  refunded: { text: 'Đã hoàn tiền', color: 'text-info' }
}

// Helpers
function getStatusInfo(status: string) {
  return statusConfig[status] || statusConfig.pending
}

function getPaymentStatusInfo(status: string) {
  return paymentStatusConfig[status] || paymentStatusConfig.pending
}

function formatPrice(price: number | undefined | null) {
  if (price === undefined || price === null || isNaN(price)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

function formatDate(date: string | undefined) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getOrderCode(order: Order) {
  return order.order_number || (order as any).code || `#${order.id}`
}

function getCustomerName(order: Order) {
  return order.full_name || order.user?.name || 'Khách hàng'
}

function getOrderTotal(order: Order) {
  return order.total ?? order.total_amount ?? 0
}

function getProductName(item: any) {
  return item.product?.name || item.name || 'Sản phẩm'
}

function getProductImage(item: any) {
  return item.product?.thumbnail || item.product?.images?.[0]?.url || null
}

// Actions visibility
const canConfirm = computed(() => {
  if (!props.order) return false
  return ['draft', 'pending'].includes(props.order.status)
})

const canDeliver = computed(() => {
  if (!props.order) return false
  return ['confirmed', 'processing'].includes(props.order.status)
})

const canComplete = computed(() => {
  if (!props.order) return false
  return props.order.status === 'delivered'
})

const canCancel = computed(() => {
  if (!props.order) return false
  return !['completed', 'cancelled'].includes(props.order.status)
})

const isProcessing = computed(() => {
  return props.isUpdating === props.order?.id
})

// Timeline items
const timeline = computed(() => {
  if (!props.order) return []
  
  const items = [
    { 
      status: 'created',
      label: 'Đơn hàng được tạo',
      time: props.order.created_at,
      done: true,
      icon: '📝'
    }
  ]
  
  // Add based on current status
  const status = props.order.status
  
  if (['confirmed', 'processing', 'shipped', 'delivered', 'completed'].includes(status)) {
    items.push({ status: 'confirmed', label: 'Đã xác nhận', time: '', done: true, icon: '✅' })
  } else {
    items.push({ status: 'confirmed', label: 'Chờ xác nhận', time: '', done: false, icon: '⏳' })
  }
  
  if (['shipped', 'delivered', 'completed'].includes(status)) {
    items.push({ status: 'shipped', label: 'Đang giao hàng', time: '', done: true, icon: '🚚' })
  } else if (!['cancelled'].includes(status)) {
    items.push({ status: 'shipped', label: 'Chờ giao hàng', time: '', done: false, icon: '📦' })
  }
  
  if (['delivered', 'completed'].includes(status)) {
    items.push({ status: 'delivered', label: 'Đã giao hàng', time: '', done: true, icon: '📬' })
  } else if (!['cancelled'].includes(status)) {
    items.push({ status: 'delivered', label: 'Chờ nhận hàng', time: '', done: false, icon: '🏠' })
  }
  
  if (status === 'completed') {
    items.push({ status: 'completed', label: 'Hoàn thành', time: '', done: true, icon: '🎉' })
  } else if (status === 'cancelled') {
    items.push({ status: 'cancelled', label: 'Đã hủy', time: '', done: true, icon: '❌' })
  }
  
  return items
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen && order" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isOpen = false"></div>
        
        <!-- Modal -->
        <div class="relative bg-dark-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-white/10 shadow-2xl flex flex-col">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-dark-900/50">
            <div class="flex items-center gap-4">
              <div>
                <div class="flex items-center gap-3">
                  <h2 class="text-xl font-bold text-white">Chi tiết đơn hàng</h2>
                  <code class="px-2 py-1 bg-primary/20 text-primary text-sm font-bold rounded">
                    {{ getOrderCode(order) }}
                  </code>
                </div>
                <p class="text-sm text-slate-400 mt-1">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span :class="[
                'px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider',
                getStatusInfo(order.status).bgColor,
                getStatusInfo(order.status).color
              ]">
                {{ getStatusInfo(order.status).text }}
              </span>
              <button @click="isOpen = false" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Content -->
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Customer & Order Info -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Customer Info -->
              <div class="bg-dark-700/50 rounded-xl p-5 border border-white/5">
                <h3 class="text-sm font-semibold text-slate-400 mb-4 flex items-center gap-2">
                  <span class="text-lg">👤</span> Thông tin khách hàng
                </h3>
                <div class="space-y-3">
                  <div class="flex items-start gap-3">
                    <span class="text-slate-500 w-24 text-sm">Họ tên:</span>
                    <span class="text-white font-medium">{{ getCustomerName(order) }}</span>
                  </div>
                  <div class="flex items-start gap-3">
                    <span class="text-slate-500 w-24 text-sm">Điện thoại:</span>
                    <span class="text-white">{{ order.phone || 'N/A' }}</span>
                  </div>
                  <div class="flex items-start gap-3">
                    <span class="text-slate-500 w-24 text-sm">Email:</span>
                    <span class="text-white">{{ order.user?.email || 'N/A' }}</span>
                  </div>
                  <div class="flex items-start gap-3">
                    <span class="text-slate-500 w-24 text-sm">Địa chỉ:</span>
                    <span class="text-white">{{ (order as any).address_line || (order as any).address || 'N/A' }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Order Timeline -->
              <div class="bg-dark-700/50 rounded-xl p-5 border border-white/5">
                <h3 class="text-sm font-semibold text-slate-400 mb-4 flex items-center gap-2">
                  <span class="text-lg">📋</span> Tiến trình đơn hàng
                </h3>
                <div class="relative">
                  <div class="absolute left-4 top-3 bottom-3 w-0.5 bg-dark-600"></div>
                  <div class="space-y-3">
                    <div v-for="(item, index) in timeline" :key="index" class="relative flex items-center gap-3 pl-10">
                      <div :class="[
                        'absolute left-2 w-6 h-6 rounded-full flex items-center justify-center text-xs',
                        item.done ? 'bg-success' : 'bg-dark-600 border border-slate-600'
                      ]">
                        <span v-if="item.done" class="text-white">✓</span>
                        <span v-else class="text-slate-500 text-[10px]">{{ item.icon }}</span>
                      </div>
                      <span :class="['text-sm', item.done ? 'text-white font-medium' : 'text-slate-500']">
                        {{ item.label }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Products Table -->
            <div class="bg-dark-700/50 rounded-xl border border-white/5 overflow-hidden">
              <div class="px-5 py-4 border-b border-white/5">
                <h3 class="text-sm font-semibold text-slate-400 flex items-center gap-2">
                  <span class="text-lg">📦</span> Sản phẩm ({{ order.items?.length || 0 }})
                </h3>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-dark-800/50">
                    <tr class="text-left">
                      <th class="px-5 py-3 text-xs font-semibold text-slate-400">Sản phẩm</th>
                      <th class="px-5 py-3 text-xs font-semibold text-slate-400 text-center">SL</th>
                      <th class="px-5 py-3 text-xs font-semibold text-slate-400 text-right">Đơn giá</th>
                      <th class="px-5 py-3 text-xs font-semibold text-slate-400 text-right">Thành tiền</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5">
                    <tr v-for="item in (order.items || [])" :key="item.id" class="hover:bg-white/[0.02]">
                      <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                          <div class="w-12 h-12 bg-dark-600 rounded-lg overflow-hidden flex-shrink-0">
                            <img v-if="getProductImage(item)" :src="getProductImage(item)" :alt="getProductName(item)" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                              📦
                            </div>
                          </div>
                          <div>
                            <p class="text-sm font-medium text-white">{{ getProductName(item) }}</p>
                            <p class="text-xs text-slate-500">SKU: {{ item.product?.sku || `P${item.product_id}` }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-dark-600 rounded-lg text-sm font-semibold text-white">
                          {{ item.qty }}
                        </span>
                      </td>
                      <td class="px-5 py-3 text-right text-sm text-slate-300">
                        {{ formatPrice(item.price) }}
                      </td>
                      <td class="px-5 py-3 text-right text-sm font-semibold text-primary">
                        {{ formatPrice(item.price * item.qty) }}
                      </td>
                    </tr>
                    <!-- Empty state -->
                    <tr v-if="!order.items?.length">
                      <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                        Không có sản phẩm
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Order Summary -->
            <div class="bg-gradient-to-br from-primary/5 to-secondary/5 rounded-xl p-5 border border-white/5">
              <div class="flex items-center justify-between">
                <div class="space-y-2">
                  <div class="flex items-center gap-8 text-sm">
                    <span class="text-slate-400">Tạm tính:</span>
                    <span class="text-white">{{ formatPrice(getOrderTotal(order)) }}</span>
                  </div>
                  <div class="flex items-center gap-8 text-sm">
                    <span class="text-slate-400">Phí vận chuyển:</span>
                    <span class="text-success">Miễn phí</span>
                  </div>
                  <div v-if="(order as any).discount" class="flex items-center gap-8 text-sm">
                    <span class="text-slate-400">Giảm giá:</span>
                    <span class="text-error">-{{ formatPrice((order as any).discount) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm text-slate-400 mb-1">Tổng thanh toán</p>
                  <p class="text-3xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    {{ formatPrice(getOrderTotal(order)) }}
                  </p>
                  <p v-if="order.payment_status" :class="['text-xs mt-1', getPaymentStatusInfo(order.payment_status).color]">
                    {{ getPaymentStatusInfo(order.payment_status).text }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Footer Actions -->
          <div class="px-6 py-4 border-t border-white/10 bg-dark-900/50 flex items-center justify-between">
            <button @click="isOpen = false" class="px-4 py-2 text-slate-400 hover:text-white transition-colors">
              Đóng
            </button>
            <div class="flex items-center gap-3">
              <button v-if="canCancel" 
                @click="emit('cancel', order)" 
                :disabled="isProcessing"
                class="px-4 py-2 bg-error/10 hover:bg-error/20 text-error rounded-lg font-medium transition-colors disabled:opacity-50">
                <span v-if="isProcessing">Đang xử lý...</span>
                <span v-else>❌ Hủy đơn</span>
              </button>
              <button v-if="canConfirm" 
                @click="emit('confirm', order)" 
                :disabled="isProcessing"
                class="px-4 py-2 bg-success/10 hover:bg-success/20 text-success rounded-lg font-medium transition-colors disabled:opacity-50">
                <span v-if="isProcessing">Đang xử lý...</span>
                <span v-else>✅ Xác nhận đơn</span>
              </button>
              <button v-if="canDeliver" 
                @click="emit('deliver', order)" 
                :disabled="isProcessing"
                class="px-4 py-2 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg font-medium transition-colors disabled:opacity-50">
                <span v-if="isProcessing">Đang xử lý...</span>
                <span v-else>🚚 Đánh dấu đã giao</span>
              </button>
              <button v-if="canComplete" 
                @click="emit('complete', order)" 
                :disabled="isProcessing"
                class="px-4 py-2 bg-gradient-to-r from-success to-primary text-white rounded-lg font-medium transition-colors disabled:opacity-50">
                <span v-if="isProcessing">Đang xử lý...</span>
                <span v-else>🎉 Hoàn thành</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

