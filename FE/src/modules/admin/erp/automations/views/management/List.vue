<script setup lang="ts">
import { triggerLabels, actionLabels } from '../../models/automation'
import { useAutomations } from '../../composables/useAutomations'

const { automations, isLoading, showModal, editingItem, form, openCreate, openEdit, toggleActive, saveAutomation, deleteAutomation } = useAutomations()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Marketing Automation" description="Thiết lập các kịch bản tự động hóa để tối ưu hóa quy trình chăm sóc khách hàng">
      <template #actions>
        <DButton variant="primary" @click="openCreate">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          Tạo workflow mới
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <!-- Automations Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 overflow-auto pb-6">
      <DCard v-for="item in automations" :key="item.id" class="flex flex-col h-full group transition-all duration-300 hover:border-primary/40 border-white/5 bg-dark-800/80">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div :class="['w-2.5 h-2.5 rounded-full shadow-[0_0_8px]', item.is_active ? 'bg-success shadow-success/50' : 'bg-slate-500 shadow-slate-500/20']"></div>
            <h3 class="text-white font-bold text-lg group-hover:text-primary-light transition-colors">{{ item.name }}</h3>
          </div>
          <button @click.stop="toggleActive(item)" 
            :class="['relative w-11 h-6 rounded-full transition-all duration-300 ring-2 ring-transparent', item.is_active ? 'bg-success ring-success/20' : 'bg-dark-600']">
            <span :class="['absolute top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 shadow-sm', item.is_active ? 'left-6' : 'left-1']"></span>
          </button>
        </div>

        <div class="space-y-4 flex-1">
          <div class="p-4 rounded-xl bg-dark-700/50 border border-white/5 space-y-3">
            <div class="flex items-start gap-4">
              <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary-light flex-shrink-0">
                <img src="@/assets/admin/icons/zap.svg" class="w-4 h-4" :style="{ filter: 'drop-shadow(0 0 1px currentColor)' }" alt="Trigger" />
              </div>
              <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1.5">Sự kiện kích hoạt</p>
                <p class="text-sm text-white font-medium italic">{{ triggerLabels[item.trigger] }}</p>
              </div>
            </div>

            <div class="border-t border-white/5 pt-3 flex items-start gap-4">
              <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center text-info flex-shrink-0">
                <img src="@/assets/admin/icons/message-square.svg" class="w-4 h-4" alt="Action" />
              </div>
              <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1.5">Hành động thực thi</p>
                <p class="text-sm text-white font-medium italic">{{ actionLabels[item.action] }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-white/5 grid grid-cols-2 gap-3">
          <DButton variant="secondary" size="sm" class="!bg-dark-700 hover:!bg-dark-600" @click="openEdit(item)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1.5">
              <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
            </svg>
            Tùy chỉnh
          </DButton>
          <DButton variant="error" size="sm" class="!bg-error/10 hover:!bg-error/20 !text-error" @click="deleteAutomation(item)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1.5">
              <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
            </svg>
            Xóa bỏ
          </DButton>
        </div>
      </DCard>

      <!-- Empty State -->
      <DEmptyState v-if="!automations.length" icon="inbox" title="Hệ thống tự động hóa đang trống" description="Hãy bắt đầu bằng cách tạo quy trình tự động đầu tiên của bạn để tiết kiệm thời gian vận hành.">
        <template #action>
          <DButton variant="primary" @click="openCreate">Thiết lập ngay</DButton>
        </template>
      </DEmptyState>
    </div>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingItem ? 'Hiệu chỉnh kịch bản Automation' : 'Thiết lập quy trình tự động mới'" size="md">
      <div class="grid grid-cols-1 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên định danh workflow *</label>
          <input v-model="form.name" type="text" class="form-input" placeholder="Ví dụ: Gửi mã giảm giá khi khách đăng ký" />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Trường hợp kích hoạt (Trigger) *</label>
            <select v-model="form.trigger" class="form-input">
              <option v-for="[k, v] in Object.entries(triggerLabels)" :key="k" :value="k">{{ v }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Hành động cần thực hiện (Action) *</label>
            <select v-model="form.action" class="form-input">
              <option v-for="[k, v] in Object.entries(actionLabels)" :key="k" :value="k">{{ v }}</option>
            </select>
          </div>
        </div>

        <div class="p-4 bg-primary/5 rounded-xl border border-primary/20">
          <p class="text-xs text-primary-light font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            Ghi chú: Automation sẽ được áp dụng ngay sau khi kích hoạt.
          </p>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy bỏ</DButton>
          <DButton variant="primary" class="flex-1" @click="saveAutomation">Kích hoạt & Lưu</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
