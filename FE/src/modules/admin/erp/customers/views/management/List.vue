<script setup lang="ts">
import { customerColumns } from '../../configs/columns'
import DetailModal from '../modals/DetailModal.vue'

const {
  isLoading,
  currentPage,
  totalPages,
  searchQuery,
  showDetailModal,
  selectedCustomer,
  customerOrders,
  filteredCustomers,
  stats,
  openDetail,
  closeDetail,
  setSearch,
  changePage
} = useCustomers()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Quản lý Khách hàng" description="Theo dõi thông tin, lịch sử mua hàng và doanh thu từ khách hàng" />

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 flex-shrink-0">
      <DCard class="relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
          <img src="@/assets/admin/icons/users.svg" class="w-12 h-12" alt="Users" />
        </div>
        <p class="text-slate-400 text-sm font-medium">Tổng khách hàng</p>
        <p class="text-3xl font-bold text-white mt-1">{{ stats.total }}</p>
      </DCard>
      <DCard class="relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
          <img src="@/assets/admin/icons/activity.svg" class="w-12 h-12" alt="Activity" />
        </div>
        <p class="text-slate-400 text-sm font-medium">Đang hoạt động</p>
        <p class="text-3xl font-bold text-success mt-1">{{ stats.active }}</p>
      </DCard>
      <DCard class="relative overflow-hidden group border-primary/20 bg-primary/5">
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
          <img src="@/assets/admin/icons/dollar-sign.svg" class="w-12 h-12" alt="Revenue" />
        </div>
        <p class="text-slate-400 text-sm font-medium">Tổng doanh thu</p>
        <p class="text-3xl font-bold text-primary-light mt-1">{{ formatPrice(stats.totalSpent) }}</p>
      </DCard>
    </div>

    <!-- Search -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearch" @search="setSearch(searchQuery)" placeholder="Tìm kiếm theo tên, email, số điện thoại..." />

    <!-- Table -->
    <AdminTable :columns="customerColumns" :data="filteredCustomers" :loading="isLoading" empty-text="Không tìm thấy khách hàng nào" @row-click="openDetail">
      <template #cell-id="{ value }">
        <span class="text-xs text-slate-500 font-mono">#{{ value }}</span>
      </template>

      <template #cell-name="{ item }">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-dark-600 flex items-center justify-center text-slate-400 font-bold text-xs border border-white/5">
            {{ item.name?.charAt(0)?.toUpperCase() }}
          </div>
          <span class="font-medium text-white">{{ item.name }}</span>
        </div>
      </template>

      <template #cell-email="{ value }">
        <span class="text-slate-400 text-sm">{{ value }}</span>
      </template>

      <template #cell-total_orders="{ value }">
        <div class="flex flex-col items-center">
          <span class="text-info font-bold">{{ value || 0 }}</span>
          <span class="text-[9px] text-slate-600 uppercase tracking-tighter">Đơn hàng</span>
        </div>
      </template>

      <template #cell-total_spent="{ value }">
        <span class="text-success font-semibold">{{ formatPrice(value || 0) }}</span>
      </template>

      <template #cell-active="{ value }">
        <StatusBadge :status="value ? 'active' : 'inactive'" :text="value ? 'Hoạt động' : 'Tạm khóa'" />
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-500 text-xs">{{ formatDate(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="view" title="Xem chi tiết" @click.stop="openDetail(item)" />
          <DAction icon="edit" title="Sửa" @click.stop="openDetail(item)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Detail Modal -->
    <DetailModal v-model="showDetailModal" :customer="selectedCustomer" :orders="customerOrders" @close="closeDetail" />
  </div>
</template>
