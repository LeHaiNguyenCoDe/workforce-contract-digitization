<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSwal } from '@/utils'
import BaseModal from '@/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface ShippingPartner {
    code: string
    name: string
}

interface ReconciliationItem {
    id: number
    order_id: number
    tracking_number: string | null
    expected_amount: number
    received_amount: number | null
    status: string
    notes: string | null
    order?: { id: number; order_code: string }
}

interface Reconciliation {
    id: number
    reconciliation_code: string
    shipping_partner: string
    period_from: string
    period_to: string
    expected_amount: number
    received_amount: number
    difference: number
    status: string
    items_count: number
    items?: ReconciliationItem[]
    created_at: string
}

const swal = useSwal()

// State
const reconciliations = ref<Reconciliation[]>([])
const shippingPartners = ref<ShippingPartner[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const showCreateModal = ref(false)
const showDetailModal = ref(false)
const selectedReconciliation = ref<Reconciliation | null>(null)

const newForm = ref({
    shipping_partner: '',
    period_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    period_to: new Date().toISOString().split('T')[0],
    notes: ''
})

async function fetchReconciliations() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/cod-reconciliations')
        reconciliations.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch:', error)
    } finally {
        isLoading.value = false
    }
}

async function fetchShippingPartners() {
    try {
        const response = await httpClient.get('/admin/cod-reconciliations/shipping-partners')
        shippingPartners.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch partners:', error)
    }
}

function getStatusBadge(status: string) {
    switch (status) {
        case 'draft': return { class: 'bg-slate-500/10 text-slate-400', label: 'Nháp' }
        case 'pending': return { class: 'bg-info/10 text-info', label: 'Đang đối soát' }
        case 'matched': return { class: 'bg-success/10 text-success', label: 'Khớp' }
        case 'discrepancy': return { class: 'bg-warning/10 text-warning', label: 'Lệch' }
        case 'resolved': return { class: 'bg-primary/10 text-primary', label: 'Đã xử lý' }
        default: return { class: 'bg-slate-500/10 text-slate-400', label: status }
    }
}

function getItemStatusBadge(status: string) {
    switch (status) {
        case 'pending': return { class: 'text-slate-400', label: 'Chờ' }
        case 'matched': return { class: 'text-success', label: '✓' }
        case 'over': return { class: 'text-info', label: '+Dư' }
        case 'short': return { class: 'text-warning', label: '-Thiếu' }
        case 'missing': return { class: 'text-error', label: '✕ Mất' }
        default: return { class: 'text-slate-400', label: status }
    }
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('vi-VN')
}

function getPartnerName(code: string): string {
    return shippingPartners.value.find(p => p.code === code)?.name || code
}

async function createReconciliation() {
    if (!newForm.value.shipping_partner) {
        await swal.warning('Vui lòng chọn đối tác vận chuyển!')
        return
    }

    try {
        isSaving.value = true
        const response = await httpClient.post('/admin/cod-reconciliations', newForm.value)
        await swal.success('Tạo phiên đối soát thành công!')
        showCreateModal.value = false
        await fetchReconciliations()
        openDetail((response.data as any).data)
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    } finally {
        isSaving.value = false
    }
}

async function openDetail(rec: Reconciliation) {
    try {
        const response = await httpClient.get(`/admin/cod-reconciliations/${rec.id}`)
        selectedReconciliation.value = (response.data as any).data
        showDetailModal.value = true
    } catch (error) {
        console.error('Failed to fetch detail:', error)
    }
}

async function saveItemAmounts() {
    if (!selectedReconciliation.value?.items) return

    try {
        isSaving.value = true
        const items = selectedReconciliation.value.items.map(item => ({
            id: item.id,
            received_amount: item.received_amount || 0,
            notes: item.notes
        }))
        await httpClient.put(`/admin/cod-reconciliations/${selectedReconciliation.value.id}/items`, { items })
        await swal.success('Lưu thành công!')
        await openDetail(selectedReconciliation.value)
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    } finally {
        isSaving.value = false
    }
}

