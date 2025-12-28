<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { membershipService, type CustomerMembership, type PointTransaction, type MembershipTier } from '@/plugins/api/services'
import BaseModal from '@/shared/components/BaseModal.vue'

// State
const searchQuery = ref('')
const selectedCustomer = ref<CustomerMembership | null>(null)
const transactions = ref<PointTransaction[]>([])
const isLoading = ref(false)
const isSearching = ref(false)
const showRedeemModal = ref(false)
const tiers = ref<MembershipTier[]>([])

// Redeem form
const redeemForm = ref({
  points: 0,
  description: ''
})

// Mock customers for search
const mockCustomers = [
  { id: 1, name: 'Nguyễn Văn An', email: 'an.nguyen@email.com', total_points: 2500, available_points: 2100, tier: { id: 2, name: 'Bạc', color: '#C0C0C0' } },
  { id: 2, name: 'Trần Thị Bình', email: 'binh.tran@email.com', total_points: 8500, available_points: 7200, tier: { id: 3, name: 'Vàng', color: '#FFD700' } },
  { id: 3, name: 'Lê Hoàng Cường', email: 'cuong.le@email.com', total_points: 450, available_points: 450, tier: { id: 1, name: 'Đồng', color: '#CD7F32' } },
  { id: 4, name: 'Phạm Thu Dung', email: 'dung.pham@email.com', total_points: 25000, available_points: 18500, tier: { id: 4, name: 'Kim cương', color: '#B9F2FF' } }
]

const searchResults = ref<any[]>([])

// Methods
const searchCustomer = async () => {
  if (!searchQuery.value) {
    searchResults.value = []
    return
  }

  isSearching.value = true
  // Mock search
  setTimeout(() => {
    const query = searchQuery.value.toLowerCase()
    searchResults.value = mockCustomers.filter(c =>
      c.name.toLowerCase().includes(query) ||
      c.email.toLowerCase().includes(query)
    )
    isSearching.value = false
  }, 300)
}

const selectCustomer = async (customer: any) => {
  selectedCustomer.value = customer
  searchResults.value = []
  searchQuery.value = ''
  await fetchTransactions(customer.id)
}

const fetchTransactions = async (customerId: number) => {
  isLoading.value = true
  try {
    const response = await membershipService.getCustomerTransactions(customerId)
    transactions.value = response.data
  } catch (error) {
    console.error('Failed to fetch transactions:', error)
    // Mock data
    transactions.value = getMockTransactions()
  } finally {
    isLoading.value = false
  }
}

const getMockTransactions = (): PointTransaction[] => [
  {
    id: 1,
    customer_id: 1,
    type: 'earn',
    points: 500,
    balance_after: 2100,
    description: 'Đơn hàng #1234',
    reference_type: 'order',
    reference_id: 1234,
    created_at: '2024-12-20T10:00:00Z'
  },
  {
    id: 2,
    customer_id: 1,
    type: 'redeem',
    points: -200,
    balance_after: 1600,
    description: 'Đổi voucher giảm giá',
    created_at: '2024-12-18T14:30:00Z'
  },
  {
    id: 3,
    customer_id: 1,
    type: 'earn',
    points: 300,
    balance_after: 1800,
    description: 'Đơn hàng #1230',
    reference_type: 'order',
    reference_id: 1230,
    created_at: '2024-12-15T09:00:00Z'
  },
  {
    id: 4,
    customer_id: 1,
    type: 'adjust',
    points: 100,
    balance_after: 1500,
    description: 'Điều chỉnh - Bù điểm khuyến mãi',
    created_at: '2024-12-10T11:20:00Z'
  },
  {
    id: 5,
    customer_id: 1,
    type: 'earn',
    points: 450,
    balance_after: 1400,
    description: 'Đơn hàng #1225',
    reference_type: 'order',
    reference_id: 1225,
    created_at: '2024-12-05T16:45:00Z'
  }
]

const openRedeemModal = () => {
  redeemForm.value = { points: 0, description: '' }
  showRedeemModal.value = true
}

