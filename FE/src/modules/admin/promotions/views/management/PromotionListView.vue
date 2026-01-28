```
<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { promotionColumns } from '../../configs/columns'
import { useAdminPromotionStore } from '../../store/store'
import { useAdminPromotions } from '../../composables/useAdminPromotions'

const { t } = useI18n()

// Store
const store = useAdminPromotionStore()

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
} = useAdminPromotions()

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
    <AdminPageHeader :title="t('admin.promotions')" :description="t('common.managePromotions')">
      <template #actions>
        <DButton variant="primary" @click="openCreateModal">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          {{ t('common.create') }}
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Table -->
    <AdminTable :columns="promotionColumns" :data="promotions" :loading="isLoading" :empty-text="t('admin.noPromotions')">
      <template #cell-name_code="{ item }">
        <div>
          <p class="font-medium text-white">{{ item.name }}</p>
          <code class="text-[10px] font-mono text-primary-light bg-primary/10 px-1 rounded uppercase">{{ item.code }}</code>
        </div>
      </template>

      <template #cell-discount="{ item }">
        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-sm font-bold bg-primary text-white shadow-sm">
          {{ formatDiscount(item) }}
        </span>
      </template>

      <template #cell-period="{ item }">
        <div class="text-xs">
          <p class="text-slate-400">{{ formatDate(item.starts_at) }}</p>
          <p class="text-slate-500">đến {{ formatDate(item.ends_at) }}</p>
        </div>
      </template>

      <template #cell-status="{ item }">
        <StatusBadge :status="isExpired(item) ? 'expired' : (item.is_active ? 'active' : 'inactive')" 
          :text="isExpired(item) ? t('admin.expired') : (item.is_active ? t('admin.running') : t('admin.paused'))" />
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="edit" @click.stop="openEditModal(item)" />
          <DAction icon="delete" variant="danger" @click.stop="deletePromotion(item.id)" />
        </div>
      </template>
    </AdminTable>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingPromotion ? t('common.updatePromotion') : t('common.createPromotion')" size="md">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.programName') }} *</label>
          <input v-model="promotionForm.name" type="text" class="form-input" placeholder="Ví dụ: Black Friday 2024" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('cart.promoCode') }}</label>
          <input v-model="promotionForm.code" type="text" class="form-input font-mono uppercase" placeholder="Tự động" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('common.status') }}</label>
          <div class="flex items-center h-10">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="promotionForm.is_active" type="checkbox" class="form-checkbox" />
              <span class="text-sm text-slate-400">{{ t('admin.activate') }}</span>
            </label>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.discountType') }} *</label>
          <select v-model="promotionForm.type" class="form-input">
            <option value="percent">{{ t('admin.percentage') }}</option>
            <option value="fixed_amount">{{ t('admin.fixedAmount') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.value') }} *</label>
          <input v-model.number="promotionForm.value" type="number" class="form-input" min="1" placeholder="0" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.startDate') }}</label>
          <input v-model="promotionForm.starts_at" type="date" class="form-input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.endDate') }}</label>
          <input v-model="promotionForm.ends_at" type="date" class="form-input" />
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">{{ t('common.cancel') }}</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="savePromotion">{{ t('common.savePromotion') }}</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
