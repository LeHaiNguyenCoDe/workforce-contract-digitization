<script setup lang="ts">
import { returnColumns, returnStatusOptions, returnStatusLabels } from '../../configs/columns'
import DetailModal from '../modals/DetailModal.vue'

const {
  isLoading,
  currentPage,
  totalPages,
  searchQuery,
  statusFilter,
  showDetailModal,
  showCreateModal,
  selectedReturn,
  form,
  filteredReturns,
  openDetail,
  closeDetail,
  openCreate,
  handleApprove,
  handleReject,
  handleComplete,
  setSearch,
  setStatusFilter,
  changePage
} = useReturns()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Trả hàng / RMA" description="Quản lý và xử lý các yêu cầu đổi trả hàng từ khách hàng">
      <template #actions>
        <DButton variant="primary" @click="openCreate">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          Tạo phiếu RMA
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Search & Filters -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearch" @search="setSearch(searchQuery)" placeholder="Tìm theo mã RMA, mã đơn hàng, lý do...">
      <template #filters>
        <div class="flex gap-2">
          <select :value="statusFilter" @change="setStatusFilter(($event.target as HTMLSelectElement).value)" class="form-input w-44 bg-dark-700 border-white/10 text-white">
            <option v-for="opt in returnStatusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <DButton variant="secondary" @click="setSearch(searchQuery)" title="Làm mới">
            <img src="@/assets/admin/icons/refresh-cw.svg" class="w-4 h-4 brightness-0 invert opacity-70" alt="Refresh" />
          </DButton>
        </div>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="returnColumns" :data="filteredReturns" :loading="isLoading" empty-text="Không có phiếu trả hàng nào" @row-click="openDetail">
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
        <StatusBadge :status="value" :text="returnStatusLabels[value] || value" />
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-500 text-[11px]">{{ formatDateTime(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="view" title="Chi tiết" @click.stop="openDetail(item)" />
          <template v-if="item.status === 'pending'">
            <DAction icon="approve" title="Duyệt" variant="success" @click.stop="handleApprove(item.id)" />
            <DAction icon="cancel" title="Từ chối" variant="danger" @click.stop="handleReject(item.id)" />
          </template>
          <DAction v-if="item.status === 'approved'" icon="complete" title="Hoàn thành" variant="info" @click.stop="handleComplete(item.id)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Details -->
    <DetailModal v-model="showDetailModal" :item="selectedReturn" @close="closeDetail" />

    <!-- Create RMA -->
    <DModal v-model="showCreateModal" title="Tạo phiếu RMA mới" size="md">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Mã đơn hàng *</label>
          <input v-model="form.order_id" type="number" class="form-input" placeholder="Nhập mã đơn hàng" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Lý do trả hàng *</label>
          <select v-model="form.reason" class="form-input">
            <option value="" disabled>Chọn lý do</option>
            <option value="Sản phẩm bị lỗi">Sản phẩm bị lỗi</option>
            <option value="Không đúng mô tả">Không đúng mô tả</option>
            <option value="Đổi size/màu">Đổi size/màu</option>
            <option value="Khách đổi ý">Khách đổi ý</option>
            <option value="Khác">Khác</option>
          </select>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú bổ sung</label>
          <textarea v-model="form.notes" class="form-input min-h-[100px]" placeholder="Nhập chi tiết cụ thể..."></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showCreateModal = false">Đóng</DButton>
          <DButton variant="primary" class="flex-1" :disabled="!form.order_id || !form.reason">Tạo phiếu RMA</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