async function reconcile() {
    if (!selectedReconciliation.value) return
    const confirmed = await swal.confirm('Hoàn tất đối soát?')
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/cod-reconciliations/${selectedReconciliation.value.id}/reconcile`)
        await swal.success('Đối soát hoàn tất!')
        showDetailModal.value = false
        await fetchReconciliations()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function deleteReconciliation(rec: Reconciliation) {
    const confirmed = await swal.confirm(`Xóa ${rec.reconciliation_code}?`)
    if (!confirmed) return

    try {
        await httpClient.delete(`/admin/cod-reconciliations/${rec.id}`)
        await swal.success('Đã xóa!')
        await fetchReconciliations()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

onMounted(async () => {
    await Promise.all([fetchReconciliations(), fetchShippingPartners()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Đối soát COD</h1>
                <p class="text-slate-400 mt-1">So khớp tiền COD từ đối tác vận chuyển với đơn hàng</p>
            </div>
            <button @click="showCreateModal = true" class="btn btn-primary">+ Tạo đối soát</button>
        </div>

        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="reconciliations.length === 0"
                class="flex-1 flex items-center justify-center text-slate-400">
                Chưa có phiên đối soát nào
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Mã</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">ĐTVC</th>
                            <th class="px-4 py-3 text-center text-xs text-slate-400 uppercase">Kỳ</th>
                            <th class="px-4 py-3 text-center text-xs text-slate-400 uppercase">Số đơn</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Kỳ vọng</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Thực nhận</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Chênh lệch</th>
                            <th class="px-4 py-3 text-center text-xs text-slate-400 uppercase">Trạng thái</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="rec in reconciliations" :key="rec.id" class="hover:bg-white/5 cursor-pointer"
                            @click="openDetail(rec)">
                            <td class="px-4 py-3 font-mono text-primary">{{ rec.reconciliation_code }}</td>
                            <td class="px-4 py-3 text-white">{{ getPartnerName(rec.shipping_partner) }}</td>
                            <td class="px-4 py-3 text-center text-slate-400 text-sm">{{ formatDate(rec.period_from) }} -
                                {{ formatDate(rec.period_to) }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ rec.items_count }}</td>
                            <td class="px-4 py-3 text-right text-slate-300">{{ formatCurrency(rec.expected_amount) }}
                            </td>
                            <td class="px-4 py-3 text-right text-white">{{ formatCurrency(rec.received_amount) }}</td>
                            <td class="px-4 py-3 text-right font-medium"
                                :class="rec.difference === 0 ? 'text-success' : rec.difference > 0 ? 'text-info' : 'text-error'">
                                {{ rec.difference > 0 ? '+' : '' }}{{ formatCurrency(rec.difference) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(rec.status).class]">
                                    {{ getStatusBadge(rec.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <button v-if="rec.status === 'draft'" @click="deleteReconciliation(rec)"
                                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <BaseModal v-model="showCreateModal" title="Tạo phiên đối soát COD" size="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Đối tác vận chuyển *</label>
                    <select v-model="newForm.shipping_partner" class="form-select w-full">
                        <option value="">-- Chọn --</option>
                        <option v-for="p in shippingPartners" :key="p.code" :value="p.code">{{ p.name }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Từ ngày *</label>
                        <input v-model="newForm.period_from" type="date" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Đến ngày *</label>
                        <input v-model="newForm.period_to" type="date" class="form-input w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showCreateModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="createReconciliation" class="btn btn-primary" :disabled="isSaving">Tạo</button>
                </div>
            </template>
        </BaseModal>

        <!-- Detail Modal -->
        <BaseModal v-model="showDetailModal" :title="`Đối soát ${selectedReconciliation?.reconciliation_code || ''}`"
            size="xl">
            <div v-if="selectedReconciliation" class="space-y-4">
                <!-- Summary -->
                <div class="grid grid-cols-4 gap-4 p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-slate-400 text-sm">Kỳ vọng</p>
                        <p class="text-xl font-bold text-white">{{
                            formatCurrency(selectedReconciliation.expected_amount) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">Thực nhận</p>
                        <p class="text-xl font-bold text-success">{{
                            formatCurrency(selectedReconciliation.received_amount) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">Chênh lệch</p>
                        <p class="text-xl font-bold"
                            :class="selectedReconciliation.difference === 0 ? 'text-success' : 'text-error'">
                            {{ formatCurrency(selectedReconciliation.difference) }}
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <button
                            v-if="selectedReconciliation.status !== 'matched' && selectedReconciliation.status !== 'resolved'"
                            @click="saveItemAmounts" class="btn btn-sm btn-secondary" :disabled="isSaving">Lưu</button>
                        <button
                            v-if="selectedReconciliation.status !== 'matched' && selectedReconciliation.status !== 'resolved'"
                            @click="reconcile" class="btn btn-sm btn-success">Hoàn tất</button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="max-h-80 overflow-auto border border-white/10 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-dark-700 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs text-slate-400">Đơn hàng</th>
                                <th class="px-3 py-2 text-left text-xs text-slate-400">Mã vận đơn</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">Kỳ vọng</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">Thực nhận</th>
                                <th class="px-3 py-2 text-center text-xs text-slate-400">TT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="item in selectedReconciliation.items" :key="item.id">
                                <td class="px-3 py-2 text-white">{{ item.order?.order_code || `#${item.order_id}` }}
                                </td>
                                <td class="px-3 py-2 text-slate-400 font-mono text-sm">{{ item.tracking_number || '-' }}
                                </td>
                                <td class="px-3 py-2 text-right text-slate-300">{{ formatCurrency(item.expected_amount)
                                    }}</td>
                                <td class="px-3 py-2 text-right">
                                    <input
                                        v-if="selectedReconciliation.status !== 'matched' && selectedReconciliation.status !== 'resolved'"
                                        v-model.number="item.received_amount" type="number" min="0"
                                        class="form-input w-28 text-right py-1 text-sm" />
                                    <span v-else class="text-white">{{ formatCurrency(item.received_amount || 0)
                                        }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span :class="getItemStatusBadge(item.status).class">{{
                                        getItemStatusBadge(item.status).label }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <template #footer>
                <button @click="showDetailModal = false" class="btn btn-secondary">Đóng</button>
            </template>
        </BaseModal>
    </div>
</template>
