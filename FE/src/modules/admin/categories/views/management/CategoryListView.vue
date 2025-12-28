<script setup lang="ts">
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { categoryColumns } from '../../configs/columns'

const { t } = useI18n()

// Store
const store = useAdminCategoryStore()

// Extract reactive refs from store
const { categories, isLoading, isSaving, categoryForm } = storeToRefs(store)

// Composables
const {
  showModal,
  editingCategory,
  handleNameChange,
  openCreateModal,
  openEditModal,
  saveCategory,
  deleteCategory
} = useCategories()

// Lifecycle
onMounted(async () => {
  await store.fetchCategories()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader :title="t('admin.categories')" description="Quản lý danh mục sản phẩm của cửa hàng">
      <template #actions>
        <DButton variant="primary" @click="openCreateModal">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          {{ t('common.create') }}
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Table -->
    <AdminTable :columns="categoryColumns" :data="categories" :loading="isLoading" empty-text="Chưa có danh mục nào">
      <template #cell-name="{ item }">
        <div>
          <p class="font-medium text-white">{{ item.name }}</p>
          <p class="text-xs text-slate-500">{{ item.slug }}</p>
        </div>
      </template>

      <template #cell-slug="{ value }">
        <code class="text-xs bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 font-mono">{{ value }}</code>
      </template>

      <template #cell-is_active="{ item }">
        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', item.is_active !== false ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400']">
          {{ item.is_active !== false ? 'Hoạt động' : 'Tạm ẩn' }}
        </span>
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-400 text-sm">{{ formatDate(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="edit" @click.stop="openEditModal(item)" />
          <DAction icon="delete" variant="danger" @click.stop="deleteCategory(item.id)" />
        </div>
      </template>
    </AdminTable>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingCategory ? 'Chỉnh sửa danh mục' : 'Tạo danh mục mới'" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên danh mục *</label>
          <input v-model="categoryForm.name" @input="handleNameChange" type="text" class="form-input" placeholder="Nhập tên danh mục" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
          <input v-model="categoryForm.slug" type="text" class="form-input" placeholder="tu-dong-tao-tu-ten" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
          <textarea v-model="categoryForm.description" class="form-input" rows="3" placeholder="Mô tả danh mục..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục cha</label>
          <select v-model="categoryForm.parent_id" class="form-input">
            <option value="">Không có (Danh mục gốc)</option>
            <option v-for="cat in categories.filter(c => c.id !== editingCategory?.id)" :key="cat.id" :value="cat.id.toString()">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="saveCategory">Lưu lại</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
