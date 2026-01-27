/**
 * Expense Category Service
 */
import httpClient from '@/plugins/api/httpClient'

export interface ExpenseCategory {
    id: number
    name: string
    code: string
    type: 'expense' | 'income'
    description: string | null
    is_active: boolean
    created_at: string
    updated_at: string
}

export interface CreateCategoryPayload {
    name: string
    code: string
    type: 'expense' | 'income'
    description?: string
}

const BASE_URL = '/admin/expenses/categories'

export const expenseCategoryService = {
    async getAll() {
        const response = await httpClient.get(BASE_URL)
        return (response.data as any).data || [] as ExpenseCategory[]
    },

    async create(payload: CreateCategoryPayload) {
        const response = await httpClient.post(BASE_URL, payload)
        return (response.data as any).data as ExpenseCategory
    },

    async update(id: number, payload: Partial<CreateCategoryPayload>) {
        const response = await httpClient.put(`${BASE_URL}/${id}`, payload)
        return (response.data as any).data as ExpenseCategory
    },

    async delete(id: number) {
        await httpClient.delete(`${BASE_URL}/${id}`)
    }
}

export default expenseCategoryService
