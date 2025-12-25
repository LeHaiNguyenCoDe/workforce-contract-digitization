<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/shared/components/BaseModal.vue'
import { usePromotionStore } from '../store/store'
import { usePromotions } from '../composables/usePromotions'

const { t } = useI18n()

// Store
const store = usePromotionStore()

// Composables
const {
  showModal,
  editingPromotion,
  formatDate,
  formatDiscount,
  isExpired,
  openCreateModal,
  openEditModal,
  savePromotion,
  deletePromotion
} = usePromotions()

// Computed from store
const promotions = computed(() => store.promotions)
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSaving)
const promotionForm = store.promotionForm

// Lifecycle
onMounted(async () => {
  await store.fetchPromotions()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ t('admin.promotions') }}</h1>
        <p class="text-slate-400 mt-1">Quản lý các chương trình khuyến mãi</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        {{ t('common.create') }}
      </button>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
        </div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">ID</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tên / Mã</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Giảm giá</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Thời gian</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="promo in promotions" :key="promo.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4 text-sm text-slate-400">#{{ promo.id }}</td>
              <td class="px-6 py-4">
                <p class="font-medium text-white">{{ promo.name }}</p>
                <p class="text-xs font-mono text-secondary">{{ promo.code }}</p>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-primary text-white">
                  {{ formatDiscount(promo) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <p class="text-slate-400">{{ formatDate(promo.starts_at) }}</p>
                <p class="text-slate-500">→ {{ formatDate(promo.ends_at) }}</p>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', isExpired(promo) ? 'bg-error/10 text-error' : 'bg-success/10 text-success']">
                  {{ isExpired(promo) ? 'Hết hạn' : 'Đang chạy' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(promo)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                    </svg>
                  </button>
                  <button @click="deletePromotion(promo.id)"
                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18" />
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!promotions.length" class="py-16 text-center">
          <p class="text-slate-400">Chưa có khuyến mãi nào</p>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <BaseModal v-model="showModal" :title="editingPromotion ? 'Chỉnh sửa khuyến mãi' : 'Tạo khuyến mãi mới'" size="md">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Tên *</label>
            <input v-model="promotionForm.name" type="text" class="form-input" placeholder="Black Friday" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Mã</label>
            <input v-model="promotionForm.code" type="text" class="form-input font-mono uppercase"
              placeholder="Tự động" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Loại *</label>
            <select v-model="promotionForm.type" class="form-input">
              <option value="percent">Phần trăm (%)</option>
              <option value="fixed_amount">Số tiền</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Giá trị *</label>
            <input v-model.number="promotionForm.value" type="number" class="form-input" min="1" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Bắt đầu</label>
            <input v-model="promotionForm.starts_at" type="date" class="form-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Kết thúc</label>
            <input v-model="promotionForm.ends_at" type="date" class="form-input" />
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="promotionForm.is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded" />
          <label for="is_active" class="text-sm text-slate-300">Kích hoạt</label>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">
            {{ t('common.cancel') }}
          </button>
          <button @click="savePromotion" class="btn btn-primary"
            :disabled="isSaving || !promotionForm.name || !promotionForm.value">
            <span v-if="isSaving"
              class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
            {{ isSaving ? 'Đang lưu...' : t('common.save') }}
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
