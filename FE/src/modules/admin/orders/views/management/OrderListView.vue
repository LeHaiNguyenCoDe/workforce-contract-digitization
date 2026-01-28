<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { orderColumns } from '../../configs/columns'
import { useAdminOrderStore } from '../../store/store'
import { useAdminOrders } from '../../composables/useAdminOrders'
import OrderDetailModal from '../../components/OrderDetailModal.vue'

const { t } = useI18n()

// Store
const store = useAdminOrderStore()

// Composables
const {
  searchQuery,
  selectedOrder,
  showOrderDetail,
  filteredOrders,
  canApproveOrders,
  getStatusInfo,
  getStockStatus,
  getCustomerName,
  formatPrice,
  formatDate,
  setSearchQuery,
  confirmAssignShipper,
  viewOrderDetail,
  cancelOrder,
  handleConfirmOrder,
  handleMarkDelivered,
  handleCompleteOrder
} = useAdminOrders()

// Computed from store
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
    <AdminPageHeader :title="t('admin.orders')" :description="t('common.manageOrders')">
      <template #actions>
        <div v-if="canApproveOrders"
          class="flex items-center gap-2 px-3 py-1.5 bg-success/10 border border-success/20 rounded-lg text-xs font-medium text-success">
          <img src="@/assets/admin/icons/check.svg" class="w-3.5 h-3.5" alt="Check" />
          {{ t('common.hasApprovalPermission') }}
        </div>
      </template>
    </AdminPageHeader>

    <!-- Filters -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearchQuery" @search="store.fetchOrders()"
      :placeholder="t('common.searchOrdersPlaceholder')">
      <template #filters>
        <select :value="statusFilter" @change="setStatusFilter(($event.target as HTMLSelectElement).value)"
          class="form-input w-48 bg-dark-700 border-white/10 text-white">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Nháp</option>
          <option value="pending">Chờ xử lý</option>
          <option value="confirmed">Đã xác nhận</option>
          <option value="processing">Đang xử lý</option>
          <option value="delivered">Đã giao</option>
          <option value="completed">Hoàn thành</option>
          <option value="cancelled">Đã hủy</option>
        </select>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="orderColumns" :data="filteredOrders" :loading="isLoading" empty-text="Không có đơn hàng nào">
      <template #cell-order_number="{ item, value }">
        <code class="text-xs font-mono text-primary font-bold">{{ value || item.code || `#${item.id}` }}</code>
      </template>

      <template #cell-customer="{ item }">
        <div>
          <p class="font-medium text-white">{{ getCustomerName(item) }}</p>
          <p class="text-[10px] text-slate-500">{{ getCustomerContact(item) }}</p>
        </div>
      </template>

      <template #cell-total="{ item }">
        <span class="text-white font-semibold">{{ formatPrice(getOrderTotal(item)) }}</span>
      </template>

      <template #cell-stock="{ item }">
        <span :class="['text-xs font-medium', getStockStatus(item).color]">
          {{ getStockStatus(item).text }}
        </span>
      </template>

      <template #cell-status="{ value }">
        <span
          :class="['px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold', getStatusInfo(value).color]">
          {{ getStatusInfo(value).text }}
        </span>
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-400 text-xs">{{ formatDate(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <template v-if="canApproveOrders">
            <!-- Confirm: pending -> confirmed -->
            <DAction v-if="item.status === 'pending' || item.status === 'draft'" icon="approve" title="Xác nhận đơn"
              variant="success" @click.stop="handleConfirmOrder(item)" :loading="isUpdating === item.id" />

            <!-- Deliver: confirmed/processing -> delivered -->
            <DAction v-if="item.status === 'confirmed' || item.status === 'processing'" icon="edit"
              title="Đánh dấu đã giao" variant="primary" @click.stop="handleMarkDelivered(item)"
              :loading="isUpdating === item.id" />

            <!-- Complete: delivered -> completed -->
            <DAction v-if="item.status === 'delivered'" icon="complete" title="Hoàn thành đơn" variant="success"
              @click.stop="handleCompleteOrder(item)" :loading="isUpdating === item.id" />

            <!-- Cancel: any status except completed/cancelled -->
            <DAction v-if="!['completed', 'cancelled'].includes(item.status)" icon="delete" title="Hủy đơn"
              variant="danger" @click.stop="cancelOrder(item.id)" :loading="isUpdating === item.id" />
          </template>
          <DAction icon="view" title="Chi tiết" variant="info" @click.stop="viewOrderDetail(item)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Shipper Modal -->
    <DModal v-model="showShipperModal" title="Phân công nhân viên giao hàng" size="md">
      <div v-if="selectedOrderForShipper" class="space-y-4">
        <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
          <p class="text-xs text-slate-400 mb-1">Đang xử lý đơn hàng:</p>
          <p class="text-white font-bold font-mono">{{ selectedOrderForShipper.order_number }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Chọn Shipper phụ trách</label>
          <select v-model="selectedShipperId" class="form-input">
            <option :value="null">-- Chọn nhân viên --</option>
            <option v-for="shipper in shippers" :key="shipper.id" :value="shipper.id">
              {{ shipper.name }} ({{ shipper.phone }})
            </option>
          </select>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showShipperModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :disabled="!selectedShipperId" @click="confirmAssignShipper">Xác
            nhận phân công</DButton>
        </div>
      </template>
    </DModal>

    <!-- Order Detail Modal -->
    <OrderDetailModal v-model="showOrderDetail" :order="selectedOrder" :isUpdating="isUpdating"
      @confirm="handleConfirmOrder" @deliver="handleMarkDelivered" @complete="handleCompleteOrder"
      @cancel="(order) => cancelOrder(order.id)" />
  </div>
</template>
