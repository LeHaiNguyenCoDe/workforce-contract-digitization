/**
 * Composable for Products
 * Provides reusable logic for product management
 */

import { ref, computed } from 'vue'
import { useSwal } from '@/utils'
import type { Product } from '../types'

export function useAdminProducts() {
  const store = useAdminProductStore()
  const swal = useSwal()

  // Local state
  const searchQuery = ref('')
  const showModal = ref(false)
  const editingProduct = ref<Product | null>(null)

  // Computed
  const filteredProducts = computed(() => {
    let result = store.products
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      result = result.filter(
        p =>
          p.name.toLowerCase().includes(query) ||
          p.slug?.toLowerCase().includes(query)
      )
    }
    return result
  })

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

  function formatPrice(price: number) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
  }

  function setSearchQuery(value: string) {
    searchQuery.value = value
    if (store.currentPage !== 1) {
      store.setPage(1)
    }
  }

  async function openCreateModal() {
    editingProduct.value = null
    store.selectedProduct = null
    store.resetProductForm()
    // Ensure categories are loaded
    if (store.categories.length === 0) {
      await store.fetchCategories()
    }
    showModal.value = true
  }

  async function openEditModal(product: Product) {
    try {
      // Fetch full product data by ID to ensure we have all fields
      const fullProduct = await adminProductService.getById(product.id)
      editingProduct.value = fullProduct
      store.selectedProduct = fullProduct
      store.productForm = {
        name: fullProduct.name,
        slug: fullProduct.slug || '',
        category_id: fullProduct.category_id?.toString() || fullProduct.category?.id?.toString() || '',
        price: fullProduct.price,
        sale_price: fullProduct.sale_price?.toString() || '',
        short_description: fullProduct.short_description || '',
        description: fullProduct.description || '',
        thumbnail: fullProduct.thumbnail || '',
        is_active: fullProduct.is_active ?? true
      }
    } catch (error) {
      console.error('Failed to fetch product details:', error)
      // Fallback to using product data from list
      editingProduct.value = product
      store.selectedProduct = product
      store.productForm = {
        name: product.name,
        slug: product.slug || '',
        category_id: product.category_id?.toString() || product.category?.id?.toString() || '',
        price: product.price,
        sale_price: product.sale_price?.toString() || '',
        short_description: product.short_description || '',
        description: product.description || '',
        thumbnail: product.thumbnail || '',
        is_active: product.is_active ?? true
      }
    }
    showModal.value = true
  }

  async function saveProduct() {
    if (store.isSaving) return

    // Get current form values directly from store (reactive)
    const form = store.productForm

    // Validate required fields - use actual form values
    const errors: string[] = []

    // Check name - trim and check
    const name = String(form.name || '').trim()
    if (!name) {
      errors.push('Tên sản phẩm')
    }

    // Check category_id - can be string or number
    const categoryId = form.category_id
    const categoryIdStr = String(categoryId || '').trim()
    const categoryIdNum = Number(categoryId)
    if (!categoryId || categoryIdStr === '' || categoryIdStr === '0' || categoryIdNum === 0 || isNaN(categoryIdNum)) {
      errors.push('Danh mục')
    }

    // Check price - allow 0 but not undefined/null/empty string
    const price = form.price
    if (price === undefined || price === null || String(price).trim() === '') {
      errors.push('Giá')
    }

    if (errors.length > 0) {
      console.log('Validation errors:', {
        name,
        categoryId,
        price,
        formName: form.name,
        formCategoryId: form.category_id,
        formPrice: form.price,
        fullForm: form
      })
      await swal.warning(`Vui lòng điền đầy đủ thông tin bắt buộc: ${errors.join(', ')}`)
      return
    }

    try {
      // Get fresh form values directly from store
      const form = store.productForm

      // Parse category_id - handle both string and number
      let categoryIdParsed = 0
      if (typeof form.category_id === 'string') {
        categoryIdParsed = parseInt(form.category_id) || 0
      } else if (typeof form.category_id === 'number') {
        categoryIdParsed = form.category_id
      }

      // Build payload with proper types
      const payload: any = {
        name: String(form.name || '').trim(),
        slug: form.slug || generateSlug(form.name),
        category_id: categoryIdParsed,
        price: Math.round(Number(form.price) || 0), // Backend expects integer
        is_active: form.is_active ?? true
      }

      // Add optional fields
      if (form.sale_price) {
        payload.sale_price = Math.round(Number(form.sale_price))
      }
      if (form.short_description) {
        payload.short_description = String(form.short_description).trim()
      }
      if (form.description) {
        payload.description = String(form.description).trim()
      }
      if (form.thumbnail) {
        payload.thumbnail = String(form.thumbnail).trim()
      }

      // Final validation after parsing
      if (!payload.name || payload.name === '') {
        await swal.warning('Tên sản phẩm không được để trống!')
        return
      }
      if (!payload.category_id || payload.category_id === 0) {
        await swal.warning('Vui lòng chọn danh mục!')
        return
      }

      // Debug log
      console.log('Saving product with payload:', payload)

      if (editingProduct.value) {
        await store.updateProduct(editingProduct.value.id, payload)
        await swal.success('Cập nhật sản phẩm thành công!')
      } else {
        await store.createProduct(payload)
        await swal.success('Tạo sản phẩm thành công!')
        // Reset to page 1 to see new product
        store.setPage(1)
      }

      showModal.value = false
      editingProduct.value = null
      store.selectedProduct = null
      store.resetProductForm()

      // Force refresh products list - reset to page 1 for new products
      // Use setTimeout to ensure modal is closed first
      setTimeout(async () => {
        await store.fetchProducts({ page: 1 })
      }, 100)
    } catch (error: any) {
      console.error('Failed to save product:', error)
      const errorData = error.response?.data

      // Show detailed validation errors
      if (errorData?.errors) {
        const errorMessages: string[] = []
        Object.keys(errorData.errors).forEach(key => {
          const messages = errorData.errors[key]
          if (Array.isArray(messages)) {
            errorMessages.push(...messages)
          } else {
            errorMessages.push(messages)
          }
        })
        await swal.error(errorMessages.join('\n') || 'Validation failed')
      } else {
        const message = errorData?.message || 'Lưu thất bại!'
        await swal.error(message)
      }
    }
  }

  async function deleteProduct(id: number) {
    console.log('[deleteProduct] Starting delete for id:', id)
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa sản phẩm này?')
    if (!confirmed) {
      console.log('[deleteProduct] User cancelled')
      return
    }

    try {
      console.log('[deleteProduct] Calling store.deleteProduct...')
      const result = await store.deleteProduct(id)
      console.log('[deleteProduct] Delete result:', result)
      await swal.success('Xóa sản phẩm thành công!')
    } catch (error: any) {
      console.error('[deleteProduct] Failed to delete product:', error)
      console.error('[deleteProduct] Error response:', error.response)
      await swal.error(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    }
  }

  function changePage(page: number) {
    store.setPage(page)
    store.fetchProducts({ search: searchQuery.value })
  }

  // Auto-generate slug from name (only for new products)
  function handleNameChange() {
    if (!editingProduct.value && store.productForm.name) {
      const productName = store.productForm.name.trim()

      // Auto-generate slug
      store.productForm.slug = generateSlug(productName)

      // Auto-select category if product name contains category name
      autoSelectCategory(productName)

      // Auto-generate short description and full description
      autoGenerateShortDescription(productName)
    }
  }

  // Auto-select category based on product name
  function autoSelectCategory(productName: string) {
    if (!productName || store.productForm.category_id || store.categories.length === 0) return // Skip if already selected or no categories

    const nameLower = productName.toLowerCase().trim()

    // Find category whose name is contained in product name (best match first)
    let matchedCategory = null
    let bestMatchLength = 0

    for (const cat of store.categories) {
      const catNameLower = cat.name.toLowerCase().trim()

      // Check if category name is in product name
      if (nameLower.includes(catNameLower)) {
        // Prefer longer category names (more specific)
        if (catNameLower.length > bestMatchLength) {
          bestMatchLength = catNameLower.length
          matchedCategory = cat
        }
      }
      // Also check reverse (product name in category name)
      else if (catNameLower.includes(nameLower) && nameLower.length >= 3) {
        if (nameLower.length > bestMatchLength) {
          bestMatchLength = nameLower.length
          matchedCategory = cat
        }
      }
    }

    if (matchedCategory) {
      store.productForm.category_id = matchedCategory.id.toString()
    }
  }

  // Auto-generate short description and full description
  function autoGenerateShortDescription(productName: string) {
    if (!productName) return

    // Use selected category if available, otherwise find matched category
    let categoryName = 'sản phẩm'
    if (store.productForm.category_id) {
      const selectedCategory = store.categories.find(cat => cat.id.toString() === store.productForm.category_id)
      if (selectedCategory) {
        categoryName = selectedCategory.name
      }
    } else {
      // Find matched category for context
      const matchedCategory = store.categories.find(cat => {
        const nameLower = productName.toLowerCase()
        const catNameLower = cat.name.toLowerCase()
        return nameLower.includes(catNameLower) || catNameLower.includes(nameLower)
      })
      if (matchedCategory) {
        categoryName = matchedCategory.name
      }
    }

    // Generate short description (only if empty)
    if (!store.productForm.short_description) {
      const shortDesc = `${productName} là ${categoryName} chất lượng cao, được thiết kế và sản xuất với tiêu chuẩn nghiêm ngặt. Sản phẩm phù hợp cho mọi nhu cầu sử dụng, mang lại trải nghiệm tuyệt vời cho người dùng.`
      store.productForm.short_description = shortDesc
    }

    // Generate full description (only if empty)
    if (!store.productForm.description) {
      const fullDesc = `## ${productName}

**Danh mục:** ${categoryName}

### Mô tả sản phẩm

${productName} là ${categoryName} được thiết kế với công nghệ tiên tiến, mang đến trải nghiệm sử dụng tuyệt vời cho người dùng. Sản phẩm được sản xuất với tiêu chuẩn chất lượng cao, đảm bảo độ bền và hiệu suất tối ưu.

### Đặc điểm nổi bật

- Chất lượng cao, đáng tin cậy
- Thiết kế hiện đại, sang trọng
- Hiệu suất vượt trội
- Phù hợp cho mọi nhu cầu sử dụng

### Thông tin chi tiết

${productName} là lựa chọn hoàn hảo cho những ai đang tìm kiếm một ${categoryName} chất lượng với giá trị tốt nhất. Sản phẩm được bảo hành chính hãng và có đầy đủ phụ kiện đi kèm.`
      store.productForm.description = fullDesc
    }
  }

  return {
    // State
    searchQuery,
    showModal,
    editingProduct,
    // Computed
    filteredProducts,
    // Methods
    generateSlug,
    formatPrice,
    setSearchQuery,
    openCreateModal,
    openEditModal,
    saveProduct,
    deleteProduct,
    changePage,
    handleNameChange
  }
}

