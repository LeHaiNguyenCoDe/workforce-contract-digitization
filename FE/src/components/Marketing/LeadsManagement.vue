<template>
  <div class="leads-management">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Quản Lý Leads</h1>
        <p class="text-gray-600 mt-1">Theo dõi và quản lý khách tiềm năng</p>
      </div>
      <div class="flex gap-3">
        <button @click="showImportModal = true" class="btn btn-outline">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Import
        </button>
        <button @click="openCreateModal" class="btn btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Lead Mới
        </button>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-600 text-sm">Tổng Leads</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_leads }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="card bg-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-600 text-sm">Đã Chuyển Đổi</p>
              <p class="text-2xl font-bold text-green-600">{{ stats.by_status?.converted || 0 }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="card bg-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-600 text-sm">Tỷ Lệ Chuyển Đổi</p>
              <p class="text-2xl font-bold text-orange-600">{{ stats.conversion_rate }}%</p>
            </div>
            <div class="bg-orange-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="card bg-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-600 text-sm">Giá Trị Ước Tính</p>
              <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.total_value) }}</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.16 2.75a.75.75 0 00-.75.75v1.5h-2a.75.75 0 000 1.5h.75v6h-.75a.75.75 0 000 1.5h2v1.5a.75.75 0 001.5 0v-1.5h2v1.5a.75.75 0 001.5 0v-1.5h2a.75.75 0 000-1.5h-.75v-6h.75a.75.75 0 000-1.5h-2v-1.5a.75.75 0 00-1.5 0v1.5h-2v-1.5a.75.75 0 00-.75-.75z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card bg-white mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input v-model="filters.search" type="text" placeholder="Tìm kiếm leads..." class="input input-bordered" />
          <select v-model="filters.status" class="select select-bordered">
            <option value="">Tất cả trạng thái</option>
            <option value="new">Mới</option>
            <option value="contacted">Đã liên hệ</option>
            <option value="qualified">Hợp lệ</option>
            <option value="converted">Đã chuyển đổi</option>
            <option value="lost">Mất</option>
          </select>
          <select v-model="filters.temperature" class="select select-bordered">
            <option value="">Tất cả nhiệt độ</option>
            <option value="hot">Nóng</option>
            <option value="warm">Ấm</option>
            <option value="cold">Lạnh</option>
          </select>
          <button @click="applyFilters" class="btn btn-outline" :disabled="isLoading">
            <svg v-if="isLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Tìm Kiếm
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && leads.length === 0" class="card bg-white p-8 text-center">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
      <p class="text-gray-600">Đang tải dữ liệu...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && leads.length === 0" class="card bg-white p-8 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <p class="text-gray-600 mb-4">Chưa có lead nào</p>
      <button @click="openCreateModal" class="btn btn-primary">Tạo Lead Đầu Tiên</button>
    </div>

    <!-- Leads Table -->
    <div v-else class="card bg-white overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tên</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Điểm</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trạng Thái</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nhiệt Độ</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nguồn</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành Động</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ lead.full_name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ lead.email || '-' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-20 bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" :style="{ width: lead.score + '%' }"></div>
                  </div>
                  <span class="text-sm font-semibold text-gray-900">{{ lead.score }}/100</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusBadge(lead.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(lead.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getTemperatureBadge(lead.temperature)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getTemperatureLabel(lead.temperature) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ lead.source }}</td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <button @click="viewLead(lead)" class="btn btn-sm btn-ghost">Xem</button>
                  <button @click="editLead(lead)" class="btn btn-sm btn-ghost">Sửa</button>
                  <button @click="deleteLead(lead.id)" class="btn btn-sm btn-ghost text-error">Xóa</button>
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
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-2xl max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-lg mb-4">{{ editingLead ? 'Cập Nhật Lead' : 'Tạo Lead Mới' }}</h3>
          <div class="py-4 space-y-4">
            <input v-model="form.full_name" type="text" placeholder="Tên đầy đủ *" class="input input-bordered w-full" />
            <input v-model="form.email" type="email" placeholder="Email" class="input input-bordered w-full" />
            <input v-model="form.phone" type="tel" placeholder="Số điện thoại" class="input input-bordered w-full" />
            <input v-model="form.company" type="text" placeholder="Công ty" class="input input-bordered w-full" />
            <select v-model="form.source" class="select select-bordered w-full">
              <option value="">Chọn nguồn</option>
              <option value="website">Website</option>
              <option value="facebook">Facebook</option>
              <option value="google">Google</option>
              <option value="referral">Giới thiệu</option>
              <option value="event">Sự kiện</option>
              <option value="cold_call">Cold Call</option>
              <option value="landing_page">Landing Page</option>
            </select>
            <textarea v-model="form.notes" placeholder="Ghi chú" class="textarea textarea-bordered w-full"></textarea>
          </div>
          <div class="flex gap-2 justify-end mt-6">
            <button @click="closeModal" class="btn" :disabled="isSaving">Hủy</button>
            <button @click="saveLead" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="loading loading-spinner loading-sm"></span>
              {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Import Modal -->
    <Teleport to="body" v-if="showImportModal">
      <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="showImportModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md">
          <h3 class="font-bold text-lg mb-4">Import Leads từ CSV</h3>
          <div class="py-4">
            <input type="file" accept=".csv" class="file-input file-input-bordered w-full" @change="handleFileSelect" ref="fileInput" />
          </div>
          <div class="flex gap-2 justify-end mt-6">
            <button @click="showImportModal = false" class="btn" :disabled="isSaving">Hủy</button>
            <button @click="handleImport" class="btn btn-primary" :disabled="isSaving || !selectedFile">
              <span v-if="isSaving" class="loading loading-spinner loading-sm"></span>
              {{ isSaving ? 'Đang upload...' : 'Upload' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLeads } from '@/modules/admin/marketing/composables/useLeads'

const {
  leads, stats, isLoading, isSaving, currentPage, lastPage, filters, form, editingLead,
  showCreateModal, showImportModal,
  fetchLeads, fetchStats, saveLead, deleteLead, viewLead, editLead,
  openCreateModal, closeModal, changePage, applyFilters, importLeads,
  getStatusLabel, getStatusBadge, getTemperatureLabel, getTemperatureBadge
} = useLeads()

const selectedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0]
  }
}

async function handleImport() {
  if (selectedFile.value) {
    await importLeads(selectedFile.value)
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0)
}

onMounted(() => {
  fetchLeads()
  fetchStats()
})
</script>

<style scoped>
.leads-management { @apply p-6; }
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
