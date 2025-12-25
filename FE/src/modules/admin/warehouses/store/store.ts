/**
 * Warehouse Products Store
 * Manages state for warehouse products and stock operations
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import { adminProductService } from '@/plugins/api/services/ProductService'
import httpClient from '@/plugins/api/httpClient'
import type { Stock, UpdateStockRequest } from '@/plugins/api/services/WarehouseService'

export interface WarehouseProduct {
  id: number
  name: string
  sku: string
  category: string
  quantity: number
  available_quantity: number // BR-06.1: Tồn kho có thể xuất
  minStock: number
  status: string
  location: string
  supplier?: string
  supplier_id?: number | null
  category_id?: number | null
  inbound_batch_id?: number | null
  quality_check_id?: number | null
}

export interface ProductForm {
  name: string
  sku: string
  category_id: number | null
  supplier_id: number | null
  stock_qty: number
  min_stock_level: number
  storage_location: string
  warehouse_type: string
}

export interface StockForm {
  type: 'in' | 'adjust' | 'outbound'
  quantity: number
  reason?: string // BR-05.2: Lý do điều chỉnh
  note: string
}

export const useWarehouseStore = defineStore('warehouse-products', () => {
  // State
  const products = ref<WarehouseProduct[]>([])
  const categories = ref<any[]>([])
  const suppliers = ref<any[]>([])
  const isLoading = ref(false)
  const isSubmitting = ref(false)
  const selectedProduct = ref<WarehouseProduct | null>(null)
  const selectedWarehouseId = ref<number>(1)

  // Forms
  const stockForm = ref<StockForm>({
    type: 'in',
    quantity: 1,
    note: ''
  })

  const productForm = ref<ProductForm>({
    name: '',
    sku: '',
    category_id: null,
    supplier_id: null,
    stock_qty: 0,
    min_stock_level: 5,
    storage_location: '',
    warehouse_type: 'stock'
  })

  // Last stock form data for remembering values
  const lastStockFormData = ref<{
    productId: number | null
    type: 'in' | 'adjust' | 'outbound'
    quantity: number
    reason?: string
    note: string
  }>({
    productId: null,
    type: 'in',
    quantity: 1,
    note: ''
  })

  // Getters
  const hasProducts = computed(() => products.value.length > 0)

  // Actions
  async function fetchProducts() {
    isLoading.value = true
    try {
      // Fetch all products
      const productsResponse = await httpClient.get('/admin/products', {
        params: { per_page: 1000 }
      })
      const productsData = (productsResponse.data as any).data
      const allProducts = Array.isArray(productsData?.data)
        ? productsData.data
        : (Array.isArray(productsData) ? productsData : [])

      // Fetch stocks for warehouse
      let stocksData: Stock[] = []
      try {
        // Only fetch if warehouse ID is valid
        if (selectedWarehouseId.value && selectedWarehouseId.value > 0) {
          stocksData = await warehouseService.getStocks(selectedWarehouseId.value)
        } else {
          console.warn('Invalid warehouse ID:', selectedWarehouseId.value, 'skipping stock fetch')
        }
      } catch (error: any) {
        console.warn('Failed to fetch stocks, will show all products with quantity 0:', error)
        // Don't throw error, just log it and continue with empty stocks
      }

      // Create stocks map
      const stocksMap = new Map(
        stocksData.map((stock) => {
          if (stock.quantity !== undefined && stock.quantity !== null) {
            stock.quantity = Number(stock.quantity)
          }
          return [stock.product_id, stock]
        })
      )

      // Merge products with stocks
      const productMap = new Map<number, WarehouseProduct>()

      allProducts.forEach((product: any) => {
        const stock = stocksMap.get(product.id)
        let quantity = 0
        let availableQuantity = 0

        if (stock) {
          quantity = stock.quantity !== undefined && stock.quantity !== null
            ? Number(stock.quantity)
            : 0
          availableQuantity = stock.available_quantity !== undefined && stock.available_quantity !== null
            ? Number(stock.available_quantity)
            : quantity // Fallback to quantity if available_quantity not set
        }

        productMap.set(product.id, {
          id: product.id,
          name: product.name || 'N/A',
          sku: product.sku || `SKU-${product.id}`,
          category: product.category?.name || 'Chưa phân loại',
          category_id: product.category_id,
          quantity: quantity,
          available_quantity: availableQuantity, // BR-06.1: Tồn kho có thể xuất
          minStock: Number(product.min_stock_level) || 5, // Ensure it's a number
          status: product.warehouse_type || 'stock',
          // Use storage_location from product, not warehouse name
          location: product.storage_location || '',
          supplier: product.supplier?.name || product.supplier || '-',
          supplier_id: product.supplier_id,
          inbound_batch_id: stock?.inbound_batch_id || null,
          quality_check_id: stock?.quality_check_id || null
        })
      })

      // Replace entire array to trigger reactivity
      products.value = Array.from(productMap.values()).sort((a, b) => b.id - a.id)
      console.log('Fetched and merged products:', products.value.length, 'items')
    } catch (error) {
      console.error('Failed to fetch products:', error)
      products.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchMetadata() {
    try {
      const [catRes, supRes, warehousesRes] = await Promise.all([
        httpClient.get('/frontend/categories'),
        httpClient.get('/admin/suppliers'),
        httpClient.get('/admin/warehouses').catch(() => null)
      ])

      categories.value = (catRes.data as any).data || []
      suppliers.value = (supRes.data as any).data?.data || (supRes.data as any).data || []

      // Set default warehouse if available
      if (warehousesRes && (warehousesRes.data as any)?.data) {
        const warehouses = Array.isArray((warehousesRes.data as any).data)
          ? (warehousesRes.data as any).data
          : []
        if (warehouses.length > 0 && selectedWarehouseId.value === 1) {
          selectedWarehouseId.value = warehouses[0].id
        }
      }
    } catch (error) {
      console.error('Failed to fetch metadata:', error)
    }
  }

  async function updateStock(data: UpdateStockRequest): Promise<Stock> {
    isSubmitting.value = true
    try {
      const stock = await warehouseService.updateStock(selectedWarehouseId.value, data)
      const newQuantity = Number(stock.quantity) || 0
      console.log('Stock updated, new quantity:', newQuantity, 'for product:', data.product_id)

      // Update product quantity in list immediately - this ensures table updates right away
      const productIndex = products.value.findIndex(p => p.id === data.product_id)
      if (productIndex !== -1) {
        // Directly mutate the quantity to trigger reactivity
        products.value[productIndex].quantity = newQuantity
        products.value[productIndex].available_quantity = stock.available_quantity || newQuantity
        console.log('Updated product in list, index:', productIndex, 'new quantity:', products.value[productIndex].quantity)
      } else {
        console.warn('Product not found in list:', data.product_id)
      }

      // Update selectedProduct quantity if it's the same product
      if (selectedProduct.value && selectedProduct.value.id === data.product_id) {
        selectedProduct.value.quantity = newQuantity
        selectedProduct.value.available_quantity = stock.available_quantity || newQuantity
        console.log('Updated selectedProduct quantity to:', newQuantity)
      }

      // Save last form data
      if (selectedProduct.value && (data.type === 'in' || data.type === 'adjust')) {
        lastStockFormData.value = {
          productId: selectedProduct.value.id,
          type: data.type as 'in' | 'adjust',
          quantity: data.quantity,
          note: data.note || ''
        }
      }

      return stock
    } catch (error) {
      console.error('Error updating stock:', error)
      throw error
    } finally {
      isSubmitting.value = false
    }
  }

  async function saveProduct(): Promise<number> {
    isSubmitting.value = true
    try {
      const payload = {
        name: productForm.value.name,
        sku: productForm.value.sku || `SKU-${Date.now()}`,
        category_id: productForm.value.category_id,
        supplier_id: productForm.value.supplier_id,
        stock_qty: productForm.value.stock_qty,
        min_stock_level: productForm.value.min_stock_level,
        storage_location: productForm.value.storage_location,
        warehouse_type: productForm.value.warehouse_type,
        price: 0,
        slug: productForm.value.name.toLowerCase().replace(/\s+/g, '-') + '-' + Date.now()
      }

      const isEdit = !!selectedProduct.value
      let productId: number

      if (isEdit) {
        await adminProductService.update(selectedProduct.value!.id, payload)
        productId = selectedProduct.value!.id
      } else {
        const product = await adminProductService.create(payload)
        productId = product.id

        // Note: BR-01.1 - Product không chứa thông tin tồn kho
        // Không tạo stock trực tiếp khi tạo product
        // Phải tạo qua Inbound Batch → QC → Inventory
      }

      return productId
    } finally {
      isSubmitting.value = false
    }
  }

  async function deleteProduct(id: number): Promise<void> {
    await adminProductService.delete(id)
    products.value = products.value.filter(p => p.id !== id)
  }

  function setSelectedWarehouse(id: number) {
    selectedWarehouseId.value = id
  }

  function resetStockForm() {
    stockForm.value = {
      type: 'in',
      quantity: 1,
      note: ''
    }
  }

  function resetProductForm() {
    productForm.value = {
      name: '',
      sku: '',
      category_id: null,
      supplier_id: null,
      stock_qty: 0,
      min_stock_level: 5,
      storage_location: '',
      warehouse_type: 'stock'
    }
  }

  return {
    // State
    products,
    categories,
    suppliers,
    isLoading,
    isSubmitting,
    selectedProduct,
    selectedWarehouseId,
    stockForm,
    productForm,
    lastStockFormData,
    // Getters
    hasProducts,
    // Actions
    fetchProducts,
    fetchMetadata,
    updateStock,
    saveProduct,
    deleteProduct,
    setSelectedWarehouse,
    resetStockForm,
    resetProductForm
  }
})

