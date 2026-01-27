<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { adminUserService } from '@/plugins/api/services'
import BaseModal from '@/components/BaseModal.vue'

interface Customer {
  id: number
  name: string
  email: string
  phone?: string
  address?: string
  role: string
  active: boolean
  total_orders?: number
  total_spent?: number
  created_at: string
  orders?: any[]
}

// State
const customers = ref<Customer[]>([])
const isLoading = ref(true)
const showDetailModal = ref(false)
const selectedCustomer = ref<Customer | null>(null)
const customerOrders = ref<any[]>([])
const loadingOrders = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

// Computed
const filteredCustomers = computed(() => {
  if (!searchQuery.value) return customers.value
  const query = searchQuery.value.toLowerCase()
  return customers.value.filter(c =>
    c.name.toLowerCase().includes(query) ||
    c.email.toLowerCase().includes(query) ||
    (c.phone && c.phone.includes(query))
  )
})

// Methods
const fetchCustomers = async () => {
  try {
    isLoading.value = true
    const response = await adminUserService.getAll({
      search: searchQuery.value || undefined,
      page: currentPage.value,
      per_page: 15
    })
    // Filter only customers (not admin/manager)
    customers.value = (response.data || response).filter((u: any) => u.role === 'customer' || !u.role)
    if (response.last_page) totalPages.value = response.last_page
  } catch (error) {
    console.error('Failed to fetch customers:', error)
    // Use mock data
    customers.value = getMockCustomers()
  } finally {
    isLoading.value = false
  }
}

const getMockCustomers = (): Customer[] => [
  {
    id: 1,
    name: 'Nguyễn Văn An',
    email: 'an.nguyen@email.com',
    phone: '0901234567',
    address: '123 Nguyễn Huệ, Q.1, TP.HCM',
    role: 'customer',
    active: true,
    total_orders: 15,
    total_spent: 12500000,
    created_at: '2024-01-15T10:00:00Z'
  },
  {
    id: 2,
    name: 'Trần Thị Bình',
    email: 'binh.tran@email.com',
    phone: '0912345678',
    address: '456 Lê Lợi, Q.3, TP.HCM',
    role: 'customer',
    active: true,
    total_orders: 8,
    total_spent: 5600000,
    created_at: '2024-03-20T14:30:00Z'
  },
  {
    id: 3,
    name: 'Lê Hoàng Cường',
    email: 'cuong.le@email.com',
    phone: '0923456789',
    role: 'customer',
    active: true,
    total_orders: 3,
    total_spent: 2100000,
    created_at: '2024-06-10T09:00:00Z'
  },
  {
    id: 4,
    name: 'Phạm Thu Dung',
    email: 'dung.pham@email.com',
    phone: '0934567890',
    address: '789 Điện Biên Phủ, Q.Bình Thạnh, TP.HCM',
    role: 'customer',
    active: false,
    total_orders: 1,
    total_spent: 350000,
    created_at: '2024-08-05T16:45:00Z'
  },
  {
    id: 5,
    name: 'Hoàng Minh Đức',
    email: 'duc.hoang@email.com',
    phone: '0945678901',
    role: 'customer',
    active: true,
    total_orders: 25,
    total_spent: 28900000,
    created_at: '2023-11-20T11:20:00Z'
  }
]

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const openDetailModal = async (customer: Customer) => {
  selectedCustomer.value = customer
  showDetailModal.value = true
  loadingOrders.value = true

  // Mock orders data
  setTimeout(() => {
    customerOrders.value = [
      { id: 1001, total_amount: 500000, status: 'delivered', created_at: '2024-12-20T10:00:00Z' },
      { id: 1002, total_amount: 350000, status: 'processing', created_at: '2024-12-22T14:30:00Z' },
      { id: 1003, total_amount: 750000, status: 'delivered', created_at: '2024-12-15T08:00:00Z' }
    ]
    loadingOrders.value = false
  }, 500)
}

const getOrderStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    processing: 'bg-info/10 text-info',
    shipped: 'bg-primary/10 text-primary',
    delivered: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error'
  }
  return classes[status] || 'bg-slate-500/10 text-slate-400'
}

const getOrderStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    shipped: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy'
  }
  return labels[status] || status
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchCustomers()
  }
}

