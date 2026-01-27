<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useReturnConfigs } from '../../configs/columns'
import DetailModal from '../modals/DetailModal.vue'
import { useReturns } from '../../composables/useReturns'
import DSelect from '@/components/UI/DSelect.vue'
import { formatPrice, formatDateTime } from '@/utils'

const { t } = useI18n()
const { columns, statusOptions, reasonOptions, statusLabels } = useReturnConfigs()

const {
  isLoading,
  isSaving,
  currentPage,
  totalPages,
  searchQuery,
  statusFilter,
  showDetailModal,
  showCreateModal,
  selectedReturn,
  availableOrders,
  selectedOrder,
  form,
  filteredReturns,
  openDetail,
  closeDetail,
  openCreate,
  handleCreate,
  handleApprove,
  handleReject,
  handleComplete,
  setSearch,
  setStatusFilter,
  changePage
} = useReturns()

const handleStatusChange = (e: Event) => {
  const target = e.target as HTMLSelectElement
  setStatusFilter(target.value as any)
}

const orderOptions = computed(() => {
  return availableOrders.value.map(order => ({
    value: order.id,
    label: `#${order.code || order.order_number || order.id} - ${order.full_name || order.customer?.name || 'N/A'}`
  }))
})

const getItemProduct = (orderItemId: number) => {
  return selectedOrder.value?.items?.find((i: any) => i.id === orderItemId)?.product
}

const getItemPrice = (orderItemId: number) => {
  return selectedOrder.value?.items?.find((i: any) => i.id === orderItemId)?.price || 0
}

const getItemTotal = (item: any) => {
  return getItemPrice(item.order_item_id) * item.quantity
}
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader :title="t('admin.returns')" :description="t('admin.manageReturns')">
      <template #actions>
        <DButton variant="primary" @click="openCreate">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          {{ t('admin.createReturn') }}
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Search & Filters -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearch" @search="setSearch(searchQuery)"
      :placeholder="t('admin.searchReturnsPlaceholder')">
      <template #filters>
        <div class="flex gap-2">
          <select :value="statusFilter" @change="handleStatusChange"
            class="form-input w-44 bg-dark-700 border-white/10 text-white">
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <DButton variant="secondary" @click="setSearch(searchQuery)" :title="t('admin.refresh')">
            <img src="@/assets/admin/icons/refresh-cw.svg" class="w-4 h-4 brightness-0 invert opacity-70"
              alt="Refresh" />
          </DButton>
        </div>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="columns" :data="filteredReturns" :loading="isLoading" :empty-text="t('admin.noReturns')"
      @row-click="openDetail">
      <template #cell-id="{ value }">
        <span class="font-bold text-white text-xs">#RMA-{{ value }}</span>
      </template>

      <template #cell-order_id="{ value }">
        <span class="text-primary-light font-medium">#{{ value }}</span>
      </template>

      <template #cell-customer="{ item }">
        <div class="min-w-0">
          <p class="text-white font-medium truncate">{{ item.customer?.name || 'N/A' }}</p>
          <p class="text-[10px] text-slate-500 truncate">{{ item.customer?.email }}</p>
        </div>
      </template>

      <template #cell-reason="{ value }">
        <span class="text-slate-400 text-xs line-clamp-1" :title="value">{{ value }}</span>
      </template>

      <template #cell-refund_amount="{ value }">
        <span v-if="value" class="text-success font-semibold">
          {{ formatPrice(value) }}
        </span>
        <span v-else class="text-slate-600">-</span>
      </template>

      <template #cell-status="{ value }">
        <StatusBadge :status="value" :text="statusLabels[value] || value" />
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-500 text-[11px]">{{ formatDateTime(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="view" :title="t('admin.viewDetails')" @click.stop="openDetail(item)" />
          <template v-if="item.status === 'pending'">
            <DAction icon="approve" :title="t('admin.approveAction')" variant="success"
              @click.stop="handleApprove(item.id)" />
            <DAction icon="reject" :title="t('admin.rejectAction')" variant="danger"
              @click.stop="handleReject(item.id)" />
          </template>
          <DAction v-if="item.status === 'approved'" icon="complete" :title="t('common.complete')" variant="info"
            @click.stop="handleComplete(item.id)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Details -->
    <DetailModal v-model="showDetailModal" :item="selectedReturn" @close="closeDetail" />

    <!-- Create RMA -->
    <DModal v-model="showCreateModal" :title="t('admin.createReturn')" size="md">
      <div class="space-y-4">
        <DSelect v-model="form.order_id" :label="t('admin.selectOrder') + ' *'" :options="orderOptions"
          :placeholder="t('admin.searchSelectOrder')" />

        <div v-if="selectedOrder" class="grid grid-cols-2 gap-4">
          <DSelect v-model="form.type" :label="t('admin.returnType') + ' *'" :options="[
            { value: 'return', label: t('admin.returnOnly') },
            { value: 'exchange', label: t('admin.exchangeOnly') },
            { value: 'refund_only', label: t('admin.refundOnly') }
          ]" />
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.reason') }} *</label>
            <select v-model="form.reason" class="form-input">
              <option value="" disabled>{{ t('admin.selectReason') }}</option>
              <option v-for="opt in reasonOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
        </div>

        <div v-if="selectedOrder && form.items.length"
          class="border border-white/10 rounded-lg overflow-hidden bg-dark-900/50">
          <div class="bg-dark-700/50 px-3 py-2 border-b border-white/10 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ t('admin.returnedProducts')
            }}</span>
            <span class="text-[10px] text-slate-500">{{ form.items.length }} {{ t('admin.itemsCount') }}</span>
          </div>
          <div class="max-h-[200px] overflow-auto divide-y divide-white/5">
            <div v-for="item in form.items" :key="item.order_item_id" class="px-3 py-3 flex items-center gap-3">
              <div class="flex-1 min-w-0">
                <p class="text-sm text-white truncate font-medium">
                  {{ getItemProduct(item.order_item_id)?.name || t('common.product') }}
                </p>
                <div class="flex items-center gap-4 mt-1">
                  <div class="flex items-center gap-2">
                    <span class="text-[10px] text-slate-500">{{ t('common.quantity') }}:</span>
                    <input v-model.number="item.quantity" type="number" min="1"
                      :max="selectedOrder.items?.find((i: any) => i.id === item.order_item_id)?.quantity"
                      class="w-16 h-7 text-xs bg-dark-700 border-white/10 rounded px-2 text-white focus:border-primary/50 outline-none" />
                  </div>
                  <span class="text-[10px] text-primary-light">
                    {{ formatPrice(getItemPrice(item.order_item_id)) }} / {{ t('admin.pricePerUnit') }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <p class="text-xs font-semibold text-white">
                  {{ formatPrice(getItemTotal(item)) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.additionalNotes') }}</label>
          <textarea v-model="form.notes" class="form-input min-h-[80px]"
            :placeholder="t('admin.notesPlaceholder')"></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showCreateModal = false">{{ t('common.close') }}</DButton>
          <DButton variant="primary" class="flex-1" :disabled="isSaving || !form.order_id || !form.reason || !form.type"
            @click="handleCreate" :loading="isSaving">
            {{ t('admin.createReturn') }}
          </DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
