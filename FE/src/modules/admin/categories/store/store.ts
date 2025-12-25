/**
 * Categories Store
 * Manages state for category management
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminCategoryService } from '@/plugins/api/services/CategoryService'
import httpClient from '@/plugins/api/httpClient'

export interface Category {
  id: number
  name: string
  slug: string
  description?: string
  parent_id?: number
  parent?: Category
  children?: Category[]
  products_count?: number
  is_active?: boolean
}

export const useCategoryStore = defineStore('admin-categories', () => {
  // State
  const categories = ref<Category[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const selectedCategory = ref<Category | null>(null)

  // Forms
  const categoryForm = ref({
    name: '',
    slug: '',
    description: '',
    parent_id: ''
  })

  // Getters
  const hasCategories = computed(() => categories.value.length > 0)

  // Actions
  async function fetchCategories() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/admin/categories')
      const data = response.data as any
      if (data?.data?.data && Array.isArray(data.data.data)) {
        categories.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        categories.value = data.data
      } else {
        categories.value = []
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error)
      categories.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function createCategory(payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminCategoryService.create(payload)
      await fetchCategories()
      return true
    } catch (error) {
      console.error('Failed to create category:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updateCategory(id: number, payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminCategoryService.update(id, payload)
      await fetchCategories()
      return true
    } catch (error) {
      console.error('Failed to update category:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deleteCategory(id: number): Promise<boolean> {
    try {
      await adminCategoryService.delete(id)
      categories.value = categories.value.filter(c => c.id !== id)
      return true
    } catch (error) {
      console.error('Failed to delete category:', error)
      return false
    }
  }

  function resetCategoryForm() {
    categoryForm.value = {
      name: '',
      slug: '',
      description: '',
      parent_id: ''
    }
  }

  function reset() {
    categories.value = []
    selectedCategory.value = null
    resetCategoryForm()
  }

  return {
    // State
    categories,
    isLoading,
    isSaving,
    selectedCategory,
    categoryForm,
    // Getters
    hasCategories,
    // Actions
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    resetCategoryForm,
    reset
  }
})

