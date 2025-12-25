/**
 * Products Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Product, ProductFilters } from './types'
import type { Category } from '@/shared/types'

export const useProductStore = defineStore('admin-products', () => {
  // State
  const products = ref<Product[]>([])
  const categories = ref<Category[]>([])
  const selectedProduct = ref<Product | null>(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const filters = ref<ProductFilters>({})

  // Getters
  const hasProducts = computed(() => products.value.length > 0)
  const activeCategories = computed(() => categories.value.filter(c => c.is_active !== false))

  // Actions
  async function fetchProducts(params?: ProductFilters & { page?: number; per_page?: number }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = { 
        page: params?.page || currentPage.value, 
        per_page: 10 
      }
      
      if (params?.search) queryParams.search = params.search

      const response = await httpClient.get<{ data: { data: Product[]; last_page: number; current_page: number } | Product[] }>('/admin/products', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        products.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        products.value = data.data
        totalPages.value = 1
      } else {
        products.value = []
      }
    } catch (error) {
      console.error('Failed to fetch products:', error)
      products.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCategories() {
    try {
      const response = await httpClient.get<{ data: Category[] }>('/frontend/categories')
      const data = response.data as any
      if (Array.isArray(data?.data)) {
        categories.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    }
  }

  async function createProduct(payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await httpClient.post('/admin/products', payload)
      await fetchProducts()
      return true
    } catch (error) {
      console.error('Failed to create product:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updateProduct(id: number, payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await httpClient.put(`/admin/products/${id}`, payload)
      await fetchProducts()
      return true
    } catch (error) {
      console.error('Failed to update product:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deleteProduct(id: number): Promise<boolean> {
    try {
      await httpClient.delete(`/admin/products/${id}`)
      products.value = products.value.filter(p => p.id !== id)
      return true
    } catch (error) {
      console.error('Failed to delete product:', error)
      return false
    }
  }

  function setPage(page: number) {
    currentPage.value = page
  }

  function setFilters(newFilters: ProductFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function reset() {
    products.value = []
    selectedProduct.value = null
    currentPage.value = 1
    filters.value = {}
  }

  return {
    // State
    products,
    categories,
    selectedProduct,
    isLoading,
    isSaving,
    currentPage,
    totalPages,
    filters,
    // Getters
    hasProducts,
    activeCategories,
    // Actions
    fetchProducts,
    fetchCategories,
    createProduct,
    updateProduct,
    deleteProduct,
    setPage,
    setFilters,
    reset
  }
})
