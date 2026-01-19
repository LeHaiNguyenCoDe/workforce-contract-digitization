<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import type { ApiResponse } from '@/plugins/api/types'

const { t } = useI18n()

// Types
interface Quotation {
    id: number
    code: string
    name: string
    customer_id: number
    status: 'draft' | 'sent' | 'accepted' | 'rejected' | 'converted' | 'expired'
    valid_until: string
    total_amount: number
    note?: string
    created_at: string
    customer?: { id: number; name: string; email: string }
    creator?: { id: number; name: string }
    items?: any[]
}

// State
const quotations = ref<Quotation[]>([])
const isLoading = ref(true)
const selectedQuotation = ref<Quotation | null>(null)
const showModal = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

// Fetch quotations
const fetchQuotations = async () => {
    isLoading.value = true
    try {
        const params = new URLSearchParams()
        if (searchQuery.value) params.set('search', searchQuery.value)
        if (statusFilter.value) params.set('status', statusFilter.value)
        params.set('page', String(currentPage.value))

        const response = await httpClient.get<ApiResponse<any>>(`admin/quotations?${params}`)
        const data = response.data.data
        quotations.value = data.data || []
        totalPages.value = data.last_page || 1
    } catch (error) {
        console.error('Failed to fetch quotations:', error)
    } finally {
        isLoading.value = false
    }
}

// Actions
const viewQuotation = async (id: number) => {
    try {
        const response = await httpClient.get<ApiResponse<Quotation>>(`admin/quotations/${id}`)
        selectedQuotation.value = response.data.data || null
        showModal.value = true
    } catch (error) {
        console.error('Failed to fetch quotation:', error)
    }
}

const sendQuotation = async (id: number) => {
    if (!confirm('Gửi báo giá này cho khách hàng?')) return
    try {
        await httpClient.post(`admin/quotations/${id}/send`)
        fetchQuotations()
    } catch (error) {
        console.error('Failed to send quotation:', error)
    }
}

const convertToOrder = async (id: number) => {
    if (!confirm('Chuyển báo giá này thành đơn hàng?')) return
    try {
        await httpClient.post(`admin/quotations/${id}/convert`)
        fetchQuotations()
    } catch (error) {
        console.error('Failed to convert quotation:', error)
    }
}

// Helpers
const formatCurrency = (num: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num)
const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-slate-500/20 text-slate-400',
        sent: 'bg-info/20 text-info',
        accepted: 'bg-success/20 text-success',
        rejected: 'bg-error/20 text-error',
        converted: 'bg-primary/20 text-primary',
        expired: 'bg-warning/20 text-warning',
    }
    return colors[status] || 'bg-slate-500/20 text-slate-400'
}

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Nháp',
        sent: 'Đã gửi',
        accepted: 'Đã chấp nhận',
        rejected: 'Từ chối',
        converted: 'Đã chuyển đơn',
        expired: 'Hết hạn',
    }
    return labels[status] || status
}

onMounted(fetchQuotations)
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-white">Quản lý Báo giá</h1>
            <router-link to="/admin/quotations/create" class="btn-primary flex items-center gap-2">
                <BaseIcon name="plus" :size="18" />
                Tạo báo giá
            </router-link>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input v-model="searchQuery" @input="fetchQuotations" type="text"
                        placeholder="Tìm kiếm theo mã, tên, khách hàng..."
                        class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white placeholder-slate-500">
                </div>
                <select v-model="statusFilter" @change="fetchQuotations"
                    class="px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft">Nháp</option>
                    <option value="sent">Đã gửi</option>
                    <option value="accepted">Đã chấp nhận</option>
                    <option value="converted">Đã chuyển đơn</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div v-if="isLoading" class="p-8 text-center text-slate-400">
                Đang tải...
            </div>
            <div v-else-if="!quotations.length" class="p-8 text-center text-slate-400">
                Chưa có báo giá nào
            </div>
            <table v-else class="w-full">
                <thead class="bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Tên</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Khách hàng</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Giá trị</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Hạn</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Trạng thái</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="q in quotations" :key="q.id" class="hover:bg-dark-700/50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-primary">{{ q.code }}</span>
                        </td>
                        <td class="px-4 py-3 text-white">{{ q.name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ q.customer?.name || '-' }}</td>
                        <td class="px-4 py-3 font-medium gradient-text">{{ formatCurrency(q.total_amount) }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ formatDate(q.valid_until) }}</td>
                        <td class="px-4 py-3">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(q.status)]">
                                {{ getStatusLabel(q.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="viewQuotation(q.id)"
                                    class="p-2 rounded-lg hover:bg-dark-600 text-slate-400 hover:text-white">
                                    <BaseIcon name="eye" :size="16" />
                                </button>
                                <button v-if="q.status === 'draft'" @click="sendQuotation(q.id)"
                                    class="p-2 rounded-lg hover:bg-info/20 text-info">
                                    <BaseIcon name="send" :size="16" />
                                </button>
                                <button v-if="['sent', 'accepted'].includes(q.status)" @click="convertToOrder(q.id)"
                                    class="p-2 rounded-lg hover:bg-success/20 text-success">
                                    <BaseIcon name="orders" :size="16" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex justify-center gap-2 p-4 border-t border-white/5">
                <button v-for="page in totalPages" :key="page" @click="currentPage = page; fetchQuotations()"
                    :class="['px-3 py-1 rounded', currentPage === page ? 'bg-primary text-white' : 'bg-dark-600 text-slate-400 hover:bg-dark-500']">
                    {{ page }}
                </button>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showModal && selectedQuotation" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-2xl bg-dark-800 rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-white/10">
                    <h3 class="text-lg font-bold text-white">Chi tiết báo giá: {{ selectedQuotation.code }}</h3>
                    <button @click="showModal = false" class="p-2 rounded-lg hover:bg-dark-700">
                        <BaseIcon name="close" :size="20" class="text-slate-400" />
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-slate-400">Khách hàng</div>
                            <div class="text-white">{{ selectedQuotation.customer?.name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Email</div>
                            <div class="text-white">{{ selectedQuotation.customer?.email }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Người tạo</div>
                            <div class="text-white">{{ selectedQuotation.creator?.name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Hiệu lực đến</div>
                            <div class="text-white">{{ formatDate(selectedQuotation.valid_until) }}</div>
                        </div>
                    </div>
                    <hr class="border-white/10">
                    <div>
                        <div class="text-sm text-slate-400 mb-2">Sản phẩm</div>
                        <div v-if="selectedQuotation.items?.length" class="space-y-2">
                            <div v-for="item in selectedQuotation.items" :key="item.id"
                                class="flex justify-between p-3 rounded-lg bg-dark-700">
                                <div>
                                    <div class="text-white">{{ item.product?.name }}</div>
                                    <div class="text-sm text-slate-400">{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</div>
                                </div>
                                <div class="font-medium gradient-text">{{ formatCurrency(item.total) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-lg bg-primary/10 border border-primary/20">
                        <span class="text-white font-medium">Tổng cộng</span>
                        <span class="text-2xl font-bold gradient-text">{{ formatCurrency(selectedQuotation.total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-primary {
    @apply px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90 transition-all;
}
</style>
