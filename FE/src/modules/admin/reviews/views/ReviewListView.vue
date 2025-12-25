<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()

interface Review {
    id: number
    rating: number
    content: string
    is_admin_reply?: boolean
    status: 'pending' | 'approved' | 'rejected'
    product?: { id: number; name: string }
    user?: { id: number; name: string; email: string }
    created_at: string
}

const reviews = ref<Review[]>([])
const isLoading = ref(true)
const statusFilter = ref('')

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')
const getStatusColor = (status: string) => ({ pending: 'bg-warning/10 text-warning', approved: 'bg-success/10 text-success', rejected: 'bg-error/10 text-error' }[status] || 'bg-slate-500/10 text-slate-400')
const getStatusText = (status: string) => ({ pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' }[status] || status)

const fetchReviews = async () => {
    isLoading.value = true
    try {
        const params: any = {}
        if (statusFilter.value) params.status = statusFilter.value
        const response: any = await httpClient.get('/admin/reviews', { params })
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) reviews.value = data.data.data
        else if (Array.isArray(data?.data)) reviews.value = data.data
        else reviews.value = []
    } catch (error) {
        reviews.value = []
    } finally {
        isLoading.value = false
    }
}

const approveReview = async (review: Review) => {
    try { await httpClient.put(`/admin/reviews/${review.id}/approve`); review.status = 'approved' } catch (error) { }
}

const rejectReview = async (review: Review) => {
    try { await httpClient.put(`/admin/reviews/${review.id}/reject`); review.status = 'rejected' } catch (error) { }
}

const deleteReview = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) return
    try { await httpClient.delete(`/admin/reviews/${id}`); reviews.value = reviews.value.filter(r => r.id !== id) } catch (error) { alert('Xóa thất bại!') }
}

onMounted(fetchReviews)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.reviews') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý đánh giá sản phẩm</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex flex-col sm:flex-row gap-4">
                <select v-model="statusFilter" @change="fetchReviews" class="form-input sm:w-48">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="rejected">Từ chối</option>
                </select>
                <button class="btn btn-secondary" @click="fetchReviews">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                    Làm mới
                </button>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto divide-y divide-white/5">
                <div v-for="review in reviews" :key="review.id" class="p-6 hover:bg-white/5 transition-colors">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}</div>
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-4 mb-2">
                                <div>
                                    <p class="font-medium text-white">{{ review.user?.name || 'Anonymous' }}
                                        <span v-if="review.is_admin_reply"
                                            class="ml-2 px-1.5 py-0.5 bg-primary/20 text-primary-light text-[10px] rounded uppercase">Phản
                                            hồi</span>
                                    </p>
                                    <p class="text-xs text-slate-500">{{ review.user?.email }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium', getStatusColor(review.status)]">{{
                                            getStatusText(review.status) }}</span>
                                    <span class="text-sm text-slate-500">{{ formatDate(review.created_at) }}</span>
                                </div>
                            </div>
                            <!-- Product -->
                            <div v-if="review.product" class="mb-3">
                                <span class="text-xs text-slate-500">Sản phẩm:</span>
                                <span class="text-sm text-primary-light ml-1">{{ review.product.name }}</span>
                            </div>
                            <!-- Rating -->
                            <div class="flex items-center gap-1 mb-2">
                                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" :fill="i <= review.rating ? 'currentColor' : 'none'"
                                    :class="i <= review.rating ? 'text-yellow-400' : 'text-slate-600'"
                                    stroke="currentColor" stroke-width="2">
                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                                <span class="text-sm text-slate-400 ml-2">{{ review.rating }}/5</span>
                            </div>
                            <!-- Content -->
                            <p class="text-slate-300 text-sm whitespace-pre-line">{{ review.content }}</p>
                            <!-- Actions -->
                            <div class="flex items-center gap-2 mt-4" v-if="review.status === 'pending'">
                                <button @click="approveReview(review)"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-success/10 text-success rounded-lg hover:bg-success/20 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    Duyệt
                                </button>
                                <button @click="rejectReview(review)"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-error/10 text-error rounded-lg hover:bg-error/20 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                    Từ chối
                                </button>
                            </div>
                            <div class="mt-4" v-else>
                                <button @click="deleteReview(review.id)" class="text-sm text-error hover:underline">Xóa
                                    đánh giá</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!reviews.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có đánh giá nào</p>
                </div>
            </div>
        </div>
    </div>
</template>
