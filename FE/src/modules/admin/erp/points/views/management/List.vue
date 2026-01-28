<script setup lang="ts">
import { usePoints } from '../../composables/usePoints'
import { transactionColumns, transactionTypeLabels, transactionTypeClasses } from '../../configs/columns'
import { formatDateTime } from '@/utils'

const {
  customerInfo, transactions, isLoading, isSaving, searchQuery,
  showRedeemModal, redeemAmount, redeemDescription,
  searchCustomer, openRedeemModal, handleRedeem
} = usePoints()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Hệ thống Điểm thưởng" description="Quản lý tích điểm, đổi thưởng và lịch sử giao dịch điểm của khách hàng" />

    <!-- Search -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="(v) => searchQuery = v" @search="searchCustomer" placeholder="Nhập tên, email hoặc số điện thoại khách hàng để tra cứu..." />

    <!-- Customer Info & Transactions -->
    <template v-if="customerInfo">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 flex-shrink-0 animate-fade-in">
        <DCard class="md:col-span-2 border-primary/20 bg-primary/5">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-inner" :style="{ backgroundColor: customerInfo.tier_color + '20', border: `1px solid ${customerInfo.tier_color}40` }">
              <img src="@/assets/admin/icons/layers.svg" class="w-8 h-8" :style="{ filter: `drop-shadow(0 0 1px ${customerInfo.tier_color})` }" alt="Tier" />
            </div>
            <div class="min-w-0">
              <h3 class="text-xl font-bold text-white truncate">{{ customerInfo.customer_name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider" :style="{ backgroundColor: customerInfo.tier_color + '20', color: customerInfo.tier_color }">
                  {{ customerInfo.tier_name }}
                </span>
                <span class="text-slate-500 text-xs truncate">{{ customerInfo.customer_email || 'Chưa có email' }}</span>
              </div>
            </div>
          </div>
        </DCard>
        
        <DCard class="flex flex-col justify-center">
          <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Khả dụng</p>
          <div class="flex items-baseline gap-1">
            <span class="text-3xl font-black text-primary-light">{{ (customerInfo.current_points || 0).toLocaleString() }}</span>
            <span class="text-xs text-slate-400 font-medium">pts</span>
          </div>
        </DCard>

        <DCard class="flex flex-col justify-center relative overflow-hidden">
          <div class="absolute -right-2 -bottom-2 opacity-5">
            <img src="@/assets/admin/icons/layers.svg" class="w-20 h-20" alt="Points" />
          </div>
          <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Tổng tích lũy</p>
          <p class="text-2xl font-bold text-success">{{ (customerInfo.total_earned || 0).toLocaleString() }}</p>
          <DButton variant="primary" size="sm" class="mt-3" @click="openRedeemModal">
            <img src="@/assets/admin/icons/wallet.svg" class="w-3.5 h-3.5 mr-1 brightness-0 invert" alt="Redeem" />
            Đổi điểm
          </DButton>
        </DCard>
      </div>

      <!-- Transactions -->
      <AdminTable :columns="transactionColumns" :data="transactions" :loading="isLoading" empty-text="Khách hàng chưa có giao dịch điểm nào">
        <template #cell-type="{ value }">
          <span class="px-2 py-1 rounded-full text-xs font-medium" :class="transactionTypeClasses[value] || 'bg-slate-500/10 text-slate-400'">
            {{ transactionTypeLabels[value] || value }}
          </span>
        </template>

        <template #cell-points="{ value }">
          <div class="flex items-center gap-1">
            <span :class="(value || 0) > 0 ? 'text-success' : 'text-error'" class="font-bold text-base">
              {{ (value || 0) > 0 ? '+' : '' }}{{ (value || 0).toLocaleString() }}
            </span>
            <span class="text-[10px] text-slate-500 font-mono">pts</span>
          </div>
        </template>

        <template #cell-balance_after="{ value }">
          <span class="text-white font-mono text-sm bg-dark-700 px-2 py-0.5 rounded border border-white/5">{{ (value || 0).toLocaleString() }}</span>
        </template>

        <template #cell-description="{ value }">
          <span class="text-slate-400 text-xs italic">{{ value || 'Giao dịch hệ thống' }}</span>
        </template>

        <template #cell-created_at="{ value }">
          <span class="text-slate-500 text-[11px]">{{ formatDateTime(value) }}</span>
        </template>
      </AdminTable>
    </template>

    <!-- Initial / Empty State -->
    <template v-else>
      <div class="flex-1 flex flex-col items-center justify-center p-12 bg-dark-800/50 rounded-2xl border border-dashed border-white/10 mt-6">
        <div class="w-20 h-20 rounded-full bg-dark-700 flex items-center justify-center text-slate-500 mb-4 border border-white/5">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Tra cứu điểm thưởng</h3>
        <p class="text-slate-400 text-center max-w-sm">Nhập thông tin khách hàng vào thanh tìm kiếm bên trên để bắt đầu quản lý điểm thưởng.</p>
      </div>
    </template>

    <!-- Redeem Modal -->
    <DModal v-model="showRedeemModal" title="Xác nhận đổi điểm thưởng" size="sm">
      <div class="space-y-4">
        <div class="p-3 bg-primary/10 rounded-xl border border-primary/20">
          <p class="text-xs text-primary-light uppercase font-bold tracking-widest mb-1">Số dư khả dụng</p>
          <p class="text-2xl font-black text-white">{{ customerInfo?.current_points.toLocaleString() }} <span class="text-sm font-normal text-slate-400 font-mono">pts</span></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Số điểm cần đổi *</label>
          <input v-model.number="redeemAmount" type="number" class="form-input" :min="1" :max="customerInfo?.current_points || 0" placeholder="0" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Lý do / Ghi chú</label>
          <textarea v-model="redeemDescription" class="form-input min-h-[80px]" placeholder="Nhập lý do đổi điểm cho khách hàng..."></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showRedeemModal = false">Đóng</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="handleRedeem">Xác nhận đổi</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
