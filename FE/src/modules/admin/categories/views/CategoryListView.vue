<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'

const { t } = useI18n()

interface Category {
    id: number
    name: string
    slug: string
    description?: string
    parent_id?: number
    parent?: Category
    children?: Category[]
    products_count?: number
    is_active?: boolean
}

const categories = ref<Category[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingCategory = ref<Category | null>(null)
const formData = ref({ name: '', slug: '', description: '', parent_id: '' })
const isSaving = ref(false)

const generateSlug = (text: string): string => {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-')
}

watch(() => formData.value.name, (newName) => {
    if (!editingCategory.value) formData.value.slug = generateSlug(newName)
})

const fetchCategories = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/categories')
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) categories.value = data.data.data
        else if (Array.isArray(data?.data)) categories.value = data.data
        else categories.value = []
    } catch (error) {
        console.error('Failed to fetch categories:', error)
        categories.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingCategory.value = null
    formData.value = { name: '', slug: '', description: '', parent_id: '' }
    showModal.value = true
}

const openEditModal = (cat: Category) => {
    editingCategory.value = cat
    formData.value = { name: cat.name, slug: cat.slug, description: cat.description || '', parent_id: cat.parent_id?.toString() || '' }
    showModal.value = true
}

const saveCategory = async () => {
    if (isSaving.value) return
    isSaving.value = true
    try {
        const payload: any = { name: formData.value.name, slug: formData.value.slug || generateSlug(formData.value.name), description: formData.value.description }
        if (formData.value.parent_id) payload.parent_id = parseInt(formData.value.parent_id)
        if (editingCategory.value) await httpClient.put(`/admin/categories/${editingCategory.value.id}`, payload)
        else await httpClient.post('/admin/categories', payload)
        showModal.value = false
        fetchCategories()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Lưu thất bại!')
    } finally {
        isSaving.value = false
    }
}

const deleteCategory = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return
    try {
        await httpClient.delete(`/admin/categories/${id}`)
        categories.value = categories.value.filter(c => c.id !== id)
    } catch (error: any) {
        alert(error.response?.data?.message || 'Xóa thất bại!')
    }
}

onMounted(fetchCategories)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.categories') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý danh mục sản phẩm</p>
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

        <!-- Table Container -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tên danh mục</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Danh mục cha</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions')
                                }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="category in categories" :key="category.id"
                            class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-400">#{{ category.id }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-white">{{ category.name }}</p>
                                <p class="text-xs text-slate-500">{{ category.slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="category.parent"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">{{
                                    category.parent.name }}</span>
                                <span v-else class="text-slate-500">-</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400">{{
                                    category.products_count ?? 0 }} sp</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(category)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteCategory(category.id)"
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
                <div v-if="!categories.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có danh mục nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal :show="showModal" :title="editingCategory ? 'Chỉnh sửa danh mục' : 'Tạo danh mục mới'" size="md"
            @close="showModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tên danh mục *</label>
                    <input v-model="formData.name" type="text" class="form-input" placeholder="Nhập tên danh mục" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
                    <input v-model="formData.slug" type="text" class="form-input" placeholder="tu-dong-tao-tu-ten" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
                    <textarea v-model="formData.description" class="form-input" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục cha</label>
                    <select v-model="formData.parent_id" class="form-input">
                        <option value="">Không có</option>
                        <option v-for="cat in categories.filter(c => c.id !== editingCategory?.id)" :key="cat.id"
                            :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">{{
                        t('common.cancel') }}</button>
                    <button @click="saveCategory" class="btn btn-primary" :disabled="isSaving || !formData.name">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : t('common.save') }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
