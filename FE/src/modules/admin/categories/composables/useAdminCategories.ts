/**
 * Composable for Categories
 * Provides reusable logic for category management
 */

import { ref } from 'vue'
import { useSwal } from '@/shared/utils'
import { adminCategoryService } from '@/plugins/api/services/CategoryService'
import { useAutoTranslate } from '@/shared/composables/useAutoTranslate'
import type { Category } from '../store/store'

export function useAdminCategories() {
  const store = useAdminCategoryStore()
  const swal = useSwal()
  const { autoTranslateAfterSave, isTranslating } = useAutoTranslate()

  // Local state
  const showModal = ref(false)
  const editingCategory = ref<Category | null>(null)

  // Methods
  function generateSlug(text: string): string {
    return text
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
  }

  function handleNameChange() {
    if (!editingCategory.value && store.categoryForm.name) {
      store.categoryForm.slug = generateSlug(store.categoryForm.name)
    }
  }

  function openCreateModal() {
    editingCategory.value = null
    store.selectedCategory = null
    store.resetCategoryForm()
    showModal.value = true
  }

  async function openEditModal(category: Category) {
    try {
      // Fetch full category data by ID to ensure we have all fields
      const fullCategory = await adminCategoryService.getById(category.id)
      editingCategory.value = fullCategory
      store.selectedCategory = fullCategory
      store.categoryForm = {
        name: fullCategory.name,
        slug: fullCategory.slug || '',
        description: fullCategory.description || '',
        parent_id: fullCategory.parent_id?.toString() || ''
      }
    } catch (error) {
      console.error('Failed to fetch category details:', error)
      // Fallback to using category data from list
      editingCategory.value = category
      store.selectedCategory = category
      store.categoryForm = {
        name: category.name,
        slug: category.slug || '',
        description: category.description || '',
        parent_id: category.parent_id?.toString() || ''
      }
    }
    showModal.value = true
  }

  async function saveCategory() {
    if (store.isSaving) return
    if (!store.categoryForm.name) {
      await swal.warning('Vui lòng nhập tên danh mục!')
      return
    }

    try {
      const payload: any = {
        name: store.categoryForm.name,
        slug: store.categoryForm.slug || generateSlug(store.categoryForm.name),
        description: store.categoryForm.description
      }
      if (store.categoryForm.parent_id) {
        payload.parent_id = parseInt(store.categoryForm.parent_id)
      }

      if (editingCategory.value) {
        await store.updateCategory(editingCategory.value.id, payload)
        // Auto-translate after update
        await autoTranslateAfterSave(
          'App\\Models\\Category',
          editingCategory.value.id,
          { name: payload.name, description: payload.description || '' }
        )
        await swal.success('Cập nhật danh mục thành công!')
      } else {
        const newCategory = await store.createCategory(payload)
        // Auto-translate after create
        if (newCategory?.id) {
          await autoTranslateAfterSave(
            'App\\Models\\Category',
            newCategory.id,
            { name: payload.name, description: payload.description || '' }
          )
        }
        await swal.success('Tạo danh mục thành công!')
      }

      showModal.value = false
      editingCategory.value = null
      store.selectedCategory = null
    } catch (error: any) {
      console.error('Failed to save category:', error)
      await swal.error(error.response?.data?.message || 'Lưu thất bại!')
    }
  }

  async function deleteCategory(id: number) {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa danh mục này?')
    if (!confirmed) return

    try {
      await store.deleteCategory(id)
      await swal.success('Xóa danh mục thành công!')
    } catch (error: any) {
      console.error('Failed to delete category:', error)
      await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
  }

  return {
    // State
    showModal,
    editingCategory,
    isTranslating,
    // Methods
    generateSlug,
    handleNameChange,
    openCreateModal,
    openEditModal,
    saveCategory,
    deleteCategory
  }
}

