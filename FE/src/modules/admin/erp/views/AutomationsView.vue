<script setup lang="ts">
import { ref, onMounted } from 'vue'
import BaseModal from '@/shared/components/BaseModal.vue'

interface Automation {
  id: number
  name: string
  trigger: string
  action: string
  active: boolean
  runs: number
  last_run?: string
}

// State
const automations = ref<Automation[]>([])
const isLoading = ref(true)
const showModal = ref(false)

// Form
const automationForm = ref({
  name: '',
  trigger: '',
  action: '',
  active: true
})

// Trigger options
const triggerOptions = [
  { value: 'order_placed', label: 'Khi có đơn hàng mới' },
  { value: 'order_delivered', label: 'Khi đơn hàng được giao' },
  { value: 'user_registered', label: 'Khi có user mới đăng ký' },
  { value: 'cart_abandoned', label: 'Giỏ hàng bị bỏ quên (24h)' },
  { value: 'low_stock', label: 'Khi hàng sắp hết' },
  { value: 'birthday', label: 'Sinh nhật khách hàng' }
]

// Action options
const actionOptions = [
  { value: 'send_email', label: 'Gửi email thông báo' },
  { value: 'send_sms', label: 'Gửi SMS' },
  { value: 'add_points', label: 'Cộng điểm thưởng' },
  { value: 'create_voucher', label: 'Tạo voucher giảm giá' },
  { value: 'notify_admin', label: 'Thông báo cho admin' }
]

// Methods
const fetchAutomations = async () => {
  isLoading.value = true
  setTimeout(() => {
    automations.value = getMockAutomations()
    isLoading.value = false
  }, 500)
}

const getMockAutomations = (): Automation[] => [
  {
    id: 1,
    name: 'Chào mừng thành viên mới',
    trigger: 'user_registered',
    action: 'send_email',
    active: true,
    runs: 156,
    last_run: '2024-12-26T08:30:00Z'
  },
  {
    id: 2,
    name: 'Nhắc giỏ hàng bỏ quên',
    trigger: 'cart_abandoned',
    action: 'send_email',
    active: true,
    runs: 89,
    last_run: '2024-12-25T14:20:00Z'
  },
  {
    id: 3,
    name: 'Điểm thưởng sinh nhật',
    trigger: 'birthday',
    action: 'add_points',
    active: true,
    runs: 45,
    last_run: '2024-12-24T00:00:00Z'
  },
  {
    id: 4,
    name: 'Cảnh báo hết hàng',
    trigger: 'low_stock',
    action: 'notify_admin',
    active: false,
    runs: 23
  }
]

const getTriggerLabel = (value: string) => {
  return triggerOptions.find(t => t.value === value)?.label || value
}

const getActionLabel = (value: string) => {
  return actionOptions.find(a => a.value === value)?.label || value
}

const formatDate = (date?: string) => {
  if (!date) return 'Chưa chạy'
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const toggleAutomation = (id: number) => {
  const automation = automations.value.find(a => a.id === id)
  if (automation) {
    automation.active = !automation.active
  }
}

const openCreateModal = () => {
  automationForm.value = { name: '', trigger: '', action: '', active: true }
  showModal.value = true
}

const saveAutomation = () => {
  if (!automationForm.value.name || !automationForm.value.trigger || !automationForm.value.action) return
  
  automations.value.push({
    id: Date.now(),
    ...automationForm.value,
    runs: 0
  })
  showModal.value = false
}

const deleteAutomation = (id: number) => {
  if (!confirm('Xác nhận xóa automation này?')) return
  automations.value = automations.value.filter(a => a.id !== id)
}

// Lifecycle
onMounted(() => {
  fetchAutomations()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Marketing Automation</h1>
        <p class="text-slate-400 mt-1">Tự động hóa các chiến dịch marketing</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        Tạo Automation
      </button>
    </div>

    <!-- Info Banner -->
    <div class="bg-info/10 border border-info/20 rounded-xl p-4 mb-6 flex-shrink-0">
      <div class="flex gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" class="text-info flex-shrink-0">
          <circle cx="12" cy="12" r="10" />
          <path d="M12 16v-4" />
          <path d="M12 8h.01" />
        </svg>
        <div>
          <p class="text-info font-medium">Tính năng đang phát triển</p>
          <p class="text-sm text-slate-400 mt-1">
            Hệ thống Automation đang trong giai đoạn xây dựng. Các rules bên dưới là mẫu demo.
            Trong tương lai sẽ tích hợp với email service, SMS gateway và notification system.
          </p>
        </div>
      </div>
    </div>

    <!-- Automations List -->
    <div class="flex-1 min-h-0 space-y-4 overflow-auto">
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else-if="automations.length" class="space-y-4">
        <div v-for="automation in automations" :key="automation.id"
          class="bg-dark-800 rounded-xl border border-white/10 p-5 hover:border-white/20 transition-colors">
          <div class="flex items-start justify-between">
            <div class="flex items-start gap-4">
              <!-- Status Toggle -->
              <button @click="toggleAutomation(automation.id)"
                class="relative w-12 h-6 rounded-full transition-colors mt-1"
                :class="automation.active ? 'bg-success' : 'bg-slate-600'">
                <span class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all"
                  :class="automation.active ? 'left-7' : 'left-1'"></span>
              </button>

              <div>
                <h3 class="text-lg font-semibold text-white">{{ automation.name }}</h3>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                  <span class="flex items-center gap-1.5 text-sm text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                    </svg>
                    {{ getTriggerLabel(automation.trigger) }}
                  </span>
                  <span class="text-slate-600">→</span>
                  <span class="flex items-center gap-1.5 text-sm text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="m22 8-6 4 6 4V8Z" />
                      <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
                    </svg>
                    {{ getActionLabel(automation.action) }}
                  </span>
                </div>
                <div class="flex items-center gap-4 mt-3 text-sm">
                  <span class="text-slate-500">Đã chạy: <span class="text-white">{{ automation.runs }}</span> lần</span>
                  <span class="text-slate-500">Lần cuối: <span class="text-slate-300">{{ formatDate(automation.last_run) }}</span></span>
                </div>
              </div>
            </div>

            <button @click="deleteAutomation(automation.id)"
              class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M3 6h18" />
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center py-12">
        <div class="text-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
            <path d="m22 8-6 4 6 4V8Z" />
            <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
          </svg>
          <h3 class="text-xl font-semibold text-white mb-2">Chưa có automation nào</h3>
          <p class="text-slate-400 mb-4">Tạo automation đầu tiên để bắt đầu tự động hóa</p>
          <button @click="openCreateModal" class="btn btn-primary">Tạo Automation</button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showModal" title="Tạo Automation mới" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên Automation *</label>
          <input v-model="automationForm.name" type="text" class="form-input" placeholder="VD: Chào mừng thành viên mới" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Trigger - Khi nào chạy *</label>
          <select v-model="automationForm.trigger" class="form-input">
            <option value="">Chọn trigger</option>
            <option v-for="opt in triggerOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Action - Hành động *</label>
          <select v-model="automationForm.action" class="form-input">
            <option value="">Chọn action</option>
            <option v-for="opt in actionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <div>
          <label class="flex items-center gap-2">
            <input v-model="automationForm.active" type="checkbox" class="form-checkbox" />
            <span class="text-sm text-slate-300">Kích hoạt ngay</span>
          </label>
        </div>

        <div class="flex gap-3 pt-4">
          <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="saveAutomation"
            :disabled="!automationForm.name || !automationForm.trigger || !automationForm.action"
            class="btn btn-primary flex-1">
            Tạo
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
