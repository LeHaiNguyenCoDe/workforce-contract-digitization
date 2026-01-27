<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface AuditLog {
  id: number
  user_id: number
  user_name: string
  action: string
  entity_type: string
  entity_id?: number
  old_values?: Record<string, any>
  new_values?: Record<string, any>
  ip_address: string
  user_agent: string
  created_at: string
}

// State
const logs = ref<AuditLog[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const actionFilter = ref('')
const entityFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const expandedLog = ref<number | null>(null)

// Filter options
const actionOptions = [
  { value: '', label: 'Tất cả hành động' },
  { value: 'create', label: 'Tạo mới' },
  { value: 'update', label: 'Cập nhật' },
  { value: 'delete', label: 'Xóa' },
  { value: 'login', label: 'Đăng nhập' },
  { value: 'logout', label: 'Đăng xuất' }
]

const entityOptions = [
  { value: '', label: 'Tất cả đối tượng' },
  { value: 'user', label: 'Người dùng' },
  { value: 'product', label: 'Sản phẩm' },
  { value: 'order', label: 'Đơn hàng' },
  { value: 'category', label: 'Danh mục' },
  { value: 'warehouse', label: 'Kho hàng' }
]

// Computed
const filteredLogs = computed(() => {
  let result = logs.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(log =>
      log.user_name.toLowerCase().includes(query) ||
      log.action.toLowerCase().includes(query) ||
      log.entity_type.toLowerCase().includes(query)
    )
  }

  if (actionFilter.value) {
    result = result.filter(log => log.action === actionFilter.value)
  }

  if (entityFilter.value) {
    result = result.filter(log => log.entity_type === entityFilter.value)
  }

  return result
})

// Methods
const fetchLogs = async () => {
  isLoading.value = true
  setTimeout(() => {
    logs.value = getMockLogs()
    totalPages.value = 3
    isLoading.value = false
  }, 500)
}

const getMockLogs = (): AuditLog[] => [
  {
    id: 1,
    user_id: 1,
    user_name: 'Admin',
    action: 'update',
    entity_type: 'product',
    entity_id: 15,
    old_values: { price: 500000, name: 'Sản phẩm A' },
    new_values: { price: 550000, name: 'Sản phẩm A - Updated' },
    ip_address: '192.168.1.100',
    user_agent: 'Chrome/120.0.0.0',
    created_at: '2024-12-26T10:30:00Z'
  },
  {
    id: 2,
    user_id: 2,
    user_name: 'Manager Minh',
    action: 'create',
    entity_type: 'order',
    entity_id: 1250,
    new_values: { total: 1500000, status: 'pending' },
    ip_address: '192.168.1.105',
    user_agent: 'Firefox/121.0',
    created_at: '2024-12-26T09:45:00Z'
  },
  {
    id: 3,
    user_id: 1,
    user_name: 'Admin',
    action: 'delete',
    entity_type: 'category',
    entity_id: 8,
    old_values: { name: 'Danh mục test', products_count: 0 },
    ip_address: '192.168.1.100',
    user_agent: 'Chrome/120.0.0.0',
    created_at: '2024-12-26T09:15:00Z'
  },
  {
    id: 4,
    user_id: 3,
    user_name: 'Staff Lan',
    action: 'login',
    entity_type: 'user',
    entity_id: 3,
    ip_address: '192.168.1.110',
    user_agent: 'Safari/17.0',
    created_at: '2024-12-26T08:00:00Z'
  },
  {
    id: 5,
    user_id: 2,
    user_name: 'Manager Minh',
    action: 'update',
    entity_type: 'order',
    entity_id: 1245,
    old_values: { status: 'processing' },
    new_values: { status: 'shipped' },
    ip_address: '192.168.1.105',
    user_agent: 'Firefox/121.0',
    created_at: '2024-12-25T16:30:00Z'
  },
  {
    id: 6,
    user_id: 4,
    user_name: 'Warehouse Hùng',
    action: 'update',
    entity_type: 'warehouse',
    entity_id: 2,
    old_values: { stock: 50 },
    new_values: { stock: 150 },
    ip_address: '192.168.1.120',
    user_agent: 'Chrome/120.0.0.0',
    created_at: '2024-12-25T14:20:00Z'
  },
  {
    id: 7,
    user_id: 3,
    user_name: 'Staff Lan',
    action: 'logout',
    entity_type: 'user',
    entity_id: 3,
    ip_address: '192.168.1.110',
    user_agent: 'Safari/17.0',
    created_at: '2024-12-25T18:00:00Z'
  }
]

