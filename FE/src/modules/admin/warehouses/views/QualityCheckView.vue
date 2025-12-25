<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'

interface QualityCheck {
    id: number
    batch_number: string
    product?: { id: number; name: string }
    product_id?: number
    supplier?: { id: number; name: string }
    supplier_id?: number
    inspector?: { name: string }
    check_date: string
    status: 'passed' | 'failed' | 'pending'
    score: number
    notes: string
    issues: string[]
}

const checks = ref<QualityCheck[]>([])
const isLoading = ref(true)
const statusFilter = ref('')
const showModal = ref(false)
const editingCheck = ref<QualityCheck | null>(null)
const isSaving = ref(false)

const products = ref<any[]>([])
const suppliers = ref<any[]>([])

const form = ref({
    batch_number: '',
    product_id: null as number | null,
    supplier_id: null as number | null,
    check_date: new Date().toISOString().split('T')[0],
    status: 'pending' as 'passed' | 'failed' | 'pending',
    score: 100,
    notes: '',
    issues: [] as string[],
    newIssue: ''
})

const filteredChecks = computed(() => {
    if (!statusFilter.value) return checks.value
    return checks.value.filter(c => c.status === statusFilter.value)
})

const statusConfig: Record<string, { text: string; class: string }> = {
    passed: { text: 'Đạt', class: 'bg-success/10 text-success' },
    failed: { text: 'Không đạt', class: 'bg-error/10 text-error' },
    pending: { text: 'Đang kiểm tra', class: 'bg-warning/10 text-warning' }
}

const getScoreClass = (score: number) => {
    if (score >= 80) return 'text-success'
    if (score >= 60) return 'text-warning'
    return 'text-error'
}

const fetchChecks = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/warehouses/quality-checks')
        const data = (response.data as any).data
        checks.value = data || []
    } catch (error) {
        console.error('Failed to fetch checks:', error)
        checks.value = []
    } finally {
        isLoading.value = false
    }
}

