<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useOrderStore } from '../store/store'
import { useOrders } from '../composables/useOrders'

const { t } = useI18n()

// Store
const store = useOrderStore()

// Composables
const {
  searchQuery,
  filteredOrders,
  canApproveOrders,
  getStatusInfo,
  getStockStatus,
  getCustomerName,
  formatPrice,
  formatDate,
  setSearchQuery,
  handleStatusUpdate,
  handleAssignShipper,
  confirmAssignShipper,
  handleCheckStock,
  cancelOrder,
  viewTracking,
  approveOrder
} = useOrders()

// Computed from store
const orders = computed(() => store.orders)
const isLoading = computed(() => store.isLoading)
const isUpdating = computed(() => store.isUpdating)
const currentPage = computed(() => store.currentPage)
const totalPages = computed(() => store.totalPages)
const statusFilter = computed(() => store.statusFilter)
const shippers = computed(() => store.shippers)
const showShipperModal = computed({
  get: () => store.showShipperModal,
  set: (value) => { store.showShipperModal = value }
})
const selectedOrderForShipper = computed(() => store.selectedOrderForShipper)
const selectedShipperId = computed({
  get: () => store.selectedShipperId,
  set: (value) => { store.selectedShipperId = value }
})

// Methods
function setStatusFilter(value: string) {
  store.setStatusFilter(value)
  store.fetchOrders()
}

function changePage(page: number) {
  store.setPage(page)
  store.fetchOrders()
}

function getCustomerContact(order: any) {
  return order.phone || order.user?.email || ''
}

function getOrderTotal(order: any) {
  return order.total ?? order.total_amount ?? 0
}

// Lifecycle
onMounted(async () => {
  await store.fetchOrders()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ t('admin.orders') }}</h1>
        <p class="text-slate-400 mt-1">Quản lý đơn hàng</p>
      </div>
      <div v-if="canApproveOrders" class="text-sm text-success">
        ✓ Có quyền duyệt đơn hàng
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
            xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input :value="searchQuery" @input="setSearchQuery(($event.target as HTMLInputElement).value)" type="text"
            placeholder="Tìm kiếm đơn hàng..."
            class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary" />
        </div>
        <select :value="statusFilter" @change="setStatusFilter(($event.target as HTMLSelectElement).value)"
          class="form-input w-48">
          <option value="">Tất cả trạng thái</option>
          <option value="pending">Chờ xử lý</option>
          <option value="processing">Đang xử lý</option>
          <option value="shipped">Đang giao</option>
          <option value="delivered">Đã giao</option>
          <option value="cancelled">Đã hủy</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
        </div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Mã đơn</th>
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Khách hàng</th>
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Tổng tiền</th>
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Kho hàng</th>
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Ngày đặt</th>
              <th v-if="canApproveOrders"
                class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-white/5 transition-colors">
              <td class="px-4 py-4">
                <code class="text-xs font-mono text-primary">{{ order.order_number }}</code>
              </td>
              <td class="px-4 py-4">
                <p class="font-medium text-white">{{ getCustomerName(order) }}</p>
                <p class="text-xs text-slate-500">{{ getCustomerContact(order) }}</p>
              </td>
              <td class="px-4 py-4 text-white font-medium">{{ formatPrice(getOrderTotal(order)) }}</td>
              <td class="px-4 py-4">
                <span :class="['text-xs font-medium', getStockStatus(order).color]">
                  {{ getStockStatus(order).text }}
                </span>
              </td>
              <td class="px-4 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusInfo(order.status).color]">
                  {{ getStatusInfo(order.status).text }}
                </span>
              </td>
              <td class="px-4 py-4 text-slate-400 text-sm">{{ formatDate(order.created_at) }}</td>
              <td v-if="canApproveOrders" class="px-4 py-4">
                <div class="flex items-center gap-2">
                  <button v-if="order.status === 'pending'"
                    @click="approveOrder(order)"
                    :disabled="isUpdating === order.id"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-success/10 text-success hover:bg-success/20 disabled:opacity-50">
                    {{ isUpdating === order.id ? 'Đang xử lý...' : 'Duyệt' }}
                  </button>
                  <button v-if="order.status !== 'cancelled' && order.status !== 'delivered'"
                    @click="cancelOrder(order.id)"
                    :disabled="isUpdating === order.id"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-error/10 text-error hover:bg-error/20 disabled:opacity-50">
                    Hủy
                  </button>
                  <button @click="handleCheckStock(order)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-info/10 text-info hover:bg-info/20">
                    Kiểm kho
                  </button>
                  <button @click="viewTracking(order)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary/20">
                    Theo dõi
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!filteredOrders.length" class="py-16 text-center">
          <p class="text-slate-400">Không có đơn hàng nào</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="border-t border-white/10 p-4 flex items-center justify-between">
        <span class="text-sm text-slate-400">Trang {{ currentPage }} / {{ totalPages }}</span>
        <div class="flex gap-2">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
            Trước
          </button>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
            Sau
          </button>
        </div>
      </div>
    </div>

    <!-- Shipper Modal -->
    <BaseModal v-model="showShipperModal" title="Phân công shipper" size="md">
      <div v-if="selectedOrderForShipper" class="space-y-4">
        <div>
          <p class="text-sm text-slate-400 mb-2">Đơn hàng: <span class="text-white font-medium">{{ selectedOrderForShipper.order_number }}</span></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Chọn shipper</label>
          <select v-model="selectedShipperId" class="form-input">
            <option :value="null">Chọn shipper</option>
            <option v-for="shipper in shippers" :key="shipper.id" :value="shipper.id">
              {{ shipper.name }} - {{ shipper.phone }}
            </option>
          </select>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showShipperModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="confirmAssignShipper" :disabled="!selectedShipperId" class="btn btn-primary flex-1">
            Xác nhận
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
