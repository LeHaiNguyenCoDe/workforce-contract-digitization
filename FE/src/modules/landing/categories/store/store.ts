/**
 * Landing Categories Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { CategoryWithProducts } from '../types'
import { buildCategoryTree } from '../helpers'

export const useLandingCategoryStore = defineStore('landing-categories', () => {
  // State
  const categories = ref<CategoryWithProducts[]>([])
  const isLoading = ref(false)

  // Getters
  const categoryTree = computed(() => buildCategoryTree(categories.value))
  const hasCategories = computed(() => categories.value.length > 0)

  // Actions
  async function fetchCategories() {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>('/categories', {
        params: { is_active: 1 }
      })
      const data = response.data as any
      categories.value = Array.isArray(data?.data) ? data.data : []
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCategoryById(id: number | string): Promise<CategoryWithProducts | null> {
    try {
      const response = await httpClient.get<any>(`/categories/${id}`)
      const data = response.data as any
      return data?.data || null
    } catch (error) {
      console.error('Failed to fetch category:', error)
      return null
    }
  }

  function reset() {
    categories.value = []
  }

  return {
    categories,
    isLoading,
    categoryTree,
    hasCategories,
    fetchCategories,
    fetchCategoryById,
    reset
  }
})
