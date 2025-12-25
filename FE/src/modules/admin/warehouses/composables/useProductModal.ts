/**
 * Composable for Product Modal
 * Provides reusable logic for product create/edit modal
 */

import { ref } from 'vue'
import { useWarehouseStore } from '../store/store'
import { useSwal } from '@/shared/utils'
import { adminProductService } from '@/plugins/api/services/ProductService'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import type { WarehouseProduct } from '../store/store'

export function useProductModal() {
    const store = useWarehouseStore()
    const swal = useSwal()

    const showProductModal = ref(false)

    async function openProductModal(product?: WarehouseProduct) {
        console.log('openProductModal called with:', product)
        if (product) {
            try {
                // Fetch full product data by ID to ensure we have all fields
                const fullProduct: any = await adminProductService.getById(product.id)
                console.log('Full product data from API:', fullProduct)

                // Fetch latest stock quantity from warehouse to ensure we have the most up-to-date quantity
                let latestQuantity = 0
                try {
                    // Only fetch if warehouse ID is valid
                    if (store.selectedWarehouseId && store.selectedWarehouseId > 0) {
                        console.log('Fetching stocks for warehouse:', store.selectedWarehouseId, 'product:', product.id)
                        const stocks = await warehouseService.getStocks(store.selectedWarehouseId)
                        console.log('Stocks fetched:', stocks.length, 'stocks')
                        const stock = stocks.find(s => s.product_id === product.id)
                        if (stock) {
                            latestQuantity = Number(stock.quantity) || 0
                            console.log('Found stock for product:', product.id, 'quantity:', latestQuantity)
                        } else {
                            console.warn('Stock not found for product:', product.id, 'using 0')
                            latestQuantity = 0
                        }
                    } else {
                        console.warn('Invalid warehouse ID:', store.selectedWarehouseId, 'using quantity from list as fallback')
                        latestQuantity = product.quantity || 0
                    }
                } catch (stockError: any) {
                    console.error('Failed to fetch stock data:', stockError)
                    console.warn('Using quantity from product list as fallback:', product.quantity)
                    // Use quantity from product list as fallback only if API fails
                    latestQuantity = product.quantity || 0
                }

                console.log('Final latestQuantity for edit modal:', latestQuantity)

                store.selectedProduct = product

                // Map all fields from fullProduct, with fallbacks
                // Use nullish coalescing (??) to handle null/undefined, but use || for empty strings
                const formData = {
                    name: fullProduct.name ?? product.name ?? '',
                    sku: fullProduct.sku ?? product.sku ?? '',
                    category_id: fullProduct.category_id ?? product.category_id ?? null,
                    supplier_id: fullProduct.supplier_id ?? product.supplier_id ?? null,
                    stock_qty: latestQuantity,
                    min_stock_level: fullProduct.min_stock_level ?? product.minStock ?? 5,
                    // For storage_location and warehouse_type, use fullProduct first, then product, then defaults
                    storage_location: fullProduct.storage_location ?? product.location ?? '',
                    warehouse_type: fullProduct.warehouse_type ?? product.status ?? 'stock'
                }

                console.log('Form data being set:', formData)
                console.log('Full product keys:', Object.keys(fullProduct))
                console.log('Full product storage_location:', fullProduct.storage_location)
                console.log('Full product warehouse_type:', fullProduct.warehouse_type)

                // Set form data
                Object.assign(store.productForm, formData)
            } catch (error) {
                console.error('Failed to fetch product details:', error)
                // Fallback to using product data from list
                store.selectedProduct = product
                store.productForm = {
                    name: product.name || '',
                    sku: product.sku || '',
                    category_id: product.category_id ?? null,
                    supplier_id: product.supplier_id ?? null,
                    stock_qty: product.quantity || 0,
                    min_stock_level: product.minStock || 5,
                    storage_location: product.location || '',
                    warehouse_type: product.status || 'stock'
                }
            }
        } else {
            store.selectedProduct = null
            store.resetProductForm()
        }
        showProductModal.value = true
        console.log('showProductModal set to:', showProductModal.value)
    }

    async function handleProductSave() {
        if (store.isSubmitting) return
        if (!store.productForm.name || !store.productForm.category_id) {
            await swal.warning('Vui lòng nhập tên và chọn danh mục!')
            return
        }

        try {
            const isEdit = !!store.selectedProduct
            const productId = await store.saveProduct()
            console.log('Product saved, ID:', productId)

            // Close modal first
            showProductModal.value = false
            store.selectedProduct = null
            store.resetProductForm()

            // Refresh products list to show updated data
            console.log('Refreshing products list...')
            // Force refresh with cache busting
            await new Promise(resolve => setTimeout(resolve, 200)) // Small delay to ensure server is ready
            await store.fetchProducts()
            console.log('Products list refreshed, products count:', store.products.length)

            await swal.success(
                isEdit ? 'Cập nhật sản phẩm thành công!' : 'Tạo sản phẩm thành công!'
            )
        } catch (error: any) {
            console.error('Product save error:', error)
            const errorMessage = error.response?.data?.message
                || error.response?.data?.errors
                || error.message
                || 'Lưu sản phẩm thất bại!'
            await swal.error(typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage))
        }
    }

    return {
        showProductModal,
        openProductModal,
        handleProductSave
    }
}

