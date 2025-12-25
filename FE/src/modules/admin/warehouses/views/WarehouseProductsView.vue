<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'

interface WarehouseProduct {
    id: number
    name: string
    sku: string
    category: string
    quantity: number
    minStock: number
    status: string
    location: string
    supplier?: string
    supplier_id?: number | null
    category_id?: number | null
}

const products = ref<WarehouseProduct[]>([])
const isLoading = ref(true)
const route = useRoute()
const searchQuery = ref('')
const statusFilter = ref(route.query.filter as string || '')

// Modals & Forms
const showStockModal = ref(false)
const showProductModal = ref(false)
const isSubmitting = ref(false)
const selectedProduct = ref<WarehouseProduct | null>(null)

const stockForm = ref({
    type: 'in' as 'in' | 'adjust',
    quantity: 1,
    note: ''
})

const productForm = ref({
    name: '',
    sku: '',
    category_id: null as number | null,
    supplier_id: null as number | null,
    stock_qty: 0,
    min_stock_level: 5,
    storage_location: '',
    warehouse_type: 'stock'
})

const categories = ref<any[]>([])
const suppliers = ref<any[]>([])

const fetchProducts = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/products')
        const data = (response.data as any).data
        const rawProducts = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])

        // Sort by ID descending to show newest first
        rawProducts.sort((a: any, b: any) => b.id - a.id)

        products.value = rawProducts.map((p: any) => ({
            id: p.id,
            name: p.name,
            sku: p.sku || `SKU-${p.id}`,
            category: p.category?.name || 'Chưa phân loại',
            category_id: p.category_id,
            quantity: p.stock_qty || 0,
            minStock: p.min_stock_level || 5,
            status: p.warehouse_type || 'stock',
            location: p.storage_location || 'Kho chính',
            supplier: p.supplier?.name || '-',
            supplier_id: p.supplier_id
        }))
    } catch (error) {
        console.error('Failed to fetch products:', error)
        products.value = []
    } finally {
        isLoading.value = false
    }
}

const fetchMetadata = async () => {
    try {
        const [catRes, supRes] = await Promise.all([
            httpClient.get('/frontend/categories'),
            httpClient.get('/admin/suppliers')
        ])
        categories.value = (catRes.data as any).data || []
        suppliers.value = (supRes.data as any).data?.data || (supRes.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch metadata:', error)
    }
}

const openStockModal = (product: WarehouseProduct, type: 'in' | 'adjust' = 'in') => {
    selectedProduct.value = product
    stockForm.value = { type, quantity: type === 'in' ? 1 : product.quantity, note: '' }
    showStockModal.value = true
}

const openProductModal = (product?: WarehouseProduct) => {
    if (product) {
        selectedProduct.value = product
        productForm.value = {
            name: product.name,
            sku: product.sku,
            category_id: product.category_id || null,
            supplier_id: product.supplier_id || null,
            stock_qty: product.quantity || 0,
            min_stock_level: product.minStock,
            storage_location: product.location,
            warehouse_type: product.status
        }
    } else {
        selectedProduct.value = null
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
    showProductModal.value = true
}

const handleStockUpdate = async () => {
    if (!selectedProduct.value || isSubmitting.value) return
    isSubmitting.value = true
    try {
        await httpClient.post(`/admin/warehouses/1/stocks`, {
            product_id: selectedProduct.value.id,
            quantity: stockForm.value.type === 'in' ? stockForm.value.quantity : (stockForm.value.quantity - selectedProduct.value.quantity),
            type: stockForm.value.type,
            note: stockForm.value.note
        })
        showStockModal.value = false
        await fetchProducts()
    } catch (error: any) {
        console.error('Stock update error:', error)
        alert(error.response?.data?.message || 'Cập nhật kho thất bại!')
    } finally {
        isSubmitting.value = false
    }
}

const handleProductSave = async () => {
    if (isSubmitting.value) return
    if (!productForm.value.name || !productForm.value.category_id) {
        alert('Vui lòng nhập tên và chọn danh mục!')
        return
    }

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

        if (selectedProduct.value) {
            await httpClient.put(`/admin/products/${selectedProduct.value.id}`, payload)
        } else {
            await httpClient.post(`/admin/products`, payload)
        }
        showProductModal.value = false
        await fetchProducts()
    } catch (error: any) {
        console.error('Product save error:', error)
        alert(error.response?.data?.message || 'Lưu sản phẩm thất bại!')
    } finally {
        isSubmitting.value = false
    }
}

const deleteProduct = async (id: number) => {
    console.log('Delete product clicked:', id)
    if (!window.confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) return
    try {
        await httpClient.delete(`/admin/products/${id}`)
        products.value = products.value.filter(p => p.id !== id)
    } catch (error: any) {
        console.error('Delete error:', error)
        alert(error.response?.data?.message || 'Xóa sản phẩm thất bại!')
    }
}

const filteredProducts = computed(() => {
    let result = products.value
    if (statusFilter.value) {
        result = result.filter(p => p.status === statusFilter.value)
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(p => p.name.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query))
    }
    return result
})

