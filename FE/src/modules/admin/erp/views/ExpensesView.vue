<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/utils'
import BaseModal from '@/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Category {
    id: number
    name: string
    code: string
    type: 'expense' | 'income'
}

interface Expense {
    id: number
    expense_code: string
    category_id: number
    category?: Category
    warehouse_id: number | null
    type: 'expense' | 'income'
    amount: number
    expense_date: string
    payment_method: string | null
    reference_number: string | null
    description: string | null
    status: 'pending' | 'approved' | 'rejected'
    created_at: string
}

interface Summary {
    total_expense: number
    total_income: number
    net: number
    by_category: Array<{ category_id: number; total: number; category?: Category }>
}

const swal = useSwal()

// State
const expenses = ref<Expense[]>([])
const categories = ref<Category[]>([])
const summary = ref<Summary>({ total_expense: 0, total_income: 0, net: 0, by_category: [] })
const isLoading = ref(true)
const isSaving = ref(false)
const showModal = ref(false)
const editingExpense = ref<Expense | null>(null)
const typeFilter = ref('')

const form = ref({
    category_id: null as number | null,
    type: 'expense' as 'expense' | 'income',
    amount: 0,
    expense_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    reference_number: '',
    description: ''
})

const filteredCategories = computed(() => categories.value.filter(c => c.type === form.value.type))

async function fetchExpenses() {
    try {
        isLoading.value = true
        const params: Record<string, string> = {}
        if (typeFilter.value) params.type = typeFilter.value
        const response = await httpClient.get('/admin/expenses', { params })
        expenses.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch expenses:', error)
    } finally {
        isLoading.value = false
    }
}

async function fetchCategories() {
    try {
        const response = await httpClient.get('/admin/expenses/categories')
        categories.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
}

async function fetchSummary() {
    try {
        const response = await httpClient.get('/admin/expenses/summary')
        summary.value = (response.data as any).data
    } catch (error) {
        console.error('Failed to fetch summary:', error)
    }
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('vi-VN')
}

function openCreateModal(type: 'expense' | 'income' = 'expense') {
    editingExpense.value = null
    form.value = { category_id: null, type, amount: 0, expense_date: new Date().toISOString().split('T')[0], payment_method: '', reference_number: '', description: '' }
    showModal.value = true
}

function openEditModal(expense: Expense) {
    editingExpense.value = expense
    form.value = {
        category_id: expense.category_id,
        type: expense.type,
        amount: expense.amount,
        expense_date: expense.expense_date,
        payment_method: expense.payment_method || '',
        reference_number: expense.reference_number || '',
        description: expense.description || ''
    }
    showModal.value = true
}

async function saveExpense() {
    if (!form.value.category_id) {
        await swal.warning('Vui lòng chọn danh mục!')
        return
    }
    if (form.value.amount <= 0) {
        await swal.warning('Số tiền phải lớn hơn 0!')
        return
    }

    try {
        isSaving.value = true
        if (editingExpense.value) {
            await httpClient.put(`/admin/expenses/${editingExpense.value.id}`, form.value)
            await swal.success('Cập nhật thành công!')
        } else {
            await httpClient.post('/admin/expenses', form.value)
            await swal.success('Tạo thành công!')
        }
        showModal.value = false
        await Promise.all([fetchExpenses(), fetchSummary()])
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    } finally {
        isSaving.value = false
    }
}

async function deleteExpense(expense: Expense) {
    const confirmed = await swal.confirm(`Xóa ${expense.expense_code}?`)
    if (!confirmed) return

    try {
        await httpClient.delete(`/admin/expenses/${expense.id}`)
        await swal.success('Đã xóa!')
        await Promise.all([fetchExpenses(), fetchSummary()])
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

onMounted(async () => {
    await Promise.all([fetchExpenses(), fetchCategories(), fetchSummary()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Thu chi</h1>
                <p class="text-slate-400 mt-1">Quản lý các khoản chi phí vận hành và thu nhập</p>
            </div>
            <div class="flex gap-2">
                <button @click="openCreateModal('income')" class="btn btn-success">+ Thu</button>
                <button @click="openCreateModal('expense')" class="btn btn-error">+ Chi</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                <p class="text-slate-400 text-sm">Tổng thu (tháng này)</p>
                <p class="text-2xl font-bold text-success">{{ formatCurrency(summary.total_income) }}</p>
            </div>
            <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                <p class="text-slate-400 text-sm">Tổng chi (tháng này)</p>
                <p class="text-2xl font-bold text-error">{{ formatCurrency(summary.total_expense) }}</p>
            </div>
            <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                <p class="text-slate-400 text-sm">Cân đối</p>
                <p class="text-2xl font-bold" :class="summary.net >= 0 ? 'text-success' : 'text-error'">
                    {{ formatCurrency(summary.net) }}
                </p>
            </div>
        </div>

        <!-- Filter -->
        <div class="flex gap-4 mb-4">
            <select v-model="typeFilter" @change="fetchExpenses" class="form-select w-40">
                <option value="">Tất cả</option>
                <option value="expense">Chi</option>
                <option value="income">Thu</option>
            </select>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="expenses.length === 0" class="flex-1 flex items-center justify-center text-slate-400">
                Chưa có khoản thu chi nào
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Loại</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Danh mục</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Số tiền</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ngày</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mô tả</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="exp in expenses" :key="exp.id" class="hover:bg-white/5">
                            <td class="px-4 py-3 font-mono text-primary">{{ exp.expense_code }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="exp.type === 'income' ? 'bg-success/10 text-success' : 'bg-error/10 text-error'"
                                    class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ exp.type === 'income' ? 'Thu' : 'Chi' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-white">{{ exp.category?.name || '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium"
                                :class="exp.type === 'income' ? 'text-success' : 'text-error'">
                                {{ exp.type === 'income' ? '+' : '-' }}{{ formatCurrency(exp.amount) }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(exp.expense_date) }}</td>
                            <td class="px-4 py-3 text-slate-400 truncate max-w-xs">{{ exp.description || '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(exp)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center">✎</button>
                                    <button @click="deleteExpense(exp)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">✕</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal"
            :title="editingExpense ? 'Sửa khoản' : (form.type === 'income' ? 'Thêm khoản thu' : 'Thêm khoản chi')"
            size="md">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Loại *</label>
                        <select v-model="form.type" class="form-select w-full" :disabled="!!editingExpense">
                            <option value="expense">Chi</option>
                            <option value="income">Thu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                        <select v-model="form.category_id" class="form-select w-full">
                            <option :value="null" disabled>-- Chọn danh mục --</option>
                            <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="filteredCategories.length === 0" class="text-xs text-warning mt-1">
                            Chưa có danh mục {{ form.type === 'income' ? 'thu' : 'chi' }}. Vui lòng tạo danh mục trước.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số tiền (VNĐ) *</label>
                        <input v-model.number="form.amount" type="number" min="0" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Ngày *</label>
                        <input v-model="form.expense_date" type="date" class="form-input w-full" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
                    <textarea v-model="form.description" rows="2" class="form-input w-full"
                        placeholder="Mô tả..."></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="saveExpense" class="btn btn-primary" :disabled="isSaving">Lưu</button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