const redeemPoints = async () => {
  if (!selectedCustomer.value || redeemForm.value.points <= 0) return

  try {
    await membershipService.redeemPoints(
      selectedCustomer.value.id,
      redeemForm.value.points,
      redeemForm.value.description
    )
    showRedeemModal.value = false
    await fetchTransactions(selectedCustomer.value.id)
    selectedCustomer.value.available_points -= redeemForm.value.points
  } catch (error) {
    console.error('Failed to redeem points:', error)
    // Mock redeem
    transactions.value.unshift({
      id: Date.now(),
      customer_id: selectedCustomer.value.id,
      type: 'redeem',
      points: -redeemForm.value.points,
      balance_after: selectedCustomer.value.available_points - redeemForm.value.points,
      description: redeemForm.value.description || 'Đổi điểm',
      created_at: new Date().toISOString()
    })
    selectedCustomer.value.available_points -= redeemForm.value.points
    showRedeemModal.value = false
  }
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'earn': return '+'
    case 'redeem': return '-'
    case 'adjust': return '~'
    case 'expire': return '✕'
    default: return '•'
  }
}

const getTypeClass = (type: string) => {
  switch (type) {
    case 'earn': return 'bg-success/10 text-success'
    case 'redeem': return 'bg-warning/10 text-warning'
    case 'adjust': return 'bg-info/10 text-info'
    case 'expire': return 'bg-error/10 text-error'
    default: return 'bg-slate-500/10 text-slate-400'
  }
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    earn: 'Tích điểm',
    redeem: 'Đổi điểm',
    adjust: 'Điều chỉnh',
    expire: 'Hết hạn'
  }
  return labels[type] || type
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

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('vi-VN').format(num)
}

