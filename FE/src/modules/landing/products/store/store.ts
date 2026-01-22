/**
 * Landing Products Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Product, ProductFilters } from './types'
import type { Category } from '@/types'

export const useLandingProductStore = defineStore('landing-products', () => {
  // State
  const products = ref<Product[]>([])
  const featuredProducts = ref<Product[]>([])
  const currentProduct = ref<Product | null>(null)
  const categories = ref<Category[]>([])
  const isLoading = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const total = ref(0)
  const filters = ref<ProductFilters>({})

  // Getters
  const hasProducts = computed(() => products.value.length > 0)
  const hasFeatured = computed(() => featuredProducts.value.length > 0)

  // Actions
  async function fetchProducts(params?: ProductFilters & { page?: number; per_page?: number }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value,
        per_page: params?.per_page || 12
      }

      if (params?.search) queryParams.search = params.search
      if (params?.category_id) queryParams.category_id = params.category_id
      if (params?.min_price) queryParams.min_price = params.min_price
      if (params?.max_price) queryParams.max_price = params.max_price
      if (params?.sort_by) queryParams.sort_by = params.sort_by
      if (params?.sort_order) queryParams.sort_order = params.sort_order

      const response = await httpClient.get<any>('/products', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        products.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
        total.value = data.data.total || 0
      } else if (Array.isArray(data?.data)) {
        products.value = data.data
        totalPages.value = 1
        total.value = data.data.length
      }
    } catch (error) {
      console.error('Failed to fetch products:', error)
      products.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchFeaturedProducts(limit = 8) {
    try {
      const response = await httpClient.get<any>('/products', {
        params: { per_page: limit, featured: true }
      })
      const data = response.data as any
      featuredProducts.value = Array.isArray(data?.data?.data)
        ? data.data.data
        : Array.isArray(data?.data)
          ? data.data
          : []
    } catch (error) {
      console.error('Failed to fetch featured products:', error)
    }
  }

  async function fetchProductById(id: number | string): Promise<Product | null> {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>(`/products/${id}`)
      const data = response.data as any
      currentProduct.value = data?.data || data
      return currentProduct.value
    } catch (error) {
      console.error('Failed to fetch product:', error)
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCategories() {
    try {
      const response = await httpClient.get<any>('/categories')
      const data = response.data as any
      categories.value = Array.isArray(data?.data) ? data.data : []
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    }
  }

  async function fetchProductsByCategory(categoryId: number, params?: { page?: number; per_page?: number }) {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>(`/categories/${categoryId}/products`, {
        params: { page: params?.page || 1, per_page: params?.per_page || 12 }
      })
      const data = response.data as any

      if (data?.data?.data) {
        products.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        products.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch category products:', error)
    } finally {
      isLoading.value = false
    }
  }

  function setFilters(newFilters: ProductFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function clearFilters() {
    filters.value = {}
  }

  function reset() {
    products.value = []
    currentProduct.value = null
    currentPage.value = 1
    filters.value = {}
  }

  return {
    // State
    products,
    featuredProducts,
    currentProduct,
    categories,
    isLoading,
    currentPage,
    totalPages,
    total,
    filters,
    // Getters
    hasProducts,
    hasFeatured,
    // Actions
    fetchProducts,
    fetchFeaturedProducts,
    fetchProductById,
    fetchCategories,
    fetchProductsByCategory,
    setFilters,
    clearFilters,
    reset
  }
})