// Lifecycle
onMounted(() => {
  fetchCustomers()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Quản lý Khách hàng</h1>
        <p class="text-slate-400 mt-1">Danh sách và thông tin khách hàng</p>
      </div>
    </div>

    <!-- Search -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
            width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input v-model="searchQuery" @keyup.enter="fetchCustomers()" type="text" class="form-input pl-10"
            placeholder="Tìm theo tên, email, số điện thoại..." />
        </div>
        <button @click="fetchCustomers()" class="btn btn-secondary">Tìm kiếm</button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6 flex-shrink-0">
      <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" class="text-primary">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ customers.length }}</p>
            <p class="text-sm text-slate-400">Tổng khách hàng</p>
          </div>
        </div>
      </div>
      <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-success/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" class="text-success">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ customers.filter(c => c.active).length }}</p>
            <p class="text-sm text-slate-400">Đang hoạt động</p>
          </div>
        </div>
      </div>
      <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-info/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" class="text-info">
              <rect width="16" height="16" x="4" y="4" rx="2" />
              <path d="M4 10h16" />
              <path d="M12 4v6" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ customers.reduce((sum, c) => sum + (c.total_orders || 0), 0) }}</p>
            <p class="text-sm text-slate-400">Tổng đơn hàng</p>
          </div>
        </div>
      </div>
      <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-warning/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" class="text-warning">
              <line x1="12" x2="12" y1="2" y2="22" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ formatPrice(customers.reduce((sum, c) => sum + (c.total_spent || 0), 0)) }}</p>
            <p class="text-sm text-slate-400">Tổng doanh thu</p>
          </div>
        </div>
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
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Khách hàng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Liên hệ</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tổng đơn</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tổng chi tiêu</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày đăng ký</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-semibold">
                    {{ customer.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-white">{{ customer.name }}</p>
                    <p class="text-xs text-slate-500">#{{ customer.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="text-slate-300">{{ customer.email }}</p>
                  <p class="text-sm text-slate-500">{{ customer.phone || '-' }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-white font-medium">{{ customer.total_orders || 0 }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-success font-medium">{{ formatPrice(customer.total_spent || 0) }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2.5 py-1 rounded-full text-xs font-medium',
                  customer.active ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400'
                ]">
                  {{ customer.active ? 'Hoạt động' : 'Không HĐ' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-400 text-sm">{{ formatDate(customer.created_at) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openDetailModal(customer)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                    title="Xem chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!filteredCustomers.length" class="py-16 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
          <p class="text-slate-400">Không tìm thấy khách hàng</p>
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
    <BaseModal v-model="showDetailModal" title="Chi tiết khách hàng" size="lg">
      <div v-if="selectedCustomer" class="space-y-6">
        <!-- Customer Info -->
        <div class="flex items-start gap-4">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-2xl font-bold">
            {{ selectedCustomer.name.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-semibold text-white">{{ selectedCustomer.name }}</h3>
            <p class="text-slate-400">{{ selectedCustomer.email }}</p>
            <div class="flex gap-4 mt-2">
              <span class="text-sm text-slate-500">📞 {{ selectedCustomer.phone || 'Chưa cập nhật' }}</span>
              <span :class="[
                'px-2 py-0.5 rounded-full text-xs',
                selectedCustomer.active ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400'
              ]">
                {{ selectedCustomer.active ? 'Hoạt động' : 'Không hoạt động' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4">
          <div class="bg-dark-700 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ selectedCustomer.total_orders || 0 }}</p>
            <p class="text-sm text-slate-400">Đơn hàng</p>
          </div>
          <div class="bg-dark-700 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ formatPrice(selectedCustomer.total_spent || 0) }}</p>
            <p class="text-sm text-slate-400">Tổng chi tiêu</p>
          </div>
          <div class="bg-dark-700 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ formatDate(selectedCustomer.created_at) }}</p>
            <p class="text-sm text-slate-400">Ngày đăng ký</p>
          </div>
        </div>

        <!-- Address -->
        <div v-if="selectedCustomer.address">
          <label class="block text-sm text-slate-400 mb-1">Địa chỉ</label>
          <p class="text-white">{{ selectedCustomer.address }}</p>
        </div>

        <!-- Recent Orders -->
        <div>
          <h4 class="text-lg font-semibold text-white mb-3">Đơn hàng gần đây</h4>
          <div v-if="loadingOrders" class="flex justify-center py-4">
            <div class="w-6 h-6 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div>
          </div>
          <div v-else-if="customerOrders.length" class="space-y-2">
            <div v-for="order in customerOrders" :key="order.id"
              class="flex items-center justify-between bg-dark-700 rounded-lg p-3">
              <div>
                <span class="font-medium text-white">#{{ order.id }}</span>
                <span class="text-sm text-slate-400 ml-2">{{ formatDate(order.created_at) }}</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-success font-medium">{{ formatPrice(order.total_amount) }}</span>
                <span :class="['px-2 py-0.5 rounded-full text-xs', getOrderStatusClass(order.status)]">
                  {{ getOrderStatusLabel(order.status) }}
                </span>
              </div>
            </div>
          </div>
          <p v-else class="text-slate-500 text-center py-4">Chưa có đơn hàng</p>
        </div>

        <div class="flex gap-3 pt-4">
          <button @click="showDetailModal = false" class="btn btn-secondary flex-1">Đóng</button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