const getStockClass = (product: WarehouseProduct) => {
    if (product.quantity === 0) return 'bg-error/10 text-error'
    if (product.quantity <= product.minStock) return 'bg-warning/10 text-warning'
    return 'bg-success/10 text-success'
}

onMounted(() => {
    fetchProducts()
    fetchMetadata()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Sản phẩm trong kho</h1>
                <p class="text-slate-400 mt-1">Quản lý sản phẩm và tồn kho</p>
            </div>
            <button class="btn btn-primary" @click="openProductModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Nhập hàng mới
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="searchQuery" type="text" class="form-input pl-10 w-full"
                        placeholder="Tìm sản phẩm, SKU..." />
                </div>
                <select v-model="statusFilter" class="form-input w-48">
                    <option value="">Tất cả</option>
                    <option value="new">Mới nhập</option>
                    <option value="stock">Tồn kho</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">SKU</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Vị trí</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tồn kho</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Nhà CC</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="product in filteredProducts" :key="product.id"
                            class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-white">{{ product.name }}</p>
                                <p class="text-xs text-slate-500">{{ product.category }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs font-mono text-primary">{{ product.sku }}</code>
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ product.location }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStockClass(product)]">
                                    {{ product.quantity }} / {{ product.minStock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-white">{{ product.supplier }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openProductModal(product)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                                        title="Sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="openStockModal(product, 'in')"
                                        class="w-8 h-8 rounded-lg bg-success/10 text-success hover:bg-success/20 flex items-center justify-center"
                                        title="Nhập kho">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14" />
                                            <path d="m19 12-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <button @click="deleteProduct(product.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                                        title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!filteredProducts.length" class="py-16 text-center">
                    <p class="text-slate-400">Không có sản phẩm nào</p>
                </div>
            </div>
        </div>

        <!-- Stock Modal -->
        <BaseModal :show="showStockModal" :title="stockForm.type === 'in' ? 'Nhập kho' : 'Điều chỉnh'" size="md"
            @close="showStockModal = false">
            <div class="space-y-4">
                <p class="text-slate-400">Sản phẩm: <span class="text-white font-bold">{{ selectedProduct?.name
                        }}</span></p>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng</label>
                    <input v-model.number="stockForm.quantity" type="number" min="0" class="form-input" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
                    <textarea v-model="stockForm.note" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showStockModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="handleStockUpdate" class="btn btn-primary" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Đang lưu...' : 'Xác nhận' }}
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Product Modal -->
        <BaseModal :show="showProductModal" :title="selectedProduct ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới'" size="lg"
            @close="showProductModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tên sản phẩm *</label>
                    <input v-model="productForm.name" type="text" class="form-input" placeholder="VD: iPhone 15 Pro" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">SKU</label>
                        <input v-model="productForm.sku" type="text" class="form-input"
                            placeholder="Tự động tạo nếu để trống" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Vị trí kho</label>
                        <input v-model="productForm.storage_location" type="text" class="form-input"
                            placeholder="VD: A1-01" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng tồn kho</label>
                        <input v-model.number="productForm.stock_qty" type="number" min="0" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng tối thiểu</label>
                        <input v-model.number="productForm.min_stock_level" type="number" min="0" class="form-input" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                        <select v-model="productForm.category_id" class="form-input">
                            <option :value="null">-- Chọn danh mục --</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nhà cung cấp</label>
                        <select v-model="productForm.supplier_id" class="form-input">
                            <option :value="null">-- Chọn nhà cung cấp --</option>
                            <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showProductModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="handleProductSave" class="btn btn-primary"
                        :disabled="isSubmitting || !productForm.name || !productForm.category_id">
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu sản phẩm' }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