const fetchMetadata = async () => {
    try {
        const [prodRes, supRes] = await Promise.all([
            httpClient.get('/admin/products'),
            httpClient.get('/admin/suppliers')
        ])
        products.value = (prodRes.data as any).data?.data || (prodRes.data as any).data || []
        suppliers.value = (supRes.data as any).data?.data || (supRes.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch metadata:', error)
    }
}

const openCreateModal = () => {
    editingCheck.value = null
    form.value = {
        batch_number: `BATCH-${Date.now()}`,
        product_id: null,
        supplier_id: null,
        check_date: new Date().toISOString().split('T')[0],
        status: 'pending',
        score: 100,
        notes: '',
        issues: [],
        newIssue: ''
    }
    showModal.value = true
}

const openEditModal = (check: QualityCheck) => {
    editingCheck.value = check
    form.value = {
        batch_number: check.batch_number,
        product_id: check.product_id || check.product?.id || null,
        supplier_id: check.supplier_id || check.supplier?.id || null,
        check_date: check.check_date?.split('T')[0] || '',
        status: check.status,
        score: check.score,
        notes: check.notes || '',
        issues: [...(check.issues || [])],
        newIssue: ''
    }
    showModal.value = true
}

const addIssue = () => {
    if (form.value.newIssue.trim()) {
        form.value.issues.push(form.value.newIssue.trim())
        form.value.newIssue = ''
    }
}

const removeIssue = (index: number) => {
    form.value.issues.splice(index, 1)
}

const saveCheck = async () => {
    if (isSaving.value) return
    if (!form.value.product_id || !form.value.supplier_id) {
        alert('Vui lòng chọn sản phẩm và nhà cung cấp!')
        return
    }

    isSaving.value = true
    try {
        const payload = {
            batch_number: form.value.batch_number,
            product_id: form.value.product_id,
            supplier_id: form.value.supplier_id,
            check_date: form.value.check_date,
            status: form.value.status,
            score: form.value.score,
            notes: form.value.notes,
            issues: form.value.issues
        }

        if (editingCheck.value) {
            await httpClient.put(`/admin/warehouses/quality-checks/${editingCheck.value.id}`, payload)
        } else {
            await httpClient.post(`/admin/warehouses/quality-checks`, payload)
        }
        showModal.value = false
        fetchChecks()
    } catch (error: any) {
        console.error('Failed to save check:', error)
        alert(error.response?.data?.message || 'Lưu phiếu kiểm tra thất bại!')
    } finally {
        isSaving.value = false
    }
}

const deleteCheck = async (id: number) => {
    console.log('Delete check clicked:', id)
    if (!window.confirm('Bạn có chắc chắn muốn xóa phiếu kiểm tra này?')) return
    try {
        await httpClient.delete(`/admin/warehouses/quality-checks/${id}`)
        checks.value = checks.value.filter(c => c.id !== id)
    } catch (error: any) {
        console.error('Failed to delete check:', error)
        alert(error.response?.data?.message || 'Xóa thất bại!')
    }
}

onMounted(() => {
    fetchChecks()
    fetchMetadata()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Kiểm tra chất lượng</h1>
                <p class="text-slate-400 mt-1">Quản lý phiếu kiểm tra chất lượng sản phẩm</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Tạo phiếu
            </button>
        </div>

        <!-- Filter -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <select v-model="statusFilter" class="form-input w-48">
                    <option value="">Tất cả trạng thái</option>
                    <option value="passed">Đạt</option>
                    <option value="pending">Đang kiểm tra</option>
                    <option value="failed">Không đạt</option>
                </select>
            </div>
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
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Số lô</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Nhà cung cấp</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày kiểm tra</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Điểm</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="check in filteredChecks" :key="check.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-primary">{{ check.batch_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white">{{ check.product?.name || 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white">{{ check.supplier?.name || 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-400">{{ check.check_date }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['font-bold text-lg', getScoreClass(check.score)]">
                                    {{ check.score }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                    statusConfig[check.status]?.class || 'bg-slate-500/10 text-slate-400'
                                ]">
                                    {{ statusConfig[check.status]?.text || check.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(check)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteCheck(check.id)"
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

                <div v-if="!filteredChecks.length" class="py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    <p class="text-slate-400">Chưa có phiếu kiểm tra nào</p>
                </div>
            </div>
        </div>

        <!-- Modal using BaseModal -->
        <BaseModal :show="showModal" :title="editingCheck ? 'Cập nhật phiếu kiểm tra' : 'Tạo phiếu kiểm tra mới'"
            size="lg" @close="showModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Số lô (Batch Number)</label>
                    <input v-model="form.batch_number" type="text" class="form-input" :disabled="!!editingCheck" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Sản phẩm *</label>
                        <select v-model="form.product_id" class="form-input" :disabled="!!editingCheck">
                            <option :value="null">-- Chọn sản phẩm --</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nhà cung cấp *</label>
                        <select v-model="form.supplier_id" class="form-input" :disabled="!!editingCheck">
                            <option :value="null">-- Chọn nhà cung cấp --</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Ngày kiểm tra</label>
                        <input v-model="form.check_date" type="date" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Trạng thái</label>
                        <select v-model="form.status" class="form-input">
                            <option value="pending">Đang kiểm tra</option>
                            <option value="passed">Đạt</option>
                            <option value="failed">Không đạt</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Điểm đánh giá (0-100)</label>
                    <input v-model.number="form.score" type="number" min="0" max="100" class="form-input" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
                    <textarea v-model="form.notes" class="form-input" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Vấn đề phát hiện</label>
                    <div class="flex gap-2 mb-2">
                        <input v-model="form.newIssue" type="text" class="form-input flex-1"
                            placeholder="Thêm vấn đề..." @keyup.enter="addIssue" />
                        <button type="button" class="btn btn-secondary" @click="addIssue">Thêm</button>
                    </div>
                    <div v-if="form.issues.length" class="flex flex-wrap gap-2">
                        <span v-for="(issue, i) in form.issues" :key="i"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-error/10 text-error text-xs rounded-full">
                            {{ issue }}
                            <button @click="removeIssue(i)" class="hover:text-white">&times;</button>
                        </span>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">Hủy</button>
                    <button @click="saveCheck" class="btn btn-primary"
                        :disabled="isSaving || !form.product_id || !form.supplier_id">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
