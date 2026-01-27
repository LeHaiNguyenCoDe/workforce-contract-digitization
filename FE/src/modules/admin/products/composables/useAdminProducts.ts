/**
 * Composable for Products
 * Provides reusable logic for product management
 */

import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useSwal } from '@/utils'
import { useAdminProductStore } from '../store/store'
import { adminProductService } from '@/plugins/api/services/ProductService'
import type { Product } from '../types'

export function useAdminProducts() {
  const store = useAdminProductStore()
  const swal = useSwal()
  const router = useRouter()

  // Local state
  const searchQuery = ref('')
  const selectedCategoryId = ref<number | null>(null)
  const priceRange = ref({ min: 0, max: 20000000 })
  const selectedBrands = ref<string[]>([])
  const selectedRating = ref<number | null>(null)
  const activeTab = ref(0)
  const showModal = ref(false)
  const editingProduct = ref<Product | null>(null)
  const errors = ref<Record<string, string>>({})

  // Computed
  const filteredProducts = computed(() => {
    let result = store.products

    // 1. Search Query
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      result = result.filter(
        p =>
          p.name.toLowerCase().includes(query) ||
          p.slug?.toLowerCase().includes(query)
      )
    }

    // 2. Category Filter
    if (selectedCategoryId.value) {
      result = result.filter(p => Number(p.category_id) === selectedCategoryId.value || p.category?.id === selectedCategoryId.value)
    }

    // 3. Price Range Filter
    if (priceRange.value.min !== undefined || priceRange.value.max !== undefined) {
      result = result.filter(p => {
        const price = p.price || 0
        const min = priceRange.value.min || 0
        const max = priceRange.value.max || Infinity
        return price >= min && price <= max
      })
    }

    // 4. Brand Filter
    if (selectedBrands.value.length > 0) {
      result = result.filter(p => {
        const brand = p.manufacturer_brand || ''
        return selectedBrands.value.includes(brand)
      })
    }

    // 5. Rating Filter
    if (selectedRating.value) {
      result = result.filter(p => (Number(p.rating?.avg) || 0) >= selectedRating.value!)
    }

    // 6. Publication Status Tab Filter
    const tabIdx = Number(activeTab.value)
    if (tabIdx === 1) { // Published
      result = result.filter(p => p.is_active)
    } else if (tabIdx === 2) { // Draft
      result = result.filter(p => !p.is_active)
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
      console.log('DEBUG: fullProduct', fullProduct)
      console.log('DEBUG: fullProduct.images', fullProduct.images)
      
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
        is_active: fullProduct.is_active ?? true,
        manufacturer_name: fullProduct.manufacturer_name || '',
        manufacturer_brand: fullProduct.manufacturer_brand || '',
        stock_quantity: fullProduct.stock_quantity || 0,
        discount_percentage: fullProduct.discount_percentage || 0,
        orders_count: fullProduct.orders_count || 0,
        published_at: fullProduct.published_at || '',
        tags: fullProduct.tags || [],
        visibility: (fullProduct as any).visibility || 'public',
        images: fullProduct.images?.map((img: any) => img.image_url || img.url || '').filter(Boolean) || [],
        specs: fullProduct.specs || {},
        variants: fullProduct.variants || [],
        faqs: (fullProduct as any).faqs || []
      }
      console.log('DEBUG: Mapped images', store.productForm.images)
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
        is_active: product.is_active ?? true,
        manufacturer_name: product.manufacturer_name || '',
        manufacturer_brand: product.manufacturer_brand || '',
        stock_quantity: product.stock_quantity || 0,
        discount_percentage: product.discount_percentage || 0,
        orders_count: product.orders_count || 0,
        published_at: product.published_at || '',
        tags: product.tags || [],
        visibility: (product as any).visibility || 'public',
        images: product.images?.map((img: any) => JSON.stringify(img)) || [],
        specs: (product as any).specs || {},
        variants: (product as any).variants || [],
        faqs: (product as any).faqs || []
      }
    showModal.value = true
  }
  }

  function validate() {
    errors.value = {}
    const form = store.productForm
    let isValid = true

    if (!form.name || !form.name.trim()) {
      errors.value.name = 'Tên sản phẩm là bắt buộc'
      isValid = false
    }

    if (!form.category_id || form.category_id === '0') {
      errors.value.category_id = 'Vui lòng chọn danh mục'
      isValid = false
    }

    if (form.price === undefined || form.price === null || form.price === 0) {
      errors.value.price = 'Giá sản phẩm là bắt buộc và phải lớn hơn 0'
      isValid = false
    }

    return isValid
  }

  async function saveProduct() {
    if (store.isSaving) return

    // Get current form values directly from store (reactive)
    const form = store.productForm

    // Validate required fields
    if (!validate()) {
      return false
    }

    const name = String(form.name || '').trim()

    try {
      const payload: any = {
        name: name,
        slug: form.slug || generateSlug(name),
        category_id: Number(form.category_id),
        price: Math.round(Number(form.price) || 0),
        is_active: form.is_active ?? true,
        sale_price: form.sale_price ? Math.round(Number(form.sale_price)) : null,
        short_description: form.short_description,
        description: form.description,
        thumbnail: form.thumbnail,
        manufacturer_name: form.manufacturer_name,
        manufacturer_brand: form.manufacturer_brand,
        stock_quantity: Number(form.stock_quantity) || 0,
        discount_percentage: Number(form.discount_percentage) || 0,
        published_at: form.published_at,
        tags: form.tags,
        visibility: form.visibility,
        images: form.images,
        specs: form.specs,
        variants: form.variants,
        faqs: form.faqs
      }


      if (editingProduct.value) {
        await store.updateProduct(editingProduct.value.id, payload)
        await swal.success('Cập nhật sản phẩm thành công!')
      } else {
        await store.createProduct(payload)
        await swal.success('Tạo sản phẩm thành công!')
        store.setPage(1)
      }

      showModal.value = false
      editingProduct.value = null
      store.resetProductForm()
      
      return true
    } catch (error: any) {
      console.error('Failed to save product:', error)
      const message = error.response?.data?.message || 'Lưu thất bại!'
      await swal.error(message)
      return false
    }
  }

  async function deleteProduct(id: number) {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa sản phẩm này?')
    if (!confirmed) return

    try {
      await store.deleteProduct(id)
      await swal.success('Xóa sản phẩm thành công!')
    } catch (error: any) {
      await swal.error(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    }
  }

  async function deleteProducts(ids: number[]) {
    if (!ids.length) return
    
    const confirmed = await swal.confirmDelete(`Bạn có chắc muốn xóa ${ids.length} sản phẩm đã chọn?`)
    if (!confirmed) return

    store.isLoading = true
    try {
      // Delete all selected products
      await Promise.all(ids.map(id => store.deleteProduct(id)))
      await swal.success(`Đã xóa ${ids.length} sản phẩm thành công!`)
    } catch (error: any) {
      await swal.error(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    } finally {
      store.isLoading = false
    }
  }

  function changePage(page: number) {
    store.setPage(page)
    store.fetchProducts({ search: searchQuery.value })
  }

  function handleNameChange() {
    if (!editingProduct.value && store.productForm.name) {
      const productName = store.productForm.name.trim()
      store.productForm.slug = generateSlug(productName)
      autoSelectCategory(productName)
      autoGenerateShortDescription(productName)
    }
  }

  function autoSelectCategory(productName: string) {
    if (!productName || store.productForm.category_id || store.categories.length === 0) return
    const nameLower = productName.toLowerCase().trim()
    let matchedCategory: any = null
    let bestMatchLength = 0

    for (const cat of store.categories) {
      const catNameLower = cat.name.toLowerCase().trim()
      if (nameLower.includes(catNameLower) && catNameLower.length > bestMatchLength) {
        bestMatchLength = catNameLower.length
        matchedCategory = cat
      }
    }
    if (matchedCategory) {
      store.productForm.category_id = matchedCategory.id.toString()
    }
  }

  function autoGenerateShortDescription(productName: string) {
    if (!productName || store.productForm.short_description) return
    let categoryName = 'sản phẩm'
    if (store.productForm.category_id) {
      const cat = store.categories.find(c => c.id.toString() === store.productForm.category_id)
      if (cat) categoryName = cat.name
    }
    const shortDesc = `${productName} là ${categoryName} chất lượng cao, thiết kế hiện đại, phù hợp cho mọi nhu cầu sử dụng.`
    store.productForm.short_description = shortDesc
  }

  async function navigateToCreate() {
    store.resetProductForm()
    editingProduct.value = null
    router.push({ name: 'admin-products-create' })
  }

  async function navigateToEdit(id: number) {
    router.push({ name: 'admin-products-edit', params: { id } })
  }

  async function navigateToView(id: number) {
    router.push({ name: 'admin-products-view', params: { id } })
  }

  async function loadProductForForm(id: number) {
    try {
      store.isLoading = true
      const fullProduct = await adminProductService.getById(id)
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
        is_active: fullProduct.is_active ?? true,
        manufacturer_name: fullProduct.manufacturer_name || '',
        manufacturer_brand: fullProduct.manufacturer_brand || '',
        stock_quantity: fullProduct.stock_quantity || 0,
        discount_percentage: fullProduct.discount_percentage || 0,
        orders_count: fullProduct.orders_count || 0,
        published_at: fullProduct.published_at || '',
        tags: fullProduct.tags || [],
        visibility: (fullProduct as any).visibility || 'public',
        images: fullProduct.images?.map((img: any) => img.image_url || img.url || '').filter(Boolean) || [],
        specs: fullProduct.specs || {},
        variants: fullProduct.variants || [],
        faqs: (fullProduct as any).faqs || []
      }
    } catch (error) {
      console.error('Failed to load product for form:', error)
      swal.error('Không thể tải thông tin sản phẩm')
      router.push({ name: 'admin-products' })
    } finally {
      store.isLoading = false
    }
  }

  return {
    // State
    searchQuery,
    showModal,
    editingProduct,
    selectedCategoryId,
    priceRange,
    selectedBrands,
    selectedRating,
    activeTab,
    errors,
    // Computed
    filteredProducts,
    // Methods
    generateSlug,
    formatPrice,
    setSearchQuery,
    openCreateModal,
    openEditModal,
    navigateToCreate,
    navigateToEdit,
    navigateToView,
    loadProductForForm,
    saveProduct,
    deleteProduct,
    deleteProducts,
    changePage,
    handleNameChange
  }
}
