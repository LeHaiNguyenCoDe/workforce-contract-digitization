<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import BaseModal from '@/components/BaseModal.vue'
import { useSwal } from '@/utils'
import type { QualityCheck, InboundBatch, CreateQualityCheckRequest } from '@/plugins/api/services/WarehouseService'

const swal = useSwal()

interface QualityCheckDisplay extends QualityCheck {
    batch_number?: string
}

const checks = ref<QualityCheckDisplay[]>([])
const isLoading = ref(true)
const statusFilter = ref('')
const showModal = ref(false)
const editingCheck = ref<QualityCheckDisplay | null>(null)
const isSaving = ref(false)

const batches = ref<InboundBatch[]>([])
const products = ref<any[]>([])

const form = ref<CreateQualityCheckRequest & { newIssue: string }>({
    inbound_batch_id: 0,
    product_id: 0,
    check_date: new Date().toISOString().split('T')[0],
    status: 'pass',
    score: 100,
    quantity_passed: 0,
    quantity_failed: 0,
    notes: '',
    issues: [] as string[],
    newIssue: ''
})

const filteredChecks = computed(() => {
    if (!statusFilter.value) return checks.value
    return checks.value.filter(c => c.status === statusFilter.value)
})

const statusConfig: Record<string, { text: string; class: string }> = {
    pass: { text: 'Đạt', class: 'bg-success/10 text-success' },
    fail: { text: 'Không đạt', class: 'bg-error/10 text-error' },
    partial: { text: 'Một phần', class: 'bg-warning/10 text-warning' }
}

const getScoreClass = (score: number) => {
    if (score >= 80) return 'text-success'
    if (score >= 60) return 'text-warning'
    return 'text-error'
}

const fetchChecks = async () => {
    isLoading.value = true
    try {
        const data = await warehouseService.getQualityChecks()
        checks.value = data.map(qc => ({
            ...qc,
            batch_number: qc.inboundBatch?.batch_number || 'N/A'
        }))
    } catch (error: any) {
        console.error('Failed to fetch checks:', error)
        await swal.error('Lấy danh sách QC thất bại!')
        checks.value = []
    } finally {
        isLoading.value = false
    }
}

const fetchMetadata = async () => {
    try {
        const [batchesRes, prodRes] = await Promise.all([
            warehouseService.getInboundBatches({ status: 'received' }).catch((err) => {
                console.error('Error fetching batches:', err)
                return []
            }),
            httpClient.get('/admin/products', { params: { per_page: 1000 } }).catch((err) => {
                console.error('Error fetching products:', err)
                return { data: { data: [] } }
            })
        ])
        
        // Filter batches: chỉ lấy những batch chưa có QC chính thức (BR-03.2)
        const allBatches = Array.isArray(batchesRes) ? batchesRes : []
        batches.value = allBatches.filter(batch => {
            // Nếu batch chưa có qualityCheck hoặc qualityCheck rỗng, thì có thể QC
            return !batch.qualityCheck || batch.qualityCheck.length === 0
        })
        
        products.value = (prodRes.data as any)?.data?.data || (prodRes.data as any)?.data || []
        
        console.log('Fetched batches (all):', allBatches.length)
        console.log('Fetched batches (available for QC):', batches.value.length)
        console.log('Fetched products:', products.value.length)
        
        if (batches.value.length === 0 && allBatches.length > 0) {
            console.warn('Có batches nhưng tất cả đã có QC')
        }
    } catch (error: any) {
        console.error('Failed to fetch metadata:', error)
        await swal.error('Lấy dữ liệu thất bại: ' + (error.message || 'Unknown error'))
        batches.value = []
        products.value = []
    }
}

const openCreateModal = async () => {
    editingCheck.value = null
    form.value = {
        inbound_batch_id: 0,
        product_id: 0,
        check_date: new Date().toISOString().split('T')[0],
        status: 'pass',
        score: 100,
        quantity_passed: 0,
        quantity_failed: 0,
        notes: '',
        issues: [],
        newIssue: ''
    }
    // Refresh batches khi mở modal để đảm bảo có dữ liệu mới nhất
    await fetchMetadata()
    showModal.value = true
}

