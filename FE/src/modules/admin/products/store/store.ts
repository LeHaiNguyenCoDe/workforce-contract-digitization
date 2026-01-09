/**
 * Products Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminProductService } from '@/plugins/api/services/ProductService'
import httpClient from '@/plugins/api/httpClient'
import type { Product, ProductFilters } from '../types'
import type { Category } from '@/types'

export const useAdminProductStore = defineStore('admin-products', () => {
  // State
  const products = ref<Product[]>([])
  const categories = ref<Category[]>([])
  const selectedProduct = ref<Product | null>(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const filters = ref<ProductFilters>({})

  // Forms
  const productForm = ref({
    name: '',
    slug: '',
    category_id: '',
    price: 0,
    sale_price: '',
    short_description: '',
    description: '',
    thumbnail: '',
    is_active: true
  })

  // Getters
  const hasProducts = computed(() => products.value.length > 0)
  const activeCategories = computed(() => categories.value.filter(c => c.is_active !== false))

  // Actions
  async function fetchProducts(params?: ProductFilters & { page?: number; per_page?: number }) {
    isLoading.value = true
    try {
      // Update current page if provided
      if (params?.page) {
        currentPage.value = params.page
      }

      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value,
        per_page: params?.per_page || 10
        // Don't filter by stock - show all products in admin/products
        // only_with_stock is for warehouse products, not admin products
      }

      if (params?.search) queryParams.search = params.search

      // Add cache busting timestamp to ensure fresh data
      queryParams._t = Date.now()

      const response = await adminProductService.getAll(queryParams)
      
      products.value = response?.items || []
      totalPages.value = response?.meta?.last_page || 1
      currentPage.value = response?.meta?.current_page || 1
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
      await adminProductService.create(payload as any)
      // Reset to page 1 to see new product
      currentPage.value = 1
      // Force refresh - clear cache by adding timestamp
      await fetchProducts({ page: 1 })
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
      await adminProductService.update(id, payload as any)
      // Refresh current page to see updated product
      await fetchProducts({ page: currentPage.value })
      return true
    } catch (error) {
      console.error('Failed to update product:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deleteProduct(id: number): Promise<boolean> {
    console.log('[store.deleteProduct] Starting delete for id:', id)
    try {
      console.log('[store.deleteProduct] Calling adminProductService.delete...')
      await adminProductService.delete(id)
      console.log('[store.deleteProduct] API call successful')
      // Remove from current list
      const beforeCount = products.value.length
      products.value = products.value.filter(p => p.id !== id)
      console.log('[store.deleteProduct] Removed from list. Before:', beforeCount, 'After:', products.value.length)
      // Refresh to update pagination if needed
      await fetchProducts({ page: currentPage.value })
      console.log('[store.deleteProduct] Products refreshed. Count:', products.value.length)
      return true
    } catch (error) {
      console.error('[store.deleteProduct] Failed to delete product:', error)
      throw error // Re-throw to let composable handle the error
    }
  }

  function setPage(page: number) {
    currentPage.value = page
  }

  function setFilters(newFilters: ProductFilters) {
    filters.value = { ...filters.value, ...newFilters }
    if (currentPage.value !== 1) {
      currentPage.value = 1
    }
  }

  function resetProductForm() {
    productForm.value = {
      name: '',
      slug: '',
      category_id: '',
      price: 0,
      sale_price: '',
      short_description: '',
      description: '',
      thumbnail: '',
      is_active: true
    }
  }

  function reset() {
    products.value = []
    selectedProduct.value = null
    currentPage.value = 1
    filters.value = {}
    resetProductForm()
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
    productForm,
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
    resetProductForm,
    reset
  }
})

