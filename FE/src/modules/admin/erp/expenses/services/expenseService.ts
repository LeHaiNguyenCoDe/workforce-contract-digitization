/**
 * Expense Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { Expense, Category, Summary, CreateExpensePayload } from '../models/expense'

const BASE_URL = '/admin/expenses'

export const expenseService = {
    async getAll(params?: Record<string, any>) {
        const response = await httpClient.get(BASE_URL, { params })
        return (response.data as any).data || [] as Expense[]
    },

    async getCategories() {
        const response = await httpClient.get(`${BASE_URL}/categories`)
        return (response.data as any).data || [] as Category[]
    },

    async getSummary() {
        const response = await httpClient.get(`${BASE_URL}/summary`)
        return (response.data as any).data as Summary
    },

    async create(payload: CreateExpensePayload) {
        const response = await httpClient.post(BASE_URL, payload)
        return response.data as Expense
    },

    async update(id: number, payload: Partial<CreateExpensePayload>) {
        const response = await httpClient.put(`${BASE_URL}/${id}`, payload)
        return response.data as Expense
    },

    async delete(id: number) {
        await httpClient.delete(`${BASE_URL}/${id}`)
    }
}

export default expenseService
