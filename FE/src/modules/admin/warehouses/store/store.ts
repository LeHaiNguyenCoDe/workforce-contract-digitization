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
  available_quantity: number
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
  reason?: string
  note: string
}

export const useWarehouseStore = defineStore('warehouse-products', () => {
  // State
  const products = ref<WarehouseProduct[]>([])
  const categories = ref<any[]>([])
  const suppliers = ref<any[]>([])
  const warehouses = ref<any[]>([])
  const stocks = ref<any[]>([])
  const availableStocks = ref<any[]>([])
  const inboundReceipts = ref<any[]>([])
  const outboundReceipts = ref<any[]>([])
  const stockAdjustments = ref<any[]>([])
  const selectedReceipt = ref<any>(null)
  
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

  // Actions - Fetch
  async function fetchProducts() {
    isLoading.value = true
    try {
      const productsResponse = await httpClient.get('/admin/products', {
        params: { per_page: 1000 }
      })
      const productsData = (productsResponse.data as any).data
      const allProducts = Array.isArray(productsData?.data)
        ? productsData.data
        : (Array.isArray(productsData) ? productsData : [])

      let stocksData: Stock[] = []
      try {
        if (selectedWarehouseId.value && selectedWarehouseId.value > 0) {
          stocksData = await warehouseService.getStocks(selectedWarehouseId.value)
        }
      } catch (error: any) {
        console.warn('Failed to fetch stocks:', error)
      }

      const stocksMap = new Map(
        stocksData.map((stock) => {
          if (stock.quantity !== undefined && stock.quantity !== null) {
            stock.quantity = Number(stock.quantity)
          }
          return [stock.product_id, stock]
        })
      )

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
            : quantity
        }

        productMap.set(product.id, {
          id: product.id,
          name: product.name || 'N/A',
          sku: product.sku || `SKU-${product.id}`,
          category: product.category?.name || 'Chưa phân loại',
          category_id: product.category_id,
          quantity: quantity,
          available_quantity: availableQuantity,
          minStock: Number(product.min_stock_level) || 5,
          status: product.warehouse_type || 'stock',
          location: product.storage_location || '',
          supplier: product.supplier?.name || product.supplier || '-',
          supplier_id: product.supplier_id,
          inbound_batch_id: stock?.inbound_batch_id || null,
          quality_check_id: stock?.quality_check_id || null
        })
      })

      products.value = Array.from(productMap.values()).sort((a, b) => b.id - a.id)
    } catch (error) {
      console.error('Failed to fetch products:', error)
      products.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchWarehouses() {
    try {
      const res = await httpClient.get('/admin/warehouses')
      warehouses.value = (res.data as any).data || []
    } catch (error) {
      console.error('Failed to fetch warehouses:', error)
      warehouses.value = []
    }
  }

  async function fetchSuppliers() {
    try {
      const res = await httpClient.get('/admin/suppliers')
      suppliers.value = (res.data as any).data?.data || (res.data as any).data || []
    } catch (error) {
      console.error('Failed to fetch suppliers:', error)
      suppliers.value = []
    }
  }

  async function fetchAllStocks() {
    isLoading.value = true
    try {
      // Fetch stocks from all warehouses
      const allStocks: any[] = []
      for (const wh of warehouses.value) {
        try {
          const whStocks = await warehouseService.getStocks(wh.id)
          allStocks.push(...whStocks.map((s: any) => ({ 
            ...s, 
            warehouse: wh,
            warehouse_id: wh.id // Ensure warehouse_id is set for filtering
          })))
        } catch (e) {
          console.warn(`Failed to fetch stocks for warehouse ${wh.id}`)
        }
      }
      stocks.value = allStocks
      availableStocks.value = allStocks.filter((s: any) => (s.available_quantity || 0) > 0)
    } catch (error) {
      console.error('Failed to fetch all stocks:', error)
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

      if (warehousesRes && (warehousesRes.data as any)?.data) {
        warehouses.value = Array.isArray((warehousesRes.data as any).data)
          ? (warehousesRes.data as any).data
          : []
        if (warehouses.value.length > 0 && selectedWarehouseId.value === 1) {
          selectedWarehouseId.value = warehouses.value[0].id
        }
      }
    } catch (error) {
      console.error('Failed to fetch metadata:', error)
    }
  }

  // Inbound Receipts
  async function fetchInboundReceipts() {
    isLoading.value = true
    try {
      const res = await httpClient.get('/admin/warehouses/inbound-receipts')
      inboundReceipts.value = (res.data as any).data || []
    } catch (error) {
      console.error('Failed to fetch inbound receipts:', error)
      inboundReceipts.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function createInboundReceipt(data: any) {
    isSubmitting.value = true
    try {
      await httpClient.post('/admin/warehouses/inbound-receipts', data)
    } finally {
      isSubmitting.value = false
    }
  }

  async function updateInboundReceipt(id: number, data: any) {
    isSubmitting.value = true
    try {
      await httpClient.put(`/admin/warehouses/inbound-receipts/${id}`, data)
    } finally {
      isSubmitting.value = false
    }
  }

  async function approveInboundReceipt(id: number) {
    await httpClient.post(`/admin/warehouses/inbound-receipts/${id}/approve`)
  }

  async function cancelInboundReceipt(id: number) {
    await httpClient.post(`/admin/warehouses/inbound-receipts/${id}/cancel`)
  }

  async function deleteInboundReceipt(id: number) {
    await httpClient.delete(`/admin/warehouses/inbound-receipts/${id}`)
  }

  // Outbound Receipts
  async function fetchOutboundReceipts() {
    isLoading.value = true
    try {
      const res = await httpClient.get('/admin/warehouses/outbound-receipts')
      outboundReceipts.value = (res.data as any).data || []
    } catch (error) {
      console.error('Failed to fetch outbound receipts:', error)
      outboundReceipts.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function createOutboundReceipt(data: any) {
    isSubmitting.value = true
    try {
      await httpClient.post('/admin/warehouses/outbound-receipts', data)
    } finally {
      isSubmitting.value = false
    }
  }

  async function updateOutboundReceipt(id: number, data: any) {
    isSubmitting.value = true
    try {
      await httpClient.put(`/admin/warehouses/outbound-receipts/${id}`, data)
    } finally {
      isSubmitting.value = false
    }
  }

  async function approveOutboundReceipt(id: number) {
    await httpClient.post(`/admin/warehouses/outbound-receipts/${id}/approve`)
  }

  async function completeOutboundReceipt(id: number) {
    await httpClient.post(`/admin/warehouses/outbound-receipts/${id}/complete`)
  }

  async function cancelOutboundReceipt(id: number) {
    await httpClient.post(`/admin/warehouses/outbound-receipts/${id}/cancel`)
  }

  // Stock Adjustments
  async function fetchStockAdjustments() {
    isLoading.value = true
    try {
      const res = await httpClient.get('/admin/warehouses/stock-adjustments')
      stockAdjustments.value = (res.data as any).data || []
    } catch (error) {
      console.error('Failed to fetch stock adjustments:', error)
      stockAdjustments.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function createStockAdjustment(data: any) {
    isSubmitting.value = true
    try {
      await httpClient.post('/admin/warehouses/stock-adjustments', data)
    } finally {
      isSubmitting.value = false
    }
  }

  async function updateStock(data: UpdateStockRequest): Promise<Stock> {
    isSubmitting.value = true
    try {
      const stock = await warehouseService.updateStock(selectedWarehouseId.value, data)
      const newQuantity = Number(stock.quantity) || 0

      const productIndex = products.value.findIndex(p => p.id === data.product_id)
      if (productIndex !== -1) {
        products.value[productIndex].quantity = newQuantity
        products.value[productIndex].available_quantity = stock.available_quantity || newQuantity
      }

      if (selectedProduct.value && selectedProduct.value.id === data.product_id) {
        selectedProduct.value.quantity = newQuantity
        selectedProduct.value.available_quantity = stock.available_quantity || newQuantity
      }

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

  function setSelectedReceipt(receipt: any) {
    selectedReceipt.value = receipt
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
    warehouses,
    stocks,
    availableStocks,
    inboundReceipts,
    outboundReceipts,
    stockAdjustments,
    selectedReceipt,
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
    fetchWarehouses,
    fetchSuppliers,
    fetchAllStocks,
    fetchMetadata,
    fetchInboundReceipts,
    createInboundReceipt,
    updateInboundReceipt,
    approveInboundReceipt,
    cancelInboundReceipt,
    deleteInboundReceipt,
    fetchOutboundReceipts,
    createOutboundReceipt,
    updateOutboundReceipt,
    approveOutboundReceipt,
    completeOutboundReceipt,
    cancelOutboundReceipt,
    fetchStockAdjustments,
    createStockAdjustment,
    updateStock,
    saveProduct,
    deleteProduct,
    setSelectedWarehouse,
    setSelectedReceipt,
    resetStockForm,
    resetProductForm
  }
})

