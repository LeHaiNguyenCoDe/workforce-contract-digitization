/**
 * Composable for Categories (Part of Products Module)
 * Provides reusable logic for category management
 */

import { ref } from 'vue'
import { useSwal } from '@/utils'
import { adminCategoryService } from '@/plugins/api/services/CategoryService'
import { useAutoTranslate } from '@/composables/useAutoTranslate'
import { useAdminCategoryStore, type Category } from '../store/categoryStore'

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
      const fullCategory = await adminCategoryService.getById(category.id)
      const cat = fullCategory as any
      editingCategory.value = cat as Category
      store.selectedCategory = cat as Category
      store.categoryForm = {
        name: cat.name,
        slug: cat.slug || '',
        description: cat.description || '',
        parent_id: cat.parent_id?.toString() || '',
        is_active: cat.is_active === null || cat.is_active === undefined ? true : Number(cat.is_active) !== 0,
        image: cat.image || ''
      }
    } catch (error) {
      console.error('Failed to fetch category details:', error)
      const cat = category as any
      editingCategory.value = cat as Category
      store.selectedCategory = cat as Category
      store.categoryForm = {
        name: cat.name,
        slug: cat.slug || '',
        description: cat.description || '',
        parent_id: cat.parent_id?.toString() || '',
        is_active: cat.is_active === null || cat.is_active === undefined ? true : Number(cat.is_active) !== 0,
        image: cat.image || ''
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
        description: store.categoryForm.description,
        is_active: store.categoryForm.is_active ? 1 : 0,
        image: store.categoryForm.image
      }
      if (store.categoryForm.parent_id) {
        payload.parent_id = parseInt(store.categoryForm.parent_id)
      }

      if (editingCategory.value) {
        const categoryId = editingCategory.value.id
        const categoryName = payload.name
        const categoryDesc = payload.description || ''

        await store.updateCategory(categoryId, payload)
        
        // Show success immediately
        swal.success('Cập nhật danh mục thành công!')
        showModal.value = false

        // Run translation in background
        autoTranslateAfterSave(
          'App\\Models\\Category',
          categoryId,
          { name: categoryName, description: categoryDesc }
        ).catch(err => console.error('Translation failed:', err))

      } else {
        const newCategory = await store.createCategory(payload)
        
        // Show success immediately
        swal.success('Tạo danh mục thành công!')
        showModal.value = false

        // Run translation in background
        if (newCategory?.id) {
          autoTranslateAfterSave(
            'App\\Models\\Category',
            newCategory.id,
            { name: payload.name, description: payload.description || '' }
          ).catch(err => console.error('Translation failed:', err))
        }
      }

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
