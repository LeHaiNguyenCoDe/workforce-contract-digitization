<script setup lang="ts">
import { expenseColumns, typeOptions } from '../../configs/columns'

const {
  expenses, summary, isLoading, isSaving, typeFilter, showModal, editingExpense, form, filteredCategories,
  openCreate, openEdit, saveExpense, deleteExpense, setTypeFilter
} = useExpenses()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Sổ Thu Chi" description="Theo dõi dòng tiền, quản lý các khoản thu nhập và chi phí vận hành doanh nghiệp">
      <template #actions>
        <div class="flex gap-2">
          <DButton variant="success" @click="openCreate('income')">
            <img src="@/assets/admin/icons/plus.svg" class="w-4.5 h-4.5 mr-1.5 brightness-0 invert" alt="Income" />
            Ghi thu
          </DButton>
          <DButton variant="primary" @click="openCreate('expense')">
            <img src="@/assets/admin/icons/minus.svg" class="w-4.5 h-4.5 mr-1.5 brightness-0 invert" alt="Expense" />
            Ghi chi
          </DButton>
        </div>
      </template>
    </AdminPageHeader>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 flex-shrink-0 animate-fade-in">
      <DCard class="relative overflow-hidden group border-success/20 bg-success/5">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
          <img src="@/assets/admin/icons/dollar-sign.svg" class="w-16 h-16" alt="Income" />
        </div>
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Tổng thu tháng này</p>
        <p class="text-3xl font-black text-success">{{ formatPrice(summary.total_income) }}</p>
      </DCard>
      
      <DCard class="relative overflow-hidden group border-error/20 bg-error/5">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
          <img src="@/assets/admin/icons/dollar-sign.svg" class="w-16 h-16" alt="Expense" />
        </div>
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Tổng chi tháng này</p>
        <p class="text-3xl font-black text-error">{{ formatPrice(summary.total_expense) }}</p>
      </DCard>

      <DCard class="relative overflow-hidden group border-white/10" :class="summary.net >= 0 ? 'bg-indigo-500/5' : 'bg-amber-500/5'">
        <div class="absolute top-0 right-0 p-4 opacity-10">
          <img src="@/assets/admin/icons/activity.svg" class="w-16 h-16" alt="Balance" />
        </div>
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Lợi nhuận ròng</p>
        <p class="text-3xl font-black" :class="summary.net >= 0 ? 'text-indigo-400' : 'text-amber-500'">
          {{ formatPrice(summary.net) }}
        </p>
      </DCard>
    </div>

    <!-- Filter -->
    <AdminSearch :modelValue="''" placeholder="Tìm kiếm giao dịch..." class="mb-6">
      <template #filters>
        <div class="flex gap-2">
          <select :value="typeFilter" @change="setTypeFilter(($event.target as HTMLSelectElement).value)" class="form-input w-48 bg-dark-700 border-white/10 text-white">
            <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="expenseColumns" :data="expenses" :loading="isLoading" empty-text="Hệ thống chưa ghi nhận bất kỳ giao dịch thu chi nào.">
      <template #cell-expense_code="{ value }">
        <span class="font-mono text-[11px] text-primary-light bg-primary/10 px-2 py-0.5 rounded border border-primary/20">{{ value }}</span>
      </template>

      <template #cell-type="{ value }">
        <StatusBadge :status="value === 'income' ? 'income' : 'expense'" :text="value === 'income' ? 'Thu' : 'Chi'" />
      </template>

      <template #cell-category="{ item }">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full" :class="item.type === 'income' ? 'bg-success' : 'bg-error'"></div>
          <span class="text-sm text-white font-medium">{{ item.category?.name || 'Chưa phân loại' }}</span>
        </div>
      </template>

      <template #cell-amount="{ item }">
        <span :class="item.type === 'income' ? 'text-success' : 'text-error'" class="font-bold text-base">
          {{ item.type === 'income' ? '+' : '-' }}{{ formatPrice(item.amount) }}
        </span>
      </template>

      <template #cell-expense_date="{ value }">
        <span class="text-slate-500 text-xs">{{ formatDate(value) }}</span>
      </template>

      <template #cell-description="{ value }">
        <p class="text-slate-400 text-xs max-w-xs truncate italic" :title="value">{{ value || 'Không có mô tả' }}</p>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="edit" @click.stop="openEdit(item)" />
          <DAction icon="delete" variant="danger" @click.stop="deleteExpense(item)" />
        </div>
      </template>
    </AdminTable>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingExpense ? 'Cập nhật giao dịch' : (form.type === 'income' ? 'Tạo phiếu thu' : 'Tạo phiếu chi')" size="md">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-1">
          <label class="block text-sm font-medium text-slate-300 mb-2">Loại giao dịch *</label>
          <select v-model="form.type" class="form-input" :disabled="!!editingExpense">
            <option value="expense">Chi phí (Expense)</option>
            <option value="income">Thu nhập (Income)</option>
          </select>
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
          <select v-model="form.category_id" class="form-input">
            <option :value="null" disabled>-- Chọn danh mục --</option>
            <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="filteredCategories.length === 0" class="text-xs text-warning mt-1">
            Chưa có danh mục {{ form.type === 'income' ? 'thu' : 'chi' }}. Vui lòng tạo danh mục trước.
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Số tiền (VNĐ) *</label>
          <input v-model.number="form.amount" type="number" class="form-input" min="0" placeholder="0" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ngày thực hiện *</label>
          <input v-model="form.expense_date" type="date" class="form-input" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Nội dung / Mô tả</label>
          <textarea v-model="form.description" class="form-input min-h-[100px]" placeholder="Nhập chi tiết giao dịch..."></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="saveExpense">Xác nhận Lưu</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
