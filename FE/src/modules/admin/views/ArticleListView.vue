<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useSwal } from '@/shared/utils'

const { t } = useI18n()

interface Article {
    id: number
    title: string
    slug: string
    content?: string
    excerpt?: string
    thumbnail?: string
    published_at?: string
    views?: number
    created_at: string
}

const articles = ref<Article[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingArticle = ref<Article | null>(null)
const isSaving = ref(false)
const formData = ref({ title: '', slug: '', excerpt: '', content: '', thumbnail: '' })
const swal = useSwal()

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')
const generateSlug = (text: string): string => text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-')

watch(() => formData.value.title, (newTitle) => { if (!editingArticle.value) formData.value.slug = generateSlug(newTitle) })

const fetchArticles = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/articles')
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) articles.value = data.data.data
        else if (Array.isArray(data?.data)) articles.value = data.data
        else articles.value = []
    } catch (error) {
        articles.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingArticle.value = null
    formData.value = { title: '', slug: '', excerpt: '', content: '', thumbnail: '' }
    showModal.value = true
}

const openEditModal = (article: Article) => {
    editingArticle.value = article
    formData.value = { title: article.title, slug: article.slug, excerpt: article.excerpt || '', content: article.content || '', thumbnail: article.thumbnail || '' }
    showModal.value = true
}

const saveArticle = async () => {
    if (isSaving.value) return
    isSaving.value = true
    try {
        const payload: any = { title: formData.value.title, slug: formData.value.slug || generateSlug(formData.value.title), content: formData.value.content }
        if (formData.value.excerpt) payload.excerpt = formData.value.excerpt
        if (formData.value.thumbnail) payload.thumbnail = formData.value.thumbnail
        
        if (editingArticle.value) {
            await httpClient.put(`/admin/articles/${editingArticle.value.id}`, payload)
            await swal.success('Cập nhật bài viết thành công!')
        } else {
            await httpClient.post('/admin/articles', payload)
            await swal.success('Thêm bài viết thành công!')
        }
        showModal.value = false
        fetchArticles()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lưu thất bại!')
    } finally {
        isSaving.value = false
    }
}

const togglePublish = async (article: Article) => {
    try {
        const endpoint = article.published_at ? `/admin/articles/${article.id}/unpublish` : `/admin/articles/${article.id}/publish`
        await httpClient.post(endpoint)
        article.published_at = article.published_at ? undefined : new Date().toISOString()
    } catch (error) { }
}

const deleteArticle = async (id: number) => {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa bài viết này?')
    if (!confirmed) return
    
    try {
        await httpClient.delete(`/admin/articles/${id}`)
        articles.value = articles.value.filter(a => a.id !== id)
        await swal.success('Xóa bài viết thành công!')
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
}

onMounted(fetchArticles)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.articles') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý bài viết / tin tức</p>
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
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Bài viết</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Lượt xem</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày tạo</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions')
                            }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="article in articles" :key="article.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-400">#{{ article.id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 h-12 bg-dark-700 rounded-lg overflow-hidden flex-shrink-0">
                                        <img v-if="article.thumbnail" :src="article.thumbnail" :alt="article.title"
                                            class="w-full h-full object-cover" />
                                        <div v-else
                                            class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white truncate max-w-[300px]">{{ article.title }}</p>
                                        <p class="text-xs text-slate-500 truncate max-w-[300px]">{{ article.slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="togglePublish(article)"
                                    :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors', article.published_at ? 'bg-success/10 text-success hover:bg-success/20' : 'bg-warning/10 text-warning hover:bg-warning/20']">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="article.published_at ? 'bg-success' : 'bg-warning'"></span>
                                    {{ article.published_at ? 'Đã xuất bản' : 'Nháp' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ article.views || 0 }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(article.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(article)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteArticle(article.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!articles.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có bài viết nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" :title="editingArticle ? 'Chỉnh sửa bài viết' : 'Tạo bài viết mới'" size="lg">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tiêu đề *</label>
                    <input v-model="formData.title" type="text" class="form-input" placeholder="Tiêu đề bài viết" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
                    <input v-model="formData.slug" type="text" class="form-input" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Thumbnail URL</label>
                    <input v-model="formData.thumbnail" type="url" class="form-input" placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tóm tắt</label>
                    <textarea v-model="formData.excerpt" class="form-input" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nội dung *</label>
                    <textarea v-model="formData.content" class="form-input" rows="6"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">{{
                        t('common.cancel') }}</button>
                    <button @click="saveArticle" class="btn btn-primary"
                        :disabled="isSaving || !formData.title || !formData.content">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : t('common.save') }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
