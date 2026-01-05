<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import { useAutoTranslate } from '@/shared/composables/useAutoTranslate'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { autoTranslateAfterSave, isTranslating } = useAutoTranslate()

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

// Auto-select category and generate description
const autoSelectCategory = (productName: string) => {
    if (!productName || formData.value.category_id || categories.value.length === 0) return // Skip if already selected or no categories

    const nameLower = productName.toLowerCase().trim()

    // Find category whose name is contained in product name (best match first)
    let matchedCategory = null
    let bestMatchLength = 0

    for (const cat of categories.value) {
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
        formData.value.category_id = matchedCategory.id.toString()
    }
}

// Auto-generate short description and full description
const autoGenerateShortDescription = (productName: string) => {
    if (!productName) return

    // Use selected category if available, otherwise find matched category
    let categoryName = 'sản phẩm'
    if (formData.value.category_id) {
        const selectedCategory = categories.value.find(cat => cat.id.toString() === formData.value.category_id)
        if (selectedCategory) {
            categoryName = selectedCategory.name
        }
    } else {
        // Find matched category for context
        const matchedCategory = categories.value.find(cat => {
            const nameLower = productName.toLowerCase()
            const catNameLower = cat.name.toLowerCase()
            return nameLower.includes(catNameLower) || catNameLower.includes(nameLower)
        })
        if (matchedCategory) {
            categoryName = matchedCategory.name
        }
    }

    // Generate short description (only if empty)
    if (!formData.value.short_description) {
        const shortDesc = `${productName} là ${categoryName} chất lượng cao, được thiết kế và sản xuất với tiêu chuẩn nghiêm ngặt. Sản phẩm phù hợp cho mọi nhu cầu sử dụng, mang lại trải nghiệm tuyệt vời cho người dùng.`
        formData.value.short_description = shortDesc
    }

    // Generate full description (only if empty)
    if (!formData.value.description) {
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
        formData.value.description = fullDesc
    }
}

watch(() => formData.value.name, (newName) => {
    if (!isEditMode.value && newName) {
        const productName = newName.trim()

        // Auto-generate slug
        formData.value.slug = generateSlug(productName)

        // Auto-select category
        autoSelectCategory(productName)

        // Auto-generate short description and full description
        // Use nextTick to ensure category is selected first
        setTimeout(() => {
            autoGenerateShortDescription(productName)
        }, 100)
    }
})

// Watch category changes to update descriptions
watch(() => formData.value.category_id, (newCategoryId, oldCategoryId) => {
    if (!isEditMode.value && formData.value.name && newCategoryId && newCategoryId !== oldCategoryId) {
        // Update descriptions when category changes
        autoGenerateShortDescription(formData.value.name.trim())
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

        let savedProductId: number
        if (isEditMode.value) {
            await httpClient.put(`/admin/products/${productId.value}`, payload)
            savedProductId = parseInt(productId.value!)
        } else {
            const response = await httpClient.post('/admin/products', payload)
            savedProductId = response.data?.data?.id
        }

        // Auto-translate to all languages after save
        if (savedProductId) {
            await autoTranslateAfterSave(
                'App\\Models\\Product',
                savedProductId,
                {
                    name: formData.value.name,
                    short_description: formData.value.short_description,
                    description: formData.value.description
                }
            )
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
                    {{ isEditMode ? t('admin.editProduct') : t('admin.createProduct') }}
                </h1>
                <p class="text-slate-400 mt-1">{{ isEditMode ? t('admin.updateProduct') : t('admin.addProduct') }}</p>
                <div class="mt-2 bg-info/10 border border-info/20 rounded-lg p-2 text-xs text-info max-w-2xl">
                    <strong>{{ t('common.notice') || 'Note' }}:</strong> {{ t('admin.productNotice') }}
                    <router-link :to="{ name: 'admin-warehouse-inbound-batches' }"
                        class="underline font-semibold hover:text-info-light">
                        {{ t('admin.warehouseInbound') }}
                    </router-link>
                </div>
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

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">{{ t('admin.basicInfo')
                        }}</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.productName') }}
                                *</label>
                            <input v-model="formData.name" type="text" class="form-input"
                                placeholder="VD: iPhone 15 Pro Max" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.slug') }} *</label>
                            <input v-model="formData.slug" type="text" class="form-input"
                                placeholder="tu-dong-tao-tu-ten" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.categories') }}
                            *</label>
                        <select v-model="formData.category_id" class="form-input">
                            <option value="">-- {{ t('common.selectCategory') }} --</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Price -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">{{ t('admin.pricing') }}
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.originalPrice') }}
                                *</label>
                            <input v-model.number="formData.price" type="number" class="form-input" min="0"
                                placeholder="1000000" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.salePrice')
                                }}</label>
                            <input v-model="formData.sale_price" type="number" class="form-input" min="0"
                                placeholder="900000" />
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">{{ t('admin.description')
                        }}</h3>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.shortDescription')
                            }}</label>
                        <textarea v-model="formData.short_description" class="form-input" rows="2"
                            :placeholder="t('admin.shortDescription')"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.detailedDescription')
                            }}</label>
                        <textarea v-model="formData.description" class="form-input" rows="5"
                            :placeholder="t('admin.detailedDescription')"></textarea>
                    </div>
                </div>

                <!-- Image -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white/10 pb-3">{{ t('admin.images') }}
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.thumbnailUrl')
                            }}</label>
                        <input v-model="formData.thumbnail" type="url" class="form-input"
                            placeholder="https://example.com/image.jpg" />
                    </div>

                    <div v-if="formData.thumbnail" class="flex items-center gap-4">
                        <img :src="formData.thumbnail" alt="Preview"
                            class="w-24 h-24 rounded-lg object-cover bg-dark-700" />
                        <span class="text-sm text-slate-500">{{ t('admin.preview') }}</span>
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                    <input v-model="formData.is_active" type="checkbox" id="is_active" class="w-5 h-5 rounded" />
                    <label for="is_active" class="text-slate-300">{{ t('admin.activateProduct') }}</label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-white/10">
                    <button @click="goBack" class="btn btn-secondary" :disabled="isSaving">
                        {{ t('common.cancel') }}
                    </button>
                    <button @click="saveProduct" class="btn btn-primary"
                        :disabled="isSaving || isTranslating || !formData.name || !formData.category_id || !formData.price">
                        <span v-if="isSaving || isTranslating"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? t('common.saving') : isTranslating ? t('common.translating') : (isEditMode ?
                            t('common.update') : t('common.create')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