const onBatchChange = () => {
    const batch = batches.value.find(b => b.id === form.value.inbound_batch_id)
    if (batch && batch.items && batch.items.length > 0) {
        // Auto-select first product from batch
        form.value.product_id = batch.items[0].product_id
        // Set default quantities
        const totalQty = batch.items.reduce((sum, item) => sum + item.quantity_received, 0)
        form.value.quantity_passed = totalQty
        form.value.quantity_failed = 0
    }
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
    
    // BR-03.1: Validate batch and product
    if (!form.value.inbound_batch_id || !form.value.product_id) {
        await swal.warning('Vui lòng chọn lô nhập và sản phẩm!')
        return
    }

    // Validate quantities
    if (form.value.quantity_passed < 0 || form.value.quantity_failed < 0) {
        await swal.warning('Số lượng không được âm!')
        return
    }

    if (form.value.status === 'pass' && form.value.quantity_failed > 0) {
        await swal.warning('QC PASS không được có số lượng FAIL!')
        return
    }

    if (form.value.status === 'fail' && form.value.quantity_passed > 0) {
        await swal.warning('QC FAIL không được có số lượng PASS!')
        return
    }

    isSaving.value = true
    try {
        const payload: CreateQualityCheckRequest = {
            inbound_batch_id: form.value.inbound_batch_id,
            product_id: form.value.product_id,
            check_date: form.value.check_date,
            status: form.value.status,
            score: form.value.score || 100,
            quantity_passed: form.value.quantity_passed,
            quantity_failed: form.value.quantity_failed,
            notes: form.value.notes || '',
            issues: form.value.issues || []
        }

        await warehouseService.createQualityCheck(payload)
        showModal.value = false
        await swal.success('Tạo phiếu kiểm tra chất lượng thành công!')
        await fetchChecks()
        await fetchMetadata() // Refresh batches
    } catch (error: any) {
        console.error('Failed to save check:', error)
        const errorMessage = error.response?.data?.message 
            || error.response?.data?.errors 
            || 'Lưu phiếu kiểm tra thất bại!'
        await swal.error(typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage))
    } finally {
        isSaving.value = false
    }
}

const deleteCheck = async (id: number) => {
    const confirmed = await swal.confirmDelete('Bạn có chắc chắn muốn xóa phiếu kiểm tra này?')
    if (!confirmed) return
    
    try {
        await httpClient.delete(`/admin/warehouses/quality-checks/${id}`)
        checks.value = checks.value.filter(c => c.id !== id)
        await swal.success('Xóa phiếu kiểm tra thành công!')
    } catch (error: any) {
        console.error('Failed to delete check:', error)
        await swal.error(error.response?.data?.message || 'Xóa thất bại!')
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
                <p class="text-slate-400 mt-1">QC trên Batch - BR-03.1: QC bắt buộc theo Batch</p>
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
                    <option value="pass">Đạt</option>
                    <option value="partial">Một phần</option>
                    <option value="fail">Không đạt</option>
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
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Số lượng</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày kiểm tra</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Điểm</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="check in filteredChecks" :key="check.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-primary">{{ check.batch_number || check.inboundBatch?.batch_number || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white">{{ check.product?.name || 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white">{{ check.supplier?.name || 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-300">
                                    PASS: {{ check.quantity_passed || 0 }} | FAIL: {{ check.quantity_failed || 0 }}
                                </span>
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
        <BaseModal v-model="showModal" title="Tạo phiếu kiểm tra chất lượng (BR-03.1)"
            size="lg">
            <div class="space-y-4">
                <div class="bg-info/10 border border-info/20 rounded-lg p-3 mb-4">
                    <p class="text-sm text-info">
                        <strong>Lưu ý:</strong> QC bắt buộc theo Batch. Một Batch chỉ có 1 kết quả QC chính thức. 
                        QC PASS/PARTIAL sẽ tự động tạo Inventory.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Lô nhập (Inbound Batch) *</label>
                    <select v-model.number="form.inbound_batch_id" @change="onBatchChange" class="form-input" :disabled="batches.length === 0">
                        <option :value="0">-- Chọn lô nhập --</option>
                        <option v-for="batch in batches" :key="batch.id" :value="batch.id">
                            {{ batch.batch_number }} - {{ batch.warehouse?.name || 'N/A' }} ({{ batch.status }})
                        </option>
                    </select>
                    <p v-if="batches.length === 0" class="text-xs text-warning mt-1">
                        ⚠️ Chưa có lô nhập nào ở trạng thái RECEIVED. Vui lòng tạo và nhận hàng trước khi QC.
                    </p>
                    <p v-else class="text-xs text-slate-400 mt-1">
                        Chỉ hiển thị các lô đã nhận hàng (RECEIVED). Tổng: {{ batches.length }} lô
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Sản phẩm *</label>
                    <select v-model.number="form.product_id" class="form-input">
                        <option :value="0">-- Chọn sản phẩm --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Ngày kiểm tra</label>
                        <input v-model="form.check_date" type="date" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Trạng thái *</label>
                        <select v-model="form.status" class="form-input">
                            <option value="pass">Đạt (PASS)</option>
                            <option value="partial">Một phần (PARTIAL)</option>
                            <option value="fail">Không đạt (FAIL)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng PASS *</label>
                        <input v-model.number="form.quantity_passed" type="number" min="0" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng FAIL *</label>
                        <input v-model.number="form.quantity_failed" type="number" min="0" class="form-input" />
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
                        :disabled="isSaving || !form.inbound_batch_id || !form.product_id">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
