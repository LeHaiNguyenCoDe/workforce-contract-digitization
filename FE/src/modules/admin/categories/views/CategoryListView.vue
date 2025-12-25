<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useCategoryStore } from '../store/store'
import { useCategories } from '../composables/useCategories'

const { t } = useI18n()

// Store
const store = useCategoryStore()

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

// Computed from store
const categories = computed(() => store.categories)
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSaving)
const categoryForm = store.categoryForm

// Lifecycle
onMounted(async () => {
  await store.fetchCategories()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ t('admin.categories') }}</h1>
        <p class="text-slate-400 mt-1">Quản lý danh mục sản phẩm</p>
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
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tên danh mục</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Danh mục cha</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="category in categories" :key="category.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4 text-sm text-slate-400">#{{ category.id }}</td>
              <td class="px-6 py-4">
                <p class="font-medium text-white">{{ category.name }}</p>
                <p class="text-xs text-slate-500">{{ category.slug }}</p>
              </td>
              <td class="px-6 py-4">
                <span v-if="category.parent"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                  {{ category.parent.name }}
                </span>
                <span v-else class="text-slate-500">-</span>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400">
                  {{ category.products_count ?? 0 }} sp
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(category)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                    </svg>
                  </button>
                  <button @click="deleteCategory(category.id)"
                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18" />
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                      <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!categories.length" class="py-16 text-center">
          <p class="text-slate-400">Chưa có danh mục nào</p>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <BaseModal v-model="showModal" :title="editingCategory ? 'Chỉnh sửa danh mục' : 'Tạo danh mục mới'" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên danh mục *</label>
          <input v-model="categoryForm.name" @input="handleNameChange" type="text" class="form-input"
            placeholder="Nhập tên danh mục" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
          <input v-model="categoryForm.slug" type="text" class="form-input" placeholder="tu-dong-tao-tu-ten" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
          <textarea v-model="categoryForm.description" class="form-input" rows="3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục cha</label>
          <select v-model="categoryForm.parent_id" class="form-input">
            <option value="">Không có</option>
            <option v-for="cat in categories.filter(c => c.id !== editingCategory?.id)" :key="cat.id"
              :value="cat.id.toString()">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">
            {{ t('common.cancel') }}
          </button>
          <button @click="saveCategory" class="btn btn-primary" :disabled="isSaving || !categoryForm.name">
            <span v-if="isSaving"
              class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
            {{ isSaving ? 'Đang lưu...' : t('common.save') }}
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
