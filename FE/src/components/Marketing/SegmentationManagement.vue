<template>
  <div class="segmentation-management">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Phân Khúc Khách Hàng</h1>
        <p class="text-gray-600 mt-1">Chia khách hàng thành các nhóm có đặc điểm chung</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Segment Mới
      </button>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Tổng Segment</p>
          <p class="text-2xl font-bold text-gray-900">{{ stats.total_segments }}</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Segment Động</p>
          <p class="text-2xl font-bold text-blue-600">{{ stats.dynamic_segments }}</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Segment Tĩnh</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.static_segments }}</p>
        </div>
      </div>
      <div class="card bg-white">
        <div class="card-body">
          <p class="text-gray-600 text-sm">Trung Bình Size</p>
          <p class="text-2xl font-bold text-purple-600">{{ stats.average_segment_size }}</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && segments.length === 0" class="card bg-white p-8 text-center">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
      <p class="text-gray-600">Đang tải dữ liệu...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && segments.length === 0" class="card bg-white p-8 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
      </svg>
      <p class="text-gray-600 mb-4">Chưa có phân khúc nào</p>
      <button @click="openCreateModal" class="btn btn-primary">Tạo Phân Khúc Đầu Tiên</button>
    </div>

    <!-- Segments Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div v-for="segment in segments" :key="segment.id" class="card bg-white hover:shadow-lg transition-shadow">
        <div class="card-body">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-bold text-lg text-gray-900">{{ segment.name }}</h3>
              <p class="text-sm text-gray-600">{{ segment.description || 'Không có mô tả' }}</p>
            </div>
            <span :style="{ backgroundColor: segment.color }" class="w-8 h-8 rounded-full flex-shrink-0"></span>
          </div>

          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Loại:</span>
              <span :class="segment.type === 'dynamic' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 rounded text-xs font-semibold">
                {{ segment.type === 'dynamic' ? 'Động' : 'Tĩnh' }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Khách hàng:</span>
              <span class="font-semibold text-gray-900">{{ segment.customers_count }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Trạng thái:</span>
              <span :class="segment.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded text-xs font-semibold">
                {{ segment.is_active ? 'Hoạt động' : 'Không hoạt động' }}
              </span>
            </div>
          </div>

          <div class="flex gap-2">
            <button @click="viewSegment(segment)" class="btn btn-sm btn-ghost flex-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              Xem
            </button>
            <button @click="editSegment(segment)" class="btn btn-sm btn-ghost flex-1">Sửa</button>
            <button @click="deleteSegment(segment.id)" class="btn btn-sm btn-ghost flex-1 text-error">Xóa</button>
          </div>
        </div>
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
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-2xl max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-lg mb-4">{{ editingSegment ? 'Cập Nhật Segment' : 'Tạo Segment Mới' }}</h3>
          <div class="py-4 space-y-4">
            <input v-model="form.name" type="text" placeholder="Tên segment *" class="input input-bordered w-full" />
            <textarea v-model="form.description" placeholder="Mô tả" class="textarea textarea-bordered w-full"></textarea>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Loại</label>
                <select v-model="form.type" class="select select-bordered w-full">
                  <option value="static">Tĩnh (Manual)</option>
                  <option value="dynamic">Động (Auto)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Màu sắc</label>
                <input v-model="form.color" type="color" class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer" />
              </div>
            </div>

            <div v-if="form.type === 'dynamic'" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Điều Kiện (JSON)</label>
              <textarea v-model="form.conditions" placeholder='[{"field": "total_orders", "operator": "greater_than", "value": 5}]' class="textarea textarea-bordered w-full font-mono text-xs h-32"></textarea>
              <p class="text-xs text-gray-500">Nhập điều kiện dạng JSON array. Ví dụ: [{"field": "total_orders", "operator": "greater_than", "value": 5}]</p>
            </div>
          </div>
          <div class="flex gap-2 justify-end mt-6">
            <button @click="closeModal" class="btn" :disabled="isSaving">Hủy</button>
            <button @click="saveSegment" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="loading loading-spinner loading-sm"></span>
              {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Segment Modal -->
    <Teleport to="body" v-if="showViewModal && selectedSegment">
      <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-4xl max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-lg mb-4">{{ selectedSegment.name }} - Khách Hàng ({{ selectedSegment.customers_count }})</h3>

          <div v-if="segmentCustomers.length === 0" class="text-center py-8 text-gray-500">
            Chưa có khách hàng trong phân khúc này
          </div>

          <div v-else class="overflow-x-auto mb-4">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left">Tên</th>
                  <th class="px-4 py-2 text-left">Email</th>
                  <th class="px-4 py-2 text-left">Điện Thoại</th>
                  <th class="px-4 py-2 text-left">Ngày Thêm</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="customer in segmentCustomers" :key="customer.id" class="hover:bg-gray-50">
                  <td class="px-4 py-2">{{ customer.name }}</td>
                  <td class="px-4 py-2">{{ customer.email }}</td>
                  <td class="px-4 py-2">{{ customer.phone || '-' }}</td>
                  <td class="px-4 py-2 text-xs text-gray-600">{{ formatDate(customer.added_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex gap-2 justify-end mt-6">
            <button @click="closeModal" class="btn">Đóng</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useSegments } from '@/modules/admin/marketing/composables/useSegments'

const {
  segments, stats, selectedSegment, segmentCustomers, isLoading, isSaving,
  currentPage, lastPage, form, editingSegment,
  showCreateModal, showViewModal,
  fetchSegments, fetchStats, saveSegment, deleteSegment, viewSegment, editSegment,
  openCreateModal, closeModal, changePage
} = useSegments()

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

onMounted(() => {
  fetchSegments()
  fetchStats()
})
</script>

<style scoped>
.segmentation-management { @apply p-6; }
.card { @apply rounded-lg shadow; }
.card-body { @apply p-6; }
.btn { @apply px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-outline { @apply border border-gray-300 text-gray-700 hover:bg-gray-50; }
.btn-ghost { @apply text-gray-600 hover:bg-gray-100; }
.btn-active { @apply bg-blue-600 text-white; }
.btn-sm { @apply px-3 py-1 text-sm; }
.btn:disabled { @apply opacity-50 cursor-not-allowed; }
.input, .select, .textarea { @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500; }
.text-error { @apply text-red-600; }
</style>