// Lifecycle
onMounted(async () => {
  try {
    tiers.value = await membershipService.getTiers()
  } catch {
    tiers.value = []
  }
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Điểm thưởng</h1>
        <p class="text-slate-400 mt-1">Quản lý và theo dõi điểm thưởng khách hàng</p>
      </div>
    </div>

    <!-- Search Customer -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
          width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input v-model="searchQuery" @input="searchCustomer" type="text" class="form-input pl-10"
          placeholder="Tìm khách hàng theo tên hoặc email..." />
        
        <!-- Search Results Dropdown -->
        <div v-if="searchResults.length" class="absolute top-full left-0 right-0 mt-1 bg-dark-700 rounded-lg border border-white/10 shadow-xl z-20 max-h-64 overflow-auto">
          <button v-for="customer in searchResults" :key="customer.id"
            @click="selectCustomer(customer)"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors text-left">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-semibold">
              {{ customer.name.charAt(0) }}
            </div>
            <div class="flex-1">
              <p class="text-white font-medium">{{ customer.name }}</p>
              <p class="text-sm text-slate-400">{{ customer.email }}</p>
            </div>
            <div class="text-right">
              <p class="text-primary font-medium">{{ formatNumber(customer.available_points) }} điểm</p>
              <span class="text-xs px-2 py-0.5 rounded-full" :style="{ backgroundColor: customer.tier?.color + '20', color: customer.tier?.color }">
                {{ customer.tier?.name }}
              </span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 min-h-0 flex gap-6">
      <!-- Customer Info Panel -->
      <div v-if="selectedCustomer" class="w-80 flex-shrink-0 bg-dark-800 rounded-xl border border-white/10 p-6">
        <div class="text-center mb-6">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3">
            {{ selectedCustomer.name?.charAt(0) || 'K' }}
          </div>
          <h3 class="text-xl font-semibold text-white">{{ selectedCustomer.name }}</h3>
          <p class="text-slate-400">{{ selectedCustomer.email }}</p>
          <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-medium"
            :style="{ backgroundColor: (selectedCustomer.tier?.color || '#6366f1') + '20', color: selectedCustomer.tier?.color || '#6366f1' }">
            {{ selectedCustomer.tier?.name || 'Thành viên' }}
          </span>
        </div>

        <div class="space-y-4">
          <div class="bg-dark-700 rounded-lg p-4 text-center">
            <p class="text-3xl font-bold text-primary">{{ formatNumber(selectedCustomer.available_points || 0) }}</p>
            <p class="text-sm text-slate-400">Điểm khả dụng</p>
          </div>
          <div class="bg-dark-700 rounded-lg p-4 text-center">
            <p class="text-xl font-bold text-white">{{ formatNumber(selectedCustomer.total_points || 0) }}</p>
            <p class="text-sm text-slate-400">Tổng điểm tích lũy</p>
          </div>
        </div>

        <div class="mt-6 space-y-2">
          <button @click="openRedeemModal" class="btn btn-primary w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" class="mr-2">
              <path d="M21 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3" />
              <path d="M21 16v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3" />
              <path d="M4 12h.01" />
              <path d="M20 12h.01" />
              <path d="M12 12h.01" />
              <path d="M12 8l-4 4 4 4" />
            </svg>
            Đổi điểm
          </button>
          <button @click="selectedCustomer = null" class="btn btn-secondary w-full">
            Đổi khách hàng
          </button>
        </div>
      </div>

      <!-- Transactions -->
      <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
        <div v-if="!selectedCustomer" class="flex-1 flex items-center justify-center">
          <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">Tìm khách hàng</h3>
            <p class="text-slate-400">Nhập tên hoặc email để xem thông tin điểm thưởng</p>
          </div>
        </div>

        <template v-else>
          <div class="p-4 border-b border-white/10">
            <h3 class="text-lg font-semibold text-white">Lịch sử giao dịch điểm</h3>
          </div>

          <div v-if="isLoading" class="flex-1 flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
          </div>

          <div v-else class="flex-1 overflow-auto">
            <table class="w-full">
              <thead class="sticky top-0 bg-dark-700">
                <tr class="border-b border-white/10">
                  <th class="px-6 py-3 text-left text-sm font-semibold text-slate-400">Loại</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold text-slate-400">Mô tả</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-slate-400">Điểm</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-slate-400">Số dư</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-slate-400">Thời gian</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-white/5">
                  <td class="px-6 py-3">
                    <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getTypeClass(tx.type)]">
                      {{ getTypeLabel(tx.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-3 text-slate-300">{{ tx.description }}</td>
                  <td class="px-6 py-3 text-right font-medium" :class="tx.points > 0 ? 'text-success' : 'text-warning'">
                    {{ tx.points > 0 ? '+' : '' }}{{ formatNumber(tx.points) }}
                  </td>
                  <td class="px-6 py-3 text-right text-white">{{ formatNumber(tx.balance_after) }}</td>
                  <td class="px-6 py-3 text-right text-slate-400 text-sm">{{ formatDate(tx.created_at) }}</td>
                </tr>
              </tbody>
            </table>

            <div v-if="!transactions.length" class="py-12 text-center">
              <p class="text-slate-400">Chưa có giao dịch nào</p>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Redeem Modal -->
    <BaseModal v-model="showRedeemModal" title="Đổi điểm thưởng" size="sm">
      <div class="space-y-4">
        <div class="bg-dark-700 rounded-lg p-4 text-center">
          <p class="text-sm text-slate-400">Điểm khả dụng</p>
          <p class="text-2xl font-bold text-primary">{{ formatNumber(selectedCustomer?.available_points || 0) }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Số điểm đổi *</label>
          <input v-model.number="redeemForm.points" type="number" min="1" :max="selectedCustomer?.available_points"
            class="form-input" placeholder="Nhập số điểm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
          <input v-model="redeemForm.description" type="text" class="form-input"
            placeholder="VD: Đổi voucher giảm giá..." />
        </div>

        <div class="flex gap-3 pt-4">
          <button @click="showRedeemModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="redeemPoints"
            :disabled="redeemForm.points <= 0 || redeemForm.points > (selectedCustomer?.available_points || 0)"
            class="btn btn-primary flex-1">
            Xác nhận
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