const getActionClass = (action: string) => {
  const classes: Record<string, string> = {
    create: 'bg-success/10 text-success',
    update: 'bg-info/10 text-info',
    delete: 'bg-error/10 text-error',
    login: 'bg-primary/10 text-primary',
    logout: 'bg-slate-500/10 text-slate-400'
  }
  return classes[action] || 'bg-slate-500/10 text-slate-400'
}

const getActionLabel = (action: string) => {
  const labels: Record<string, string> = {
    create: 'Tạo mới',
    update: 'Cập nhật',
    delete: 'Xóa',
    login: 'Đăng nhập',
    logout: 'Đăng xuất'
  }
  return labels[action] || action
}

const getEntityLabel = (entity: string) => {
  const labels: Record<string, string> = {
    user: 'Người dùng',
    product: 'Sản phẩm',
    order: 'Đơn hàng',
    category: 'Danh mục',
    warehouse: 'Kho hàng'
  }
  return labels[entity] || entity
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const toggleExpand = (id: number) => {
  expandedLog.value = expandedLog.value === id ? null : id
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchLogs()
  }
}

// Lifecycle
onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Nhật ký hệ thống</h1>
        <p class="text-slate-400 mt-1">Theo dõi các hoạt động trong hệ thống</p>
      </div>
      <button class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" class="mr-2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
        Xuất báo cáo
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
          <input v-model="searchQuery" type="text" class="form-input pl-10" placeholder="Tìm theo người dùng, hành động..." />
        </div>
        <select v-model="actionFilter" class="form-input w-48">
          <option v-for="opt in actionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
        <select v-model="entityFilter" class="form-input w-48">
          <option v-for="opt in entityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>

    <!-- Logs Table -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Thời gian</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Người dùng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Hành động</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Đối tượng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">IP</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Chi tiết</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <template v-for="log in filteredLogs" :key="log.id">
              <tr class="hover:bg-white/5 transition-colors cursor-pointer" @click="toggleExpand(log.id)">
                <td class="px-6 py-4 text-slate-400 text-sm">{{ formatDate(log.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-sm font-semibold">
                      {{ log.user_name.charAt(0) }}
                    </div>
                    <span class="text-white">{{ log.user_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getActionClass(log.action)]">
                    {{ getActionLabel(log.action) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-slate-300">
                  {{ getEntityLabel(log.entity_type) }}
                  <span v-if="log.entity_id" class="text-slate-500">#{{ log.entity_id }}</span>
                </td>
                <td class="px-6 py-4 text-slate-500 text-sm font-mono">{{ log.ip_address }}</td>
                <td class="px-6 py-4 text-right">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="text-slate-400 transition-transform"
                    :class="{ 'rotate-180': expandedLog === log.id }">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </td>
              </tr>
              <!-- Expanded Details -->
              <tr v-if="expandedLog === log.id">
                <td colspan="6" class="px-6 py-4 bg-dark-700/50">
                  <div class="grid grid-cols-2 gap-4">
                    <div v-if="log.old_values">
                      <p class="text-sm text-slate-400 mb-2">Giá trị cũ:</p>
                      <pre class="text-xs text-slate-300 bg-dark-800 rounded-lg p-3 overflow-auto">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                    </div>
                    <div v-if="log.new_values">
                      <p class="text-sm text-slate-400 mb-2">Giá trị mới:</p>
                      <pre class="text-xs text-slate-300 bg-dark-800 rounded-lg p-3 overflow-auto">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                    </div>
                    <div class="col-span-2">
                      <p class="text-sm text-slate-500">User Agent: <span class="text-slate-400">{{ log.user_agent }}</span></p>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>

        <div v-if="!filteredLogs.length" class="py-16 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" x2="8" y1="13" y2="13" />
            <line x1="16" x2="8" y1="17" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
          <p class="text-slate-400">Không tìm thấy nhật ký nào</p>
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
  </div>
</template>
