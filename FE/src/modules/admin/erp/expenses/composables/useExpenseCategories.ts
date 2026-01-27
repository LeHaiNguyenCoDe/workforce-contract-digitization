/**
 * useExpenseCategories Composable
 */
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/utils'
import { expenseCategoryService, type ExpenseCategory, type CreateCategoryPayload } from '../services/expenseCategoryService'

export function useExpenseCategories() {
    const swal = useSwal()

    const categories = ref<ExpenseCategory[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)
    const typeFilter = ref<'all' | 'expense' | 'income'>('all')

    const showModal = ref(false)
    const editingCategory = ref<ExpenseCategory | null>(null)
    const form = ref<CreateCategoryPayload>({
        name: '',
        code: '',
        type: 'expense',
        description: ''
    })

    const filteredCategories = computed(() => {
        if (typeFilter.value === 'all') return categories.value
        return categories.value.filter(c => c.type === typeFilter.value)
    })

    const expenseCategories = computed(() => categories.value.filter(c => c.type === 'expense'))
    const incomeCategories = computed(() => categories.value.filter(c => c.type === 'income'))

    async function fetchCategories() {
        isLoading.value = true
        try {
            categories.value = await expenseCategoryService.getAll()
        } catch (error) {
            console.error('Failed to fetch categories:', error)
        } finally {
            isLoading.value = false
        }
    }

    function openCreate(type: 'expense' | 'income' = 'expense') {
        editingCategory.value = null
        form.value = { name: '', code: '', type, description: '' }
        showModal.value = true
    }

    function openEdit(category: ExpenseCategory) {
        editingCategory.value = category
        form.value = {
            name: category.name,
            code: category.code,
            type: category.type,
            description: category.description || ''
        }
        showModal.value = true
    }

    async function saveCategory() {
        if (!form.value.name.trim()) {
            await swal.warning('Vui lòng nhập tên danh mục!')
            return
        }
        if (!form.value.code.trim()) {
            await swal.warning('Vui lòng nhập mã danh mục!')
            return
        }

        isSaving.value = true
        try {
            if (editingCategory.value) {
                await expenseCategoryService.update(editingCategory.value.id, form.value)
                await swal.success('Cập nhật danh mục thành công!')
            } else {
                await expenseCategoryService.create(form.value)
                await swal.success('Tạo danh mục thành công!')
            }
            showModal.value = false
            await fetchCategories()
        } catch (error: any) {
            await swal.error(error.response?.data?.message || 'Lỗi khi lưu danh mục!')
        } finally {
            isSaving.value = false
        }
    }

    async function deleteCategory(category: ExpenseCategory) {
        const confirmed = await swal.confirm(`Xóa danh mục "${category.name}"?`)
        if (!confirmed) return

        try {
            await expenseCategoryService.delete(category.id)
            await swal.success('Đã xóa danh mục!')
            await fetchCategories()
        } catch (error: any) {
            await swal.error(error.response?.data?.message || 'Không thể xóa danh mục!')
        }
    }

    function setTypeFilter(type: 'all' | 'expense' | 'income') {
        typeFilter.value = type
    }

    onMounted(fetchCategories)

    return {
        categories,
        filteredCategories,
        expenseCategories,
        incomeCategories,
        isLoading,
        isSaving,
        typeFilter,
        showModal,
        editingCategory,
        form,
        fetchCategories,
        openCreate,
        openEdit,
        saveCategory,
        deleteCategory,
        setTypeFilter
    }
}
