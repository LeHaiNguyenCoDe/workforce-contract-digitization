/**
 * Expense Model
 */
export interface Category {
    id: number
    name: string
    code: string
    type: 'expense' | 'income'
}

export interface Expense {
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

export interface Summary {
    total_expense: number
    total_income: number
    net: number
    by_category: Array<{ category_id: number; total: number; category?: Category }>
}

export interface CreateExpensePayload {
    category_id: number | null
    type: 'expense' | 'income'
    amount: number
    expense_date: string
    payment_method?: string
    reference_number?: string
    description?: string
}
