<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const productId = computed(() => route.params.id as string | undefined)
const isEditMode = computed(() => !!productId.value)

interface Category {
    id: number
    name: string
}

const categories = ref<Category[]>([])
const isLoading = ref(false)
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
    if (!isEditMode.value) {
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

const fetchProduct = async () => {
    if (!productId.value) return

    isLoading.value = true
    try {
        const response = await httpClient.get(`/admin/products/${productId.value}`)
        const product = response.data?.data

        if (product) {
            formData.value = {
                name: product.name || '',
                slug: product.slug || '',
                category_id: product.category_id?.toString() || product.category?.id?.toString() || '',
                price: product.price || 0,
                sale_price: product.sale_price?.toString() || '',
                short_description: product.short_description || '',
                description: product.description || '',
                thumbnail: product.thumbnail || '',
                is_active: product.is_active ?? true
            }
        }
    } catch (error) {
        console.error('Failed to fetch product:', error)
        alert('Không tìm thấy sản phẩm!')
        router.push('/admin/products')
    } finally {
        isLoading.value = false
    }
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
            price: formData.value.price
        }

        if (formData.value.sale_price) payload.sale_price = parseInt(formData.value.sale_price)
        if (formData.value.short_description) payload.short_description = formData.value.short_description
        if (formData.value.description) payload.description = formData.value.description
        if (formData.value.thumbnail) payload.thumbnail = formData.value.thumbnail
        payload.is_active = formData.value.is_active

        if (isEditMode.value) {
            await httpClient.put(`/admin/products/${productId.value}`, payload)
        } else {
            await httpClient.post('/admin/products', payload)
        }

        router.push('/admin/products')
    } catch (error: any) {
        console.error('Failed to save product:', error)
        const errorData = error.response?.data
        const message = errorData?.errors?.slug?.[0] || errorData?.message || 'Lưu thất bại!'
        alert(message)
    } finally {
        isSaving.value = false
    }
}

const goBack = () => {
    router.push('/admin/products')
}

onMounted(() => {
    fetchCategories()
    if (isEditMode.value) {
        fetchProduct()
    }
})
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <button @click="goBack"
                class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ isEditMode ? 'Chỉnh sửa sản phẩm' : 'Tạo sản phẩm mới' }}
                </h1>
                <p class="text-slate-400 mt-1">{{ isEditMode ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới' }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex items-center justify-center py-20">
            <div class="inline-block w-10 h-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
            </div>
        </div>

        <!-- Form -->
        <div v-else class="max-w-3xl">
            <div class="bg-dark-800 rounded-2xl border border-white/10 p-6 space-y-6">

                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">Thông tin cơ bản</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên sản phẩm *</label>
                            <input v-model="formData.name" type="text" class="form-input"
                                placeholder="VD: iPhone 15 Pro Max" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
                            <input v-model="formData.slug" type="text" class="form-input"
                                placeholder="tu-dong-tao-tu-ten" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                        <select v-model="formData.category_id" class="form-input">
                            <option value="">-- Chọn danh mục --</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Price -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">Giá bán</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá gốc (VNĐ) *</label>
                            <input v-model.number="formData.price" type="number" class="form-input" min="0"
                                placeholder="1000000" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá khuyến mãi (VNĐ)</label>
                            <input v-model="formData.sale_price" type="number" class="form-input" min="0"
                                placeholder="900000" />
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">Mô tả</h3>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả ngắn</label>
                        <textarea v-model="formData.short_description" class="form-input" rows="2"
                            placeholder="Mô tả ngắn gọn về sản phẩm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả chi tiết</label>
                        <textarea v-model="formData.description" class="form-input" rows="5"
                            placeholder="Mô tả chi tiết về sản phẩm, tính năng, thông số..."></textarea>
                    </div>
                </div>

                <!-- Image -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">Hình ảnh</h3>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">URL Thumbnail</label>
                        <input v-model="formData.thumbnail" type="url" class="form-input"
                            placeholder="https://example.com/image.jpg" />
                    </div>

                    <div v-if="formData.thumbnail" class="flex items-center gap-4">
                        <img :src="formData.thumbnail" alt="Preview"
                            class="w-24 h-24 rounded-lg object-cover bg-dark-700" />
                        <span class="text-sm text-slate-500">Xem trước</span>
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                    <input v-model="formData.is_active" type="checkbox" id="is_active" class="w-5 h-5 rounded" />
                    <label for="is_active" class="text-slate-300">Kích hoạt sản phẩm (hiển thị trên trang chủ)</label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-white/10">
                    <button @click="goBack" class="btn btn-secondary" :disabled="isSaving">
                        {{ t('common.cancel') }}
                    </button>
                    <button @click="saveProduct" class="btn btn-primary"
                        :disabled="isSaving || !formData.name || !formData.category_id || !formData.price">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : (isEditMode ? 'Cập nhật' : 'Tạo mới') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
