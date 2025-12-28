/**
 * useExpenses Composable
 */
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/shared/utils'
import expenseService from '../services/expenseService'
import type { Expense, Category, Summary, CreateExpensePayload } from '../models/expense'

export function useExpenses() {
    const swal = useSwal()

    const expenses = ref<Expense[]>([])
    const categories = ref<Category[]>([])
    const summary = ref<Summary>({ total_expense: 0, total_income: 0, net: 0, by_category: [] })
    const isLoading = ref(false)
    const isSaving = ref(false)
    const typeFilter = ref('')

    const showModal = ref(false)
    const editingExpense = ref<Expense | null>(null)
    const form = ref<CreateExpensePayload>({
        category_id: null, type: 'expense', amount: 0, expense_date: new Date().toISOString().split('T')[0], payment_method: '', reference_number: '', description: ''
    })

    const filteredCategories = computed(() => categories.value.filter(c => c.type === form.value.type))

    async function fetchExpenses() {
        isLoading.value = true
        try {
            const params: Record<string, string> = {}
            if (typeFilter.value) params.type = typeFilter.value
            expenses.value = await expenseService.getAll(params)
        } catch (error) {
            console.error('Failed to fetch expenses:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function fetchCategories() {
        try {
            categories.value = await expenseService.getCategories()
        } catch (error) {
            console.error('Failed to fetch categories:', error)
        }
    }

    async function fetchSummary() {
        try {
            summary.value = await expenseService.getSummary()
        } catch (error) {
            console.error('Failed to fetch summary:', error)
        }
    }

    function openCreate(type: 'expense' | 'income' = 'expense') {
        editingExpense.value = null
        form.value = { category_id: null, type, amount: 0, expense_date: new Date().toISOString().split('T')[0], payment_method: '', reference_number: '', description: '' }
        showModal.value = true
    }

    function openEdit(expense: Expense) {
        editingExpense.value = expense
        form.value = {
            category_id: expense.category_id, type: expense.type, amount: expense.amount, expense_date: expense.expense_date,
            payment_method: expense.payment_method || '', reference_number: expense.reference_number || '', description: expense.description || ''
        }
        showModal.value = true
    }

    async function saveExpense() {
        if (!form.value.category_id) { await swal.warning('Vui lòng chọn danh mục!'); return }
        if (form.value.amount <= 0) { await swal.warning('Số tiền phải lớn hơn 0!'); return }

        isSaving.value = true
        try {
            if (editingExpense.value) {
                await expenseService.update(editingExpense.value.id, form.value)
                await swal.success('Cập nhật thành công!')
            } else {
                await expenseService.create(form.value)
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
            await expenseService.delete(expense.id)
            await swal.success('Đã xóa!')
            await Promise.all([fetchExpenses(), fetchSummary()])
        } catch (error: any) {
            await swal.error(error.response?.data?.message || 'Lỗi!')
        }
    }

    function setTypeFilter(type: string) {
        typeFilter.value = type
        fetchExpenses()
    }

    onMounted(async () => {
        await Promise.all([fetchExpenses(), fetchCategories(), fetchSummary()])
    })

    return {
        expenses, categories, summary, isLoading, isSaving, typeFilter, showModal, editingExpense, form, filteredCategories,
        fetchExpenses, openCreate, openEdit, saveExpense, deleteExpense, setTypeFilter
    }
}
