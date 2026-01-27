/**
 * Expense configs
 */
export const expenseColumns = [
    { key: 'expense_code', label: 'Mã', width: '100px' },
    { key: 'type', label: 'Loại', width: '80px', align: 'center' as const },
    { key: 'category', label: 'Danh mục' },
    { key: 'amount', label: 'Số tiền', width: '150px', align: 'right' as const },
    { key: 'expense_date', label: 'Ngày', width: '120px' },
    { key: 'description', label: 'Mô tả' }
]

export const typeOptions = [
    { value: '', label: 'Tất cả' },
    { value: 'expense', label: 'Chi' },
    { value: 'income', label: 'Thu' }
]
