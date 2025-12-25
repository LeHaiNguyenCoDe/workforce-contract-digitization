<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'

const { t } = useI18n()

interface Category {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
    slug: string
    price: number
    sale_price?: number
    thumbnail?: string
    category?: Category
    category_id?: number
    stock_quantity?: number
    is_active?: boolean
    short_description?: string
    description?: string
}

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const isLoading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const search = ref('')

// Modal state
const showModal = ref(false)
const editingProduct = ref<Product | null>(null)
const isSaving = ref(false)
const formData = ref({
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

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

// Auto-generate slug from name
const generateSlug = (text: string): string => {
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

watch(() => formData.value.name, (newName) => {
    if (!editingProduct.value) {
        formData.value.slug = generateSlug(newName)
    }
})

const fetchCategories = async () => {
    try {
        const response = await httpClient.get('/frontend/categories')
        const data = response.data
        if (Array.isArray(data?.data)) {
            categories.value = data.data
        }
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
}

const fetchProducts = async () => {
    isLoading.value = true
    try {
        const params: any = { page: currentPage.value, per_page: 10 }
        if (search.value) params.search = search.value

        const response = await httpClient.get('/admin/products', { params })
        const data = response.data

        if (data?.data?.data && Array.isArray(data.data.data)) {
            products.value = data.data.data
            totalPages.value = data.data.last_page || 1
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

const openCreateModal = () => {
    editingProduct.value = null
    formData.value = { name: '', slug: '', category_id: '', price: 0, sale_price: '', short_description: '', description: '', thumbnail: '', is_active: true }
    showModal.value = true
}

const openEditModal = (product: Product) => {
    editingProduct.value = product
    formData.value = {
        name: product.name,
        slug: product.slug,
        category_id: product.category_id?.toString() || product.category?.id?.toString() || '',
        price: product.price,
        sale_price: product.sale_price?.toString() || '',
        short_description: product.short_description || '',
        description: product.description || '',
        thumbnail: product.thumbnail || '',
        is_active: product.is_active ?? true
    }
    showModal.value = true
}

const saveProduct = async () => {
    if (isSaving.value) return
    if (!formData.value.name || !formData.value.category_id || !formData.value.price) {
        alert('Vui lòng điền đầy đủ thông tin bắt buộc!')
        return
    }

    isSaving.value = true
    try {
        const payload: any = {
            name: formData.value.name,
            slug: formData.value.slug || generateSlug(formData.value.name),
            category_id: parseInt(formData.value.category_id),
            price: formData.value.price,
            is_active: formData.value.is_active
        }

        if (formData.value.sale_price) payload.sale_price = parseInt(formData.value.sale_price)
        if (formData.value.short_description) payload.short_description = formData.value.short_description
        if (formData.value.description) payload.description = formData.value.description
        if (formData.value.thumbnail) payload.thumbnail = formData.value.thumbnail

        if (editingProduct.value) {
            await httpClient.put(`/admin/products/${editingProduct.value.id}`, payload)
        } else {
            await httpClient.post('/admin/products', payload)
        }
        showModal.value = false
        fetchProducts()
    } catch (error: any) {
        console.error('Failed to save product:', error)
        const errorData = error.response?.data
        const message = errorData?.errors?.slug?.[0] || errorData?.message || 'Lưu thất bại!'
        alert(message)
    } finally {
        isSaving.value = false
    }
}

const deleteProduct = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return

    try {
        await httpClient.delete(`/admin/products/${id}`)
        products.value = products.value.filter(p => p.id !== id)
    } catch (error) {
        console.error('Failed to delete product:', error)
        alert('Xóa sản phẩm thất bại!')
    }
}

onMounted(() => {
    fetchCategories()
    fetchProducts()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.products') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý danh sách sản phẩm</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                {{ t('common.create') }}
            </button>
        </div>

        <!-- Search -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="search" @keyup.enter="fetchProducts" type="text" class="form-input pl-10"
                        placeholder="Tìm kiếm sản phẩm..." />
                </div>
                <button @click="fetchProducts" class="btn btn-secondary">Tìm kiếm</button>
            </div>
        </div>

        <!-- Table Container - fills remaining height -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Danh mục</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Giá</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tồn kho</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions')
                            }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="product in products" :key="product.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-dark-700 rounded-lg overflow-hidden flex-shrink-0">
                                        <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.name"
                                            class="w-full h-full object-cover" />
                                        <div v-else
                                            class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                                <circle cx="9" cy="9" r="2" />
                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white truncate max-w-[200px]">{{ product.name }}</p>
                                        <p class="text-xs text-slate-500">{{ product.slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="product.category"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                    {{ product.category.name }}
                                </span>
                                <span v-else class="text-slate-500">-</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-white">{{ formatPrice(product.price) }}</p>
                                    <p v-if="product.sale_price" class="text-xs text-success">{{
                                        formatPrice(product.sale_price) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                    (product.stock_quantity ?? 0) > 10 ? 'bg-success/10 text-success' :
                                        (product.stock_quantity ?? 0) > 0 ? 'bg-warning/10 text-warning' : 'bg-error/10 text-error'
                                ]">
                                    {{ product.stock_quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(product)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteProduct(product.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
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

                <div v-if="!products.length" class="py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="m7.5 4.27 9 5.15" />
                        <path
                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                    </svg>
                    <p class="text-slate-400">Chưa có sản phẩm nào</p>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 p-4 border-t border-white/10">
                    <button @click="currentPage--; fetchProducts()" :disabled="currentPage <= 1"
                        class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
                        {{ t('common.previous') }}
                    </button>
                    <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
                    <button @click="currentPage++; fetchProducts()" :disabled="currentPage >= totalPages"
                        class="btn btn-secondary btn-sm"
                        :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
                        {{ t('common.next') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal using BaseModal -->
        <BaseModal :show="showModal" :title="editingProduct ? 'Chỉnh sửa sản phẩm' : 'Tạo sản phẩm mới'" size="lg"
            @close="showModal = false">
            <div class="space-y-5">
                <!-- Basic Info -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tên sản phẩm *</label>
                        <input v-model="formData.name" type="text" class="form-input"
                            placeholder="VD: iPhone 15 Pro Max" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
                        <input v-model="formData.slug" type="text" class="form-input" placeholder="tu-dong-tao" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                    <select v-model="formData.category_id" class="form-input">
                        <option value="">-- Chọn danh mục --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <!-- Price -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Giá gốc (VNĐ) *</label>
                        <input v-model.number="formData.price" type="number" class="form-input" min="0" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Giá khuyến mãi</label>
                        <input v-model="formData.sale_price" type="number" class="form-input" min="0" />
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả ngắn</label>
                    <textarea v-model="formData.short_description" class="form-input" rows="2"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả chi tiết</label>
                    <textarea v-model="formData.description" class="form-input" rows="4"></textarea>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">URL Thumbnail</label>
                    <input v-model="formData.thumbnail" type="url" class="form-input" placeholder="https://..." />
                    <div v-if="formData.thumbnail" class="mt-2">
                        <img :src="formData.thumbnail" alt="Preview"
                            class="w-20 h-20 rounded-lg object-cover bg-dark-700" />
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-2">
                    <input v-model="formData.is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded" />
                    <label for="is_active" class="text-sm text-slate-300">Kích hoạt</label>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">{{
                        t('common.cancel') }}</button>
                    <button @click="saveProduct" class="btn btn-primary"
                        :disabled="isSaving || !formData.name || !formData.category_id || !formData.price">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : t('common.save') }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
