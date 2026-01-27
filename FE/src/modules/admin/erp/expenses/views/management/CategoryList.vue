<script setup lang="ts">
import { useExpenseCategories } from '../../composables/useExpenseCategories'

const {
    filteredCategories, isLoading, isSaving, typeFilter, showModal, editingCategory, form,
    openCreate, openEdit, saveCategory, deleteCategory, setTypeFilter
} = useExpenseCategories()

const typeOptions = [
    { value: 'all', label: 'Tất cả' },
    { value: 'expense', label: 'Chi phí' },
    { value: 'income', label: 'Thu nhập' }
]
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <AdminPageHeader title="Danh mục Thu Chi" description="Quản lý các danh mục phân loại khoản thu và chi phí">
            <template #actions>
                <div class="flex gap-2">
                    <DButton variant="success" @click="openCreate('income')">
                        <img src="@/assets/admin/icons/plus.svg" class="w-4 h-4 mr-1.5 brightness-0 invert" />
                        Thêm danh mục Thu
                    </DButton>
                    <DButton variant="primary" @click="openCreate('expense')">
                        <img src="@/assets/admin/icons/plus.svg" class="w-4 h-4 mr-1.5 brightness-0 invert" />
                        Thêm danh mục Chi
                    </DButton>
                </div>
            </template>
        </AdminPageHeader>

        <!-- Filter -->
        <div class="flex gap-4 mb-4">
            <select :value="typeFilter" @change="setTypeFilter(($event.target as HTMLSelectElement).value as any)"
                class="form-input w-48 bg-dark-700 border-white/10">
                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
        </div>

        <!-- Table -->
        <AdminTable :columns="[
            { key: 'code', label: 'Mã', width: '120px' },
            { key: 'name', label: 'Tên danh mục' },
            { key: 'type', label: 'Loại', width: '100px' },
            { key: 'description', label: 'Mô tả' },
        ]" :data="filteredCategories" :loading="isLoading" empty-text="Chưa có danh mục nào">
            <template #cell-code="{ value }">
                <code class="text-xs font-mono text-primary-light bg-primary/10 px-2 py-0.5 rounded">{{ value }}</code>
            </template>

            <template #cell-type="{ value }">
                <StatusBadge :status="value === 'income' ? 'income' : 'expense'"
                    :text="value === 'income' ? 'Thu' : 'Chi'" />
            </template>

            <template #cell-description="{ value }">
                <span class="text-slate-400 text-sm">{{ value || '-' }}</span>
            </template>

            <template #actions="{ item }">
                <div class="flex items-center justify-end gap-1">
                    <DAction icon="edit" @click.stop="openEdit(item)" />
                    <DAction icon="delete" variant="danger" @click.stop="deleteCategory(item)" />
                </div>
            </template>
        </AdminTable>

        <!-- Modal -->
        <DModal v-model="showModal"
            :title="editingCategory ? 'Sửa danh mục' : (form.type === 'income' ? 'Thêm danh mục Thu' : 'Thêm danh mục Chi')"
            size="md">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tên danh mục *</label>
                        <input v-model="form.name" type="text" class="form-input" placeholder="VD: Tiền điện" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Mã danh mục *</label>
                        <input v-model="form.code" type="text" class="form-input" placeholder="VD: ELECTRIC"
                            :disabled="!!editingCategory" />
                        <p class="text-xs text-slate-500 mt-1">Không thể sửa sau khi tạo</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Loại</label>
                    <select v-model="form.type" class="form-input" :disabled="!!editingCategory">
                        <option value="expense">Chi phí</option>
                        <option value="income">Thu nhập</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
                    <textarea v-model="form.description" class="form-input min-h-[80px]"
                        placeholder="Mô tả chi tiết..."></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3">
                    <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
                    <DButton variant="primary" class="flex-1" :loading="isSaving" @click="saveCategory">Lưu</DButton>
                </div>
            </template>
        </DModal>
    </div>
</template>
