/**
 * Composable for Warehouse Products
 * Provides reusable logic for warehouse products management
 */

import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSwal } from '@/shared/utils'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import type { WarehouseProduct } from '../store/store'

export function useWarehouseProducts() {
  const store = useWarehouseStore()
  const route = useRoute()
  const swal = useSwal()

  // Local state
  const searchQuery = ref('')
  const statusFilter = ref((route.query.filter as string) || '')
  const currentPage = ref(1)
  const perPage = ref(20)
  const selectedProducts = ref<Set<number>>(new Set())
  const isDeleting = ref(false)
  const showStockModal = ref(false)

  // Computed
  const filteredProducts = computed(() => {
    let result = store.products
    if (statusFilter.value) {
      result = result.filter(p => p.status === statusFilter.value)
    }
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      result = result.filter(
        p => p.name.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query)
      )
    }
    return result
  })

  const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredProducts.value.slice(start, end)
  })

  const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage.value))

  const isAllSelected = computed(() => {
    if (paginatedProducts.value.length === 0) return false
    return paginatedProducts.value.every(product => selectedProducts.value.has(product.id))
  })

  const isSomeSelected = computed(() => {
    return paginatedProducts.value.some(product => selectedProducts.value.has(product.id))
  })

  const selectedCount = computed(() => selectedProducts.value.size)

  // Methods để set search và filter, tự động reset page
  function setSearchQuery(value: string) {
    searchQuery.value = value
    if (currentPage.value !== 1) {
      currentPage.value = 1
    }
  }

  function setStatusFilter(value: string) {
    statusFilter.value = value
    if (currentPage.value !== 1) {
      currentPage.value = 1
    }
  }

  // Methods
  function changePage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  function toggleSelectProduct(productId: number) {
    if (selectedProducts.value.has(productId)) {
      selectedProducts.value.delete(productId)
    } else {
      selectedProducts.value.add(productId)
    }
  }

  function toggleSelectAll() {
    if (isAllSelected.value) {
      paginatedProducts.value.forEach(product => {
        selectedProducts.value.delete(product.id)
      })
    } else {
      paginatedProducts.value.forEach(product => {
        selectedProducts.value.add(product.id)
      })
    }
  }

  function getStockClass(product: WarehouseProduct) {
    if (product.quantity === 0) return 'bg-error/10 text-error'
    if (product.quantity <= product.minStock) return 'bg-warning/10 text-warning'
    return 'bg-success/10 text-success'
  }

  async function openStockModal(product: WarehouseProduct, type: 'in' | 'adjust' | 'outbound' = 'in') {
    console.log('openStockModal called with:', product, type)

    // Get latest product from store
    const latestProduct = store.products.find(p => p.id === product.id) || product

    // Fetch latest stock quantity from warehouse to ensure we have the most up-to-date quantity
    let currentStockQuantity = 0
    try {
      // Only fetch if warehouse ID is valid
      if (store.selectedWarehouseId && store.selectedWarehouseId > 0) {
        console.log('Fetching stocks for warehouse (stock modal):', store.selectedWarehouseId, 'product:', product.id)
        const stocks = await warehouseService.getStocks(store.selectedWarehouseId)
        console.log('Stocks fetched (stock modal):', stocks.length, 'stocks')
        const stock = stocks.find(s => s.product_id === product.id)
        if (stock) {
          currentStockQuantity = Number(stock.quantity) || 0
          console.log('Found stock for product (stock modal):', product.id, 'quantity:', currentStockQuantity)
          // Update product quantity in store to keep it in sync
          const productIndex = store.products.findIndex(p => p.id === product.id)
          if (productIndex !== -1) {
            store.products[productIndex].quantity = currentStockQuantity
            console.log('Updated product in list (stock modal), new quantity:', currentStockQuantity)
          }
          latestProduct.quantity = currentStockQuantity
        } else {
          console.warn('Stock not found for product (stock modal):', product.id, 'using 0')
          currentStockQuantity = 0
        }
      } else {
        console.warn('Invalid warehouse ID:', store.selectedWarehouseId, 'using quantity from list as fallback')
        currentStockQuantity = latestProduct.quantity || 0
      }
    } catch (stockError: any) {
      console.error('Failed to fetch stock data (stock modal):', stockError)
      console.warn('Using quantity from product list as fallback:', latestProduct.quantity)
      // Use quantity from product list as fallback only if API fails
      currentStockQuantity = latestProduct.quantity || 0
    }

    console.log('Final currentStockQuantity for stock modal:', currentStockQuantity)

    store.selectedProduct = latestProduct

    // Check if same product and type as last time
    if (
      store.lastStockFormData.productId === product.id &&
      store.lastStockFormData.type === type
    ) {
      // Keep last values
      store.stockForm = {
        type: store.lastStockFormData.type,
        quantity: store.lastStockFormData.quantity,
        reason: store.lastStockFormData.reason,
        note: store.lastStockFormData.note
      }
    } else {
      // Reset to default
      const availableQty = latestProduct.available_quantity || 0
      store.stockForm = {
        type,
        quantity: type === 'outbound' ? Math.min(1, availableQty) : (type === 'in' ? 1 : currentStockQuantity),
        reason: type === 'adjust' ? '' : undefined,
        note: ''
      }
    }
    showStockModal.value = true
    console.log('showStockModal set to:', showStockModal.value, 'Current stock:', currentStockQuantity)
  }

  async function handleStockUpdate() {
    if (!store.selectedProduct || store.isSubmitting) return

    // Validate quantity
    if (!store.stockForm.quantity || store.stockForm.quantity <= 0) {
      await swal.warning('Vui lòng nhập số lượng lớn hơn 0!')
      return
    }

    // BR-05.2: Validate reason for adjust
    if (store.stockForm.type === 'adjust' && !store.stockForm.reason?.trim()) {
      await swal.warning('Vui lòng nhập lý do điều chỉnh!')
      return
    }

    // BR-06.2: Validate outbound quantity
    if (store.stockForm.type === 'outbound') {
      const availableQty = Number(store.selectedProduct.available_quantity) || 0
      const requestedQty = Number(store.stockForm.quantity) || 0
      if (requestedQty > availableQty) {
        await swal.warning(`Không thể xuất vượt tồn kho có thể xuất! Có thể xuất: ${availableQty}`)
        return
      }
    }

    try {
      const quantity = Math.abs(Math.floor(Number(store.stockForm.quantity)))
      const noteValue = store.stockForm.note ? String(store.stockForm.note).trim() : ''

      let updatedStock: any

      if (store.stockForm.type === 'outbound') {
        // BR-06.1, BR-06.2: Xuất kho
        updatedStock = await warehouseService.outboundStock(store.selectedWarehouseId, {
          product_id: store.selectedProduct.id,
          quantity: quantity,
          note: noteValue
        })
      } else if (store.stockForm.type === 'adjust') {
        // BR-05.1, BR-05.2, BR-05.3: Điều chỉnh tồn kho
        updatedStock = await warehouseService.adjustStock(store.selectedWarehouseId, {
          product_id: store.selectedProduct.id,
          quantity: quantity,
          available_quantity: quantity, // Set available_quantity same as quantity for adjust
          reason: String(store.stockForm.reason).trim(),
          note: noteValue
        })
      } else {
        // Legacy: Nhập kho (nên dùng Inbound Batch thay vì)
        updatedStock = await store.updateStock({
          product_id: store.selectedProduct.id,
          quantity: quantity,
          type: store.stockForm.type,
          note: noteValue
        })
      }

      // Get new quantity from API response
      const newQuantity = Number(updatedStock.quantity) || 0
      const newAvailableQuantity = Number(updatedStock.available_quantity) || newQuantity
      const productId = store.selectedProduct!.id

      console.log('Stock updated successfully, new quantity:', newQuantity, 'available:', newAvailableQuantity, 'for product:', productId)

      // Update product in list immediately to show instant feedback in table
      const productIndex = store.products.findIndex(p => p.id === productId)
      if (productIndex !== -1) {
        store.products[productIndex].quantity = newQuantity
        store.products[productIndex].available_quantity = newAvailableQuantity
        console.log('Updated product in list immediately, index:', productIndex, 'new quantity:', store.products[productIndex].quantity)
      } else {
        console.warn('Product not found in list for immediate update:', productId)
      }

      // Update selectedProduct quantity if it's still the same product
      if (store.selectedProduct && store.selectedProduct.id === productId) {
        store.selectedProduct.quantity = newQuantity
        store.selectedProduct.available_quantity = newAvailableQuantity
        console.log('Updated selectedProduct quantity to:', newQuantity)
      }

      // Close modal first to show immediate feedback
      showStockModal.value = false
      store.selectedProduct = null

      // Show success message
      await swal.success('Cập nhật tồn kho thành công!')

      // Refresh products list from server to ensure everything is in sync
      // Use a small delay to ensure server has processed the update
      console.log('Refreshing products list after stock update...')
      await new Promise(resolve => setTimeout(resolve, 200)) // Small delay to ensure server is ready
      await store.fetchProducts()

      // Verify the update was applied
      const refreshedProduct = store.products.find(p => p.id === productId)
      if (refreshedProduct) {
        console.log('After refresh, product quantity:', refreshedProduct.quantity, 'available:', refreshedProduct.available_quantity, 'expected quantity:', newQuantity, 'expected available:', newAvailableQuantity)
        // Update if mismatch
        if (refreshedProduct.quantity !== newQuantity) {
          console.warn('Quantity mismatch after refresh! Updating manually...')
          refreshedProduct.quantity = newQuantity
        }
        if (refreshedProduct.available_quantity !== newAvailableQuantity) {
          console.warn('Available quantity mismatch after refresh! Updating manually...')
          refreshedProduct.available_quantity = newAvailableQuantity
        }
      } else {
        console.warn('Product not found after refresh:', productId)
      }
    } catch (error: any) {
      console.error('Stock update error:', error)
      const errorMessage =
        error.response?.data?.message ||
        error.response?.data?.errors?.quantity?.[0] ||
        'Cập nhật kho thất bại!'
      await swal.error(errorMessage)
    }
  }

  async function deleteProduct(id: number) {
    console.log('deleteProduct called with id:', id)
    const confirmed = await swal.confirmDelete('Bạn có chắc chắn muốn xóa sản phẩm này?')
    if (!confirmed) {
      console.log('Delete cancelled by user')
      return
    }

    try {
      await store.deleteProduct(id)
      selectedProducts.value.delete(id)
      await swal.success('Đã xóa sản phẩm thành công!')
    } catch (error: any) {
      console.error('Delete error:', error)
      await swal.error(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    }
  }

  async function deleteSelectedProducts() {
    if (selectedProducts.value.size === 0) return

    const count = selectedProducts.value.size
    const confirmed = await swal.confirmDelete(
      `Bạn có chắc chắn muốn xóa ${count} sản phẩm đã chọn?`
    )
    if (!confirmed) return

    isDeleting.value = true
    try {
      const deletePromises = Array.from(selectedProducts.value).map(id =>
        store.deleteProduct(id).catch(err => {
          console.error(`Failed to delete product ${id}:`, err)
          return null
        })
      )

      await Promise.all(deletePromises)

      // Remove deleted products from list
      store.products = store.products.filter(p => !selectedProducts.value.has(p.id))

      // Clear selection
      selectedProducts.value.clear()

      await swal.success(`Đã xóa ${count} sản phẩm thành công!`)
    } catch (error: any) {
      console.error('Delete selected error:', error)
      await swal.error(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    } finally {
      isDeleting.value = false
    }
  }

  return {
    // State
    searchQuery,
    statusFilter,
    currentPage,
    perPage,
    selectedProducts,
    isDeleting,
    showStockModal,
    // Computed
    paginatedProducts,
    totalPages,
    isAllSelected,
    isSomeSelected,
    selectedCount,
    // Methods
    changePage,
    setSearchQuery,
    setStatusFilter,
    toggleSelectAll,
    toggleSelectProduct,
    getStockClass,
    openStockModal,
    handleStockUpdate,
    deleteProduct,
    deleteSelectedProducts
  }
}

