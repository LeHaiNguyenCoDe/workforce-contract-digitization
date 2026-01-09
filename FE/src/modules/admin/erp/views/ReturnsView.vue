<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { returnService, type Return } from '@/plugins/api/services'
import BaseModal from '@/components/BaseModal.vue'

// State
const returns = ref<Return[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const showDetailModal = ref(false)
const selectedReturn = ref<Return | null>(null)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const isSaving = ref(false)

// Form for new return
const returnForm = ref({
  order_id: '',
  reason: '',
  notes: ''
})

// Status options
const statusOptions = [
  { value: '', label: 'Tất cả' },
  { value: 'pending', label: 'Chờ duyệt' },
  { value: 'approved', label: 'Đã duyệt' },
  { value: 'rejected', label: 'Từ chối' },
  { value: 'receiving', label: 'Đang nhận hàng' },
  { value: 'completed', label: 'Hoàn thành' },
  { value: 'cancelled', label: 'Đã hủy' }
]

// Computed
const filteredReturns = computed(() => {
  if (!searchQuery.value) return returns.value
  const query = searchQuery.value.toLowerCase()
  return returns.value.filter(r =>
    r.id.toString().includes(query) ||
    r.order_id.toString().includes(query) ||
    r.reason.toLowerCase().includes(query)
  )
})

// Methods
const fetchReturns = async () => {
  try {
    isLoading.value = true
    const response = await returnService.getReturns({
      status: statusFilter.value || undefined,
      search: searchQuery.value || undefined,
      page: currentPage.value,
      per_page: 15
    })
    returns.value = response.data
    totalPages.value = response.last_page
  } catch (error) {
    console.error('Failed to fetch returns:', error)
    // Use mock data for demo
    returns.value = getMockReturns()
  } finally {
    isLoading.value = false
  }
}

const getMockReturns = (): Return[] => [
  {
    id: 1,
    order_id: 1001,
    customer_id: 5,
    status: 'pending',
    reason: 'Sản phẩm bị lỗi',
    notes: 'Khách hàng phản ánh sản phẩm không hoạt động',
    refund_amount: 500000,
    created_at: '2024-12-20T10:00:00Z',
    updated_at: '2024-12-20T10:00:00Z',
    customer: { id: 5, name: 'Nguyễn Văn A', email: 'a@example.com' },
    order: { id: 1001, total_amount: 500000 }
  },
  {
    id: 2,
    order_id: 1002,
    customer_id: 8,
    status: 'approved',
    reason: 'Đổi size',
    notes: 'Khách muốn đổi size L sang XL',
    refund_amount: 0,
    created_at: '2024-12-21T14:30:00Z',
    updated_at: '2024-12-22T09:00:00Z',
    customer: { id: 8, name: 'Trần Thị B', email: 'b@example.com' },
    order: { id: 1002, total_amount: 350000 }
  },
  {
    id: 3,
    order_id: 1003,
    customer_id: 12,
    status: 'completed',
    reason: 'Không đúng mô tả',
    refund_amount: 750000,
    refund_method: 'original',
    created_at: '2024-12-15T08:00:00Z',
    updated_at: '2024-12-18T16:00:00Z',
    customer: { id: 12, name: 'Lê Văn C', email: 'c@example.com' },
    order: { id: 1003, total_amount: 750000 }
  }
]

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    approved: 'bg-info/10 text-info',
    rejected: 'bg-error/10 text-error',
    receiving: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-slate-500/10 text-slate-400'
  }
  return classes[status] || 'bg-slate-500/10 text-slate-400'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    rejected: 'Từ chối',
    receiving: 'Đang nhận hàng',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return labels[status] || status
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const openDetailModal = (returnItem: Return) => {
  selectedReturn.value = returnItem
  showDetailModal.value = true
}

const approveReturn = async (id: number) => {
  if (!confirm('Xác nhận duyệt phiếu trả hàng này?')) return
  try {
    await returnService.approveReturn(id)
    await fetchReturns()
  } catch (error) {
    console.error('Failed to approve:', error)
    // Update mock data
    const item = returns.value.find(r => r.id === id)
    if (item) item.status = 'approved'
  }
}

const rejectReturn = async (id: number) => {
  const reason = prompt('Nhập lý do từ chối:')
  if (!reason) return
  try {
    await returnService.rejectReturn(id, reason)
    await fetchReturns()
  } catch (error) {
    console.error('Failed to reject:', error)
    const item = returns.value.find(r => r.id === id)
    if (item) item.status = 'rejected'
  }
}

const completeReturn = async (id: number) => {
  if (!confirm('Xác nhận hoàn thành phiếu trả hàng?')) return
  try {
    await returnService.completeReturn(id, 'original')
    await fetchReturns()
  } catch (error) {
    console.error('Failed to complete:', error)
    const item = returns.value.find(r => r.id === id)
    if (item) item.status = 'completed'
  }
}

const cancelReturn = async (id: number) => {
  if (!confirm('Xác nhận hủy phiếu trả hàng?')) return
  try {
    await returnService.cancelReturn(id)
    await fetchReturns()
  } catch (error) {
    console.error('Failed to cancel:', error)
    const item = returns.value.find(r => r.id === id)
    if (item) item.status = 'cancelled'
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchReturns()
  }
}

