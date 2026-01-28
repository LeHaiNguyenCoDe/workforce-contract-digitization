<template>
  <div class="coupons-management">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Quản Lý Mã Giảm Giá</h1>
        <p class="text-gray-600 mt-1">Tạo và quản lý các chương trình khuyến mãi</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Mã Mới
      </button>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Tổng Mã</p>
          <p class="text-2xl font-bold text-gray-900">{{ stats.total_coupons }}</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Đã Sử Dụng</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.total_usages }}</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Tỷ Lệ Sử Dụng</p>
          <p class="text-2xl font-bold text-blue-600">{{ stats.usage_rate }}%</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Tổng Giảm Giá</p>
          <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.total_discount_given) }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card bg-white mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input v-model="filters.search" type="text" placeholder="Tìm mã..." class="input input-bordered" />
          <select v-model="filters.type" class="select select-bordered">
            <option value="">Tất cả loại</option>
            <option value="percentage">Phần trăm (%)</option>
            <option value="fixed">Giá cố định</option>
            <option value="bxgy">Buy X Get Y</option>
            <option value="free_shipping">Miễn phí vận chuyển</option>
          </select>
          <select v-model="filters.status" class="select select-bordered">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Hoạt động</option>
            <option value="inactive">Không hoạt động</option>
            <option value="expired">Hết hạn</option>
          </select>
          <button @click="applyFilters" class="btn btn-outline" :disabled="isLoading">
            <svg v-if="isLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Tìm Kiếm
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && coupons.length === 0" class="card bg-white p-8 text-center">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
      <p class="text-gray-600">Đang tải dữ liệu...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && coupons.length === 0" class="card bg-white p-8 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
      </svg>
      <p class="text-gray-600 mb-4">Chưa có mã giảm giá nào</p>
      <button @click="openCreateModal" class="btn btn-primary">Tạo Mã Đầu Tiên</button>
    </div>

    <!-- Coupons Table -->
    <div v-else class="card bg-white overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Mã</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tên</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Loại</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Giá Trị</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Sử Dụng</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hạn Sử Dụng</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trạng Thái</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành Động</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="coupon in coupons" :key="coupon.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-mono font-bold text-gray-900">{{ coupon.code }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ coupon.name }}</td>
              <td class="px-6 py-4">
                <span :class="getTypeBadge(coupon.type)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getTypeLabel(coupon.type) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                {{ coupon.type === 'percentage' ? coupon.value + '%' : formatCurrency(coupon.value) }}
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-600">{{ coupon.usage_count }} / {{ coupon.usage_limit_total || '∞' }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ coupon.valid_to ? formatDate(coupon.valid_to) : '∞' }}</td>
              <td class="px-6 py-4">
                <span :class="coupon.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ coupon.is_active ? 'Hoạt động' : 'Không hoạt động' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <button @click="editCoupon(coupon)" class="btn btn-sm btn-ghost">Sửa</button>
                  <button @click="generateCodes(coupon.id)" class="btn btn-sm btn-ghost">Tạo mã</button>
                  <button @click="deleteCoupon(coupon.id)" class="btn btn-sm btn-ghost text-error">Xóa</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="mt-6 flex justify-center gap-2">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="btn btn-sm btn-outline">Trước</button>
      <template v-for="page in lastPage" :key="page">
        <button
          v-if="page === 1 || page === lastPage || (page >= currentPage - 1 && page <= currentPage + 1)"
          @click="changePage(page)"
          :class="['btn btn-sm', page === currentPage ? 'btn-active' : 'btn-outline']"
        >{{ page }}</button>
        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-2">...</span>
      </template>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="btn btn-sm btn-outline">Tiếp</button>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body" v-if="showCreateModal">
      <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-4xl max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-lg mb-4">{{ editingCoupon ? 'Cập Nhật Mã' : 'Tạo Mã Mới' }}</h3>
          <div class="py-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <input v-model="form.code" type="text" placeholder="Mã coupon *" class="input input-bordered" :disabled="!!editingCoupon" />
              <input v-model="form.name" type="text" placeholder="Tên *" class="input input-bordered" />
            </div>

            <textarea v-model="form.description" placeholder="Mô tả" class="textarea textarea-bordered w-full"></textarea>

            <div class="grid grid-cols-3 gap-4">
              <select v-model="form.type" class="select select-bordered">
                <option value="percentage">Phần trăm (%)</option>
                <option value="fixed">Giá cố định</option>
                <option value="bxgy">Buy X Get Y</option>
                <option value="free_shipping">Miễn phí vận chuyển</option>
              </select>
              <input v-model.number="form.value" type="number" placeholder="Giá trị" class="input input-bordered" />
              <input v-model.number="form.min_purchase_amount" type="number" placeholder="Mua tối thiểu" class="input input-bordered" />
            </div>

            <div class="grid grid-cols-3 gap-4">
              <input v-model.number="form.usage_limit_total" type="number" placeholder="Giới hạn tổng" class="input input-bordered" />
              <input v-model.number="form.usage_limit_per_user" type="number" placeholder="Giới hạn/người" class="input input-bordered" />
              <input v-model.number="form.usage_limit_per_day" type="number" placeholder="Giới hạn/ngày" class="input input-bordered" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                <input v-model="form.valid_from" type="date" class="input input-bordered w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                <input v-model="form.valid_to" type="date" class="input input-bordered w-full" />
              </div>
            </div>

            <div class="flex gap-4 flex-wrap">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.auto_apply" type="checkbox" class="checkbox" />
                <span>Tự động áp dụng</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.stackable" type="checkbox" class="checkbox" />
                <span>Có thể kết hợp</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.first_order_only" type="checkbox" class="checkbox" />
                <span>Đơn hàng đầu tiên</span>
              </label>
            </div>
          </div>
          <div class="flex gap-2 justify-end mt-6">
            <button @click="closeModal" class="btn" :disabled="isSaving">Hủy</button>
            <button @click="saveCoupon" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="loading loading-spinner loading-sm"></span>
              {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useCoupons } from '@/modules/admin/marketing/composables/useCoupons'

const {
  coupons, stats, isLoading, isSaving, currentPage, lastPage, filters, form, editingCoupon,
  showCreateModal,
  fetchCoupons, fetchStats, saveCoupon, deleteCoupon, editCoupon,
  openCreateModal, closeModal, generateCodes, changePage, applyFilters,
  getTypeLabel, getTypeBadge, formatCurrency
} = useCoupons()

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

onMounted(() => {
  fetchCoupons()
  fetchStats()
})
</script>

<style scoped>
.coupons-management { @apply p-6; }
.card { @apply rounded-lg shadow bg-white; }
.card-body { @apply p-6; }
.btn { @apply px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-outline { @apply border border-gray-300 text-gray-700 hover:bg-gray-50; }
.btn-ghost { @apply text-gray-600 hover:bg-gray-100; }
.btn-active { @apply bg-blue-600 text-white; }
.btn-sm { @apply px-3 py-1 text-sm; }
.btn:disabled { @apply opacity-50 cursor-not-allowed; }
.input, .select, .textarea { @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500; }
.checkbox { @apply w-4 h-4 text-blue-600 rounded border-gray-300; }
.text-error { @apply text-red-600; }
</style>
