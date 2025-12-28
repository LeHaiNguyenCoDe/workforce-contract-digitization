<script setup lang="ts">
import { colorPresets } from '../../configs/presets'

const {
  tiers, isLoading, isSaving, showModal, editingTier, form, newBenefit,
  openCreate, openEdit, addBenefit, removeBenefit, saveTier, deleteTier
} = useMembership()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Hạng Thành Viên" description="Thiết lập các cấp bậc thành viên, điểm tích lũy và quyền lợi ưu đãi">
      <template #actions>
        <DButton variant="primary" @click="openCreate">
          <svg xmlns="http://www.w3.org/2000/center" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" class="mr-2">
            <path d="M12 5v14" /><path d="M5 12h14" />
          </svg>
          Thêm hạng mới
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Content -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <!-- Tiers Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 overflow-auto pb-6">
      <DCard v-for="tier in tiers" :key="tier.id" class="flex flex-col h-full group hover:border-primary/50 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform duration-300" 
            :style="{ backgroundColor: tier.color + '15', border: `1px solid ${tier.color}40` }">
            <img src="@/assets/admin/icons/layers.svg" class="w-7 h-7" :style="{ filter: `drop-shadow(0 0 1px ${tier.color})` }" alt="Tier" />
          </div>
          <div class="flex flex-col items-end">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Thành viên</span>
            <span class="text-lg font-bold text-white">{{ tier.member_count || 0 }}</span>
          </div>
        </div>

        <div class="mb-4">
          <h3 class="text-xl font-bold text-white mb-1">{{ tier.name }}</h3>
          <div class="flex items-center gap-2">
            <div class="h-1.5 flex-1 bg-dark-600 rounded-full overflow-hidden">
              <div class="h-full rounded-full" :style="{ width: '100%', backgroundColor: tier.color }"></div>
            </div>
            <span class="text-[10px] font-mono text-slate-400">{{ tier.min_points.toLocaleString() }} pts</span>
          </div>
        </div>

        <div class="space-y-3 flex-1">
          <div class="flex items-center justify-between p-2 rounded-lg bg-dark-700/50 border border-white/5">
            <span class="text-xs text-slate-400">Giảm giá trực tiếp</span>
            <span class="text-sm font-bold text-success">{{ tier.discount_percent }}%</span>
          </div>
          
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Quyền lợi đặc biệt</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="(b, i) in tier.benefits" :key="i" 
                class="px-2 py-0.5 rounded text-[10px] font-medium bg-dark-700 text-slate-300 border border-white/5">
                {{ b }}
              </span>
              <span v-if="!tier.benefits?.length" class="text-xs text-slate-600 italic">Chưa có quyền lợi</span>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-white/5 grid grid-cols-2 gap-2">
          <DButton variant="secondary" size="sm" @click="openEdit(tier)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
              <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
            </svg>
            Sửa
          </DButton>
          <DButton variant="error" size="sm" @click="deleteTier(tier)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
              <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
            </svg>
            Xóa
          </DButton>
        </div>
      </DCard>
    </div>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingTier ? 'Cập nhật hạng thành viên' : 'Thiết lập hạng mới'" size="md">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên hạng *</label>
          <input v-model="form.name" type="text" class="form-input" placeholder="Ví dụ: Platinum, Diamond..." />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Điểm tối thiểu</label>
          <input v-model.number="form.min_points" type="number" class="form-input" min="0" placeholder="0" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Giảm giá (%)</label>
          <input v-model.number="form.discount_percent" type="number" class="form-input" min="0" max="100" placeholder="0" />
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Nhận diện thương hiệu (Màu sắc)</label>
          <div class="flex flex-wrap gap-2 p-3 bg-dark-700 rounded-xl border border-white/5">
            <button v-for="preset in colorPresets" :key="preset.color" @click="form.color = preset.color"
              :class="['w-8 h-8 rounded-full border-2 transition-all shadow-sm', form.color === preset.color ? 'border-white scale-110 ring-2 ring-primary/20' : 'border-transparent opacity-60 hover:opacity-100']"
              :style="{ backgroundColor: preset.color }" :title="preset.name" />
            <div class="w-px h-8 bg-white/10 mx-1"></div>
            <input v-model="form.color" type="color" class="w-8 h-8 rounded-full cursor-pointer bg-transparent border-none p-0 overflow-hidden" />
          </div>
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Danh sách quyền lợi</label>
          <div class="flex gap-2 mb-3">
            <input v-model="newBenefit" @keyup.enter="addBenefit" type="text" class="form-input flex-1" placeholder="Thêm quyền lợi mới..." />
            <DButton variant="secondary" @click="addBenefit" class="w-10 !p-0">+</DButton>
          </div>
          <div class="flex flex-wrap gap-2">
            <span v-for="(b, i) in form.benefits" :key="i" class="px-3 py-1 bg-dark-600 rounded-lg text-xs text-slate-300 flex items-center gap-2 border border-white/5">
              {{ b }}
              <button @click="removeBenefit(i)" class="text-slate-500 hover:text-error transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </span>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="saveTier">Lưu cấu hình</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