// Lifecycle
onMounted(() => {
  fetchReturns()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Trả hàng / RMA</h1>
        <p class="text-slate-400 mt-1">Xử lý yêu cầu trả hàng và hoàn tiền</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        Tạo phiếu RMA
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
            width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input v-model="searchQuery" @keyup.enter="fetchReturns()" type="text" class="form-input pl-10"
            placeholder="Tìm theo mã đơn, lý do..." />
        </div>
        <select v-model="statusFilter" @change="fetchReturns()" class="form-input w-48">
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
        <button @click="fetchReturns()" class="btn btn-secondary">Tìm kiếm</button>
      </div>
    </div>

    <!-- Table -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Mã RMA</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Đơn hàng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Khách hàng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Lý do</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Hoàn tiền</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày tạo</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="item in filteredReturns" :key="item.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4">
                <span class="font-medium text-white">#RMA-{{ item.id }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-primary-light">#{{ item.order_id }}</span>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="text-white">{{ item.customer?.name || 'N/A' }}</p>
                  <p class="text-xs text-slate-500">{{ item.customer?.email }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-400 max-w-[200px] truncate">{{ item.reason }}</td>
              <td class="px-6 py-4">
                <span v-if="item.refund_amount" class="text-success font-medium">
                  {{ formatPrice(item.refund_amount) }}
                </span>
                <span v-else class="text-slate-500">-</span>
              </td>
              <td class="px-6 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusClass(item.status)]">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-400 text-sm">{{ formatDate(item.created_at) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openDetailModal(item)"
                    class="w-8 h-8 rounded-lg bg-slate-600/20 text-slate-400 hover:bg-slate-600/40 flex items-center justify-center"
                    title="Chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                  <button v-if="item.status === 'pending'" @click="approveReturn(item.id)"
                    class="w-8 h-8 rounded-lg bg-success/10 text-success hover:bg-success/20 flex items-center justify-center"
                    title="Duyệt">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M20 6 9 17l-5-5" />
                    </svg>
                  </button>
                  <button v-if="item.status === 'pending'" @click="rejectReturn(item.id)"
                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                    title="Từ chối">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M18 6 6 18" />
                      <path d="m6 6 12 12" />
                    </svg>
                  </button>
                  <button v-if="item.status === 'approved' || item.status === 'receiving'" @click="completeReturn(item.id)"
                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 flex items-center justify-center"
                    title="Hoàn thành">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10" />
                      <path d="m9 12 2 2 4-4" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!filteredReturns.length" class="py-16 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>
          <p class="text-slate-400">Không có phiếu trả hàng nào</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="border-t border-white/10 p-4 flex items-center justify-between">
        <span class="text-sm text-slate-400">Trang {{ currentPage }} / {{ totalPages }}</span>
        <div class="flex gap-2">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1" class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
            Trước
          </button>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
            Sau
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <BaseModal v-model="showDetailModal" title="Chi tiết phiếu RMA" size="lg">
      <div v-if="selectedReturn" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-400 mb-1">Mã RMA</label>
            <p class="text-white font-medium">#RMA-{{ selectedReturn.id }}</p>
          </div>
          <div>
            <label class="block text-sm text-slate-400 mb-1">Mã đơn hàng</label>
            <p class="text-primary-light">#{{ selectedReturn.order_id }}</p>
          </div>
          <div>
            <label class="block text-sm text-slate-400 mb-1">Khách hàng</label>
            <p class="text-white">{{ selectedReturn.customer?.name }}</p>
          </div>
          <div>
            <label class="block text-sm text-slate-400 mb-1">Trạng thái</label>
            <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusClass(selectedReturn.status)]">
              {{ getStatusLabel(selectedReturn.status) }}
            </span>
          </div>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Lý do trả hàng</label>
          <p class="text-white">{{ selectedReturn.reason }}</p>
        </div>
        <div v-if="selectedReturn.notes">
          <label class="block text-sm text-slate-400 mb-1">Ghi chú</label>
          <p class="text-slate-300">{{ selectedReturn.notes }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-400 mb-1">Số tiền hoàn</label>
            <p class="text-success font-semibold">{{ formatPrice(selectedReturn.refund_amount || 0) }}</p>
          </div>
          <div>
            <label class="block text-sm text-slate-400 mb-1">Phương thức</label>
            <p class="text-white">{{ selectedReturn.refund_method || 'Chưa xác định' }}</p>
          </div>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showDetailModal = false" class="btn btn-secondary flex-1">Đóng</button>
        </div>
      </div>
    </BaseModal>

    <!-- Create Modal -->
    <BaseModal v-model="showModal" title="Tạo phiếu RMA mới" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Mã đơn hàng *</label>
          <input v-model="returnForm.order_id" type="text" class="form-input" placeholder="Nhập mã đơn hàng" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Lý do trả hàng *</label>
          <select v-model="returnForm.reason" class="form-input">
            <option value="">Chọn lý do</option>
            <option value="Sản phẩm bị lỗi">Sản phẩm bị lỗi</option>
            <option value="Không đúng mô tả">Không đúng mô tả</option>
            <option value="Đổi size/màu">Đổi size/màu</option>
            <option value="Khách đổi ý">Khách đổi ý</option>
            <option value="Khác">Khác</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
          <textarea v-model="returnForm.notes" rows="3" class="form-input"
            placeholder="Thông tin bổ sung..."></textarea>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button class="btn btn-primary flex-1" :disabled="!returnForm.order_id || !returnForm.reason">
            Tạo phiếu
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
