/**
 * Landing Products Composable
 * Provides reusable logic for product display and filtering
 */

import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Product, ProductFilters } from '../types'

export function useLandingProducts() {
  // State
  const products = ref<Product[]>([])
  const categories = ref<any[]>([])
  const isLoading = ref(true)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const searchQuery = ref('')
  const selectedCategory = ref<number | null>(null)
  
  // Advanced filters from mockup
  const selectedCategories = ref<number[]>([])
  const selectedBrands = ref<string[]>([])
  const selectedDimensions = ref<string[]>([])
  const selectedColor = ref<string | null>(null)
  
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

  function toggleCategory(categoryId: number) {
    const index = selectedCategories.value.indexOf(categoryId)
    if (index === -1) {
      selectedCategories.value.push(categoryId)
    } else {
      selectedCategories.value.splice(index, 1)
    }
    currentPage.value = 1
    fetchProducts()
  }

  function toggleBrand(brand: string) {
    const index = selectedBrands.value.indexOf(brand)
    if (index === -1) {
      selectedBrands.value.push(brand)
    } else {
      selectedBrands.value.splice(index, 1)
    }
    currentPage.value = 1
    fetchProducts()
  }

  function toggleDimension(dim: string) {
    const index = selectedDimensions.value.indexOf(dim)
    if (index === -1) {
      selectedDimensions.value.push(dim)
    } else {
      selectedDimensions.value.splice(index, 1)
    }
    currentPage.value = 1
    fetchProducts()
  }

  function setColor(color: string | null) {
    selectedColor.value = color
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
      const queryParams: Record<string, any> = {
        page: params?.page || currentPage.value,
        per_page: 12,
        active_category_only: 1
      }

      if (searchQuery.value) queryParams.search = searchQuery.value
      
      // Multiple categories support
      if (selectedCategories.value.length > 0) {
        queryParams.category_ids = selectedCategories.value
      } else if (selectedCategory.value) {
        queryParams.category_id = selectedCategory.value
      }
      
      // Other filters (future backend support)
      if (selectedBrands.value.length > 0) queryParams.brands = selectedBrands.value
      if (selectedColor.value) queryParams.color = selectedColor.value
      if (selectedDimensions.value.length > 0) queryParams.dimensions = selectedDimensions.value
      
      // Sort handling
      if (sortBy.value === 'price_asc') {
        queryParams.sort_by = 'price_asc'
      } else if (sortBy.value === 'price_desc') {
        queryParams.sort_by = 'price_desc'
      } else if (sortBy.value === 'newest') {
        queryParams.sort_by = 'latest'
      } else if (sortBy.value === 'popular') {
        queryParams.sort_by = 'latest' // Fallback for now
      }

      const response = await httpClient.get('/products', { params: queryParams })
      const data = response.data as any

      if (data?.data?.items && Array.isArray(data.data.items)) {
        products.value = data.data.items
        totalPages.value = data.data.meta?.last_page || 1
        currentPage.value = data.data.meta?.current_page || 1
      } else if (data?.data?.data && Array.isArray(data.data.data)) {
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
      const response = await httpClient.get('/categories', { params: { per_page: 50, is_active: 1 } })
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
    selectedCategories,
    selectedBrands,
    selectedDimensions,
    selectedColor,
    sortBy,
    // Methods
    formatPrice,
    setSearch,
    toggleCategory,
    toggleBrand,
    toggleDimension,
    setColor,
    setSortBy,
    changePage,
    fetchProducts,
    loadProducts
  }
}
