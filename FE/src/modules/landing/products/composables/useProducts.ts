/**
 * Landing Products Composable
 * Provides reusable logic for product display and filtering
 */

import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Product, ProductFilters } from '../types'

export function useProducts() {
  // State
  const products = ref<Product[]>([])
  const categories = ref<any[]>([])
  const isLoading = ref(true)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const searchQuery = ref('')
  const selectedCategory = ref<number | null>(null)
  const sortBy = ref<'newest' | 'price_asc' | 'price_desc' | 'popular'>('newest')

  // Methods
  function formatPrice(price: number) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
  }

  function setSearch(query: string) {
    searchQuery.value = query
    currentPage.value = 1
    fetchProducts()
  }

  function setCategory(categoryId: number | null) {
    selectedCategory.value = categoryId
    currentPage.value = 1
    fetchProducts()
  }

  function setSortBy(sort: typeof sortBy.value) {
    sortBy.value = sort
    fetchProducts()
  }

  async function fetchProducts(params?: ProductFilters & { page?: number }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value,
        per_page: 12
      }

      if (searchQuery.value) queryParams.search = searchQuery.value
      if (selectedCategory.value) queryParams.category_id = selectedCategory.value
      
      // Sort handling
      if (sortBy.value === 'price_asc') {
        queryParams.sort_by = 'price'
        queryParams.sort_order = 'asc'
      } else if (sortBy.value === 'price_desc') {
        queryParams.sort_by = 'price'
        queryParams.sort_order = 'desc'
      } else if (sortBy.value === 'newest') {
        queryParams.sort_by = 'created_at'
        queryParams.sort_order = 'desc'
      }

      const response = await httpClient.get('/frontend/products', { params: queryParams })
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
      const response = await httpClient.get('/frontend/categories', { params: { per_page: 20 } })
      const data = response.data as any
      categories.value = Array.isArray(data?.data) ? data.data : []
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    }
  }

  function changePage(page: number) {
    currentPage.value = page
    fetchProducts({ page })
  }

  async function loadProducts() {
    await Promise.all([fetchCategories(), fetchProducts()])
  }

  onMounted(loadProducts)

  return {
    // State
    products,
    categories,
    isLoading,
    currentPage,
    totalPages,
    searchQuery,
    selectedCategory,
    sortBy,
    // Methods
    formatPrice,
    setSearch,
    setCategory,
    setSortBy,
    changePage,
    fetchProducts,
    loadProducts
  }
}
