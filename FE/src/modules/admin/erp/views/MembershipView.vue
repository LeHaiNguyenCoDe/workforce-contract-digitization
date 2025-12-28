<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { membershipService, type MembershipTier } from '@/plugins/api/services'
import BaseModal from '@/shared/components/BaseModal.vue'

// State
const tiers = ref<MembershipTier[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingTier = ref<MembershipTier | null>(null)
const isSaving = ref(false)

// Form
const tierForm = ref({
  name: '',
  min_points: 0,
  discount_percent: 0,
  color: '#6366f1',
  benefits: [] as string[]
})

const newBenefit = ref('')

// Preset colors
const colorPresets = [
  { name: 'Bronze', color: '#CD7F32' },
  { name: 'Silver', color: '#C0C0C0' },
  { name: 'Gold', color: '#FFD700' },
  { name: 'Platinum', color: '#E5E4E2' },
  { name: 'Diamond', color: '#B9F2FF' }
]

// Methods
const fetchTiers = async () => {
  try {
    isLoading.value = true
    const data = await membershipService.getTiers()
    tiers.value = data
  } catch (error) {
    console.error('Failed to fetch tiers:', error)
    // Mock data
    tiers.value = getMockTiers()
  } finally {
    isLoading.value = false
  }
}

const getMockTiers = (): MembershipTier[] => [
  {
    id: 1,
    name: 'Đồng',
    min_points: 0,
    discount_percent: 0,
    color: '#CD7F32',
    benefits: ['Tích điểm 1%', 'Nhận thông báo ưu đãi'],
    member_count: 1250,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Bạc',
    min_points: 1000,
    discount_percent: 5,
    color: '#C0C0C0',
    benefits: ['Tích điểm 2%', 'Giảm giá 5%', 'Ưu tiên hỗ trợ'],
    member_count: 450,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 3,
    name: 'Vàng',
    min_points: 5000,
    discount_percent: 10,
    color: '#FFD700',
    benefits: ['Tích điểm 3%', 'Giảm giá 10%', 'Free shipping', 'Quà sinh nhật'],
    member_count: 120,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 4,
    name: 'Kim cương',
    min_points: 20000,
    discount_percent: 15,
    color: '#B9F2FF',
    benefits: ['Tích điểm 5%', 'Giảm giá 15%', 'Free shipping', 'Quà sinh nhật VIP', 'Hotline riêng'],
    member_count: 28,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z'
  }
]

const openCreateModal = () => {
  editingTier.value = null
  tierForm.value = {
    name: '',
    min_points: 0,
    discount_percent: 0,
    color: '#6366f1',
    benefits: []
  }
  showModal.value = true
}

const openEditModal = (tier: MembershipTier) => {
  editingTier.value = tier
  tierForm.value = {
    name: tier.name,
    min_points: tier.min_points,
    discount_percent: tier.discount_percent,
    color: tier.color || '#6366f1',
    benefits: tier.benefits ? [...tier.benefits] : []
  }
  showModal.value = true
}

const addBenefit = () => {
  if (newBenefit.value.trim()) {
    tierForm.value.benefits.push(newBenefit.value.trim())
    newBenefit.value = ''
  }
}

const removeBenefit = (index: number) => {
  tierForm.value.benefits.splice(index, 1)
}

const saveTier = async () => {
  if (!tierForm.value.name) return

  try {
    isSaving.value = true
    if (editingTier.value) {
      await membershipService.updateTier(editingTier.value.id, tierForm.value)
    } else {
      await membershipService.createTier(tierForm.value)
    }
    showModal.value = false
    await fetchTiers()
  } catch (error) {
    console.error('Failed to save tier:', error)
    // Mock save
    if (editingTier.value) {
      const index = tiers.value.findIndex(t => t.id === editingTier.value!.id)
      if (index !== -1) {
        tiers.value[index] = { ...tiers.value[index], ...tierForm.value }
      }
    } else {
      tiers.value.push({
        id: Date.now(),
        ...tierForm.value,
        member_count: 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      })
    }
    showModal.value = false
  } finally {
    isSaving.value = false
  }
}

const deleteTier = async (id: number) => {
  if (!confirm('Xác nhận xóa hạng thành viên này?')) return

  try {
    await membershipService.deleteTier(id)
    await fetchTiers()
  } catch (error) {
    console.error('Failed to delete tier:', error)
    tiers.value = tiers.value.filter(t => t.id !== id)
  }
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('vi-VN').format(num)
}

// Lifecycle
onMounted(() => {
  fetchTiers()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Hạng thành viên</h1>
        <p class="text-slate-400 mt-1">Quản lý cấp bậc và quyền lợi thành viên</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        Thêm hạng
      </button>
    </div>

    <!-- Tiers Grid -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="tier in tiers" :key="tier.id"
        class="bg-dark-800 rounded-xl border border-white/10 overflow-hidden hover:border-white/20 transition-colors">
        <!-- Header with color -->
        <div class="h-2" :style="{ backgroundColor: tier.color || '#6366f1' }"></div>
        
        <div class="p-6">
          <!-- Tier Name & Icon -->
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
              :style="{ backgroundColor: (tier.color || '#6366f1') + '20' }">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                :stroke="tier.color || '#6366f1'" stroke-width="2">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                <path d="M4 22h16" />
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-white">{{ tier.name }}</h3>
              <p class="text-sm text-slate-400">{{ formatNumber(tier.member_count || 0) }} thành viên</p>
            </div>
          </div>

          <!-- Stats -->
          <div class="space-y-3 mb-4">
            <div class="flex justify-between items-center">
              <span class="text-slate-400 text-sm">Điểm tối thiểu</span>
              <span class="text-white font-medium">{{ formatNumber(tier.min_points) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400 text-sm">Chiết khấu</span>
              <span class="text-success font-medium">{{ tier.discount_percent }}%</span>
            </div>
          </div>

          <!-- Benefits -->
          <div class="mb-4">
            <p class="text-sm text-slate-400 mb-2">Quyền lợi:</p>
            <div class="space-y-1">
              <div v-for="(benefit, i) in (tier.benefits || []).slice(0, 3)" :key="i"
                class="flex items-center gap-2 text-sm text-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" class="text-success flex-shrink-0">
                  <path d="M20 6 9 17l-5-5" />
                </svg>
                <span>{{ benefit }}</span>
              </div>
              <p v-if="(tier.benefits || []).length > 3" class="text-xs text-slate-500">
                +{{ tier.benefits!.length - 3 }} quyền lợi khác
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button @click="openEditModal(tier)"
              class="flex-1 btn btn-secondary btn-sm">
              Sửa
            </button>
            <button @click="deleteTier(tier.id)"
              class="w-9 h-9 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">
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
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && !tiers.length" class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
      <div class="text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
          <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
          <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
          <path d="M4 22h16" />
          <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
          <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
          <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
        </svg>
        <h3 class="text-xl font-semibold text-white mb-2">Chưa có hạng thành viên</h3>
        <p class="text-slate-400 mb-4">Tạo hạng đầu tiên để bắt đầu chương trình loyalty</p>
        <button @click="openCreateModal" class="btn btn-primary">Thêm hạng</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <BaseModal v-model="showModal" :title="editingTier ? 'Sửa hạng thành viên' : 'Thêm hạng mới'" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên hạng *</label>
          <input v-model="tierForm.name" type="text" class="form-input" placeholder="VD: Vàng, Kim cương..." />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Điểm tối thiểu</label>
            <input v-model.number="tierForm.min_points" type="number" min="0" class="form-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Chiết khấu (%)</label>
            <input v-model.number="tierForm.discount_percent" type="number" min="0" max="100" class="form-input" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Màu sắc</label>
          <div class="flex gap-2">
            <button v-for="preset in colorPresets" :key="preset.color"
              @click="tierForm.color = preset.color"
              class="w-8 h-8 rounded-lg border-2 transition-all"
              :class="tierForm.color === preset.color ? 'border-white scale-110' : 'border-transparent'"
              :style="{ backgroundColor: preset.color }"
              :title="preset.name">
            </button>
            <input v-model="tierForm.color" type="color" class="w-8 h-8 rounded cursor-pointer" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Quyền lợi</label>
          <div class="space-y-2 mb-2">
            <div v-for="(benefit, i) in tierForm.benefits" :key="i"
              class="flex items-center gap-2 bg-dark-700 rounded-lg px-3 py-2">
              <span class="flex-1 text-sm text-white">{{ benefit }}</span>
              <button @click="removeBenefit(i)" class="text-slate-400 hover:text-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M18 6 6 18" />
                  <path d="m6 6 12 12" />
                </svg>
              </button>
            </div>
          </div>
          <div class="flex gap-2">
            <input v-model="newBenefit" @keyup.enter="addBenefit" type="text" class="form-input flex-1"
              placeholder="Thêm quyền lợi..." />
            <button @click="addBenefit" class="btn btn-secondary">Thêm</button>
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="saveTier" :disabled="!tierForm.name || isSaving" class="btn btn-primary flex-1">
            {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
