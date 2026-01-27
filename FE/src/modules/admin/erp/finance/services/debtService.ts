import httpClient from '@/plugins/api/httpClient'

export interface AccountReceivable {
    id: number
    ar_code: string
    order_id: number
    order?: { id: number, code: string, total_amount: number }
    customer_id?: number
    customer?: { id: number, name: string, email?: string }
    total_amount: number
    paid_amount: number
    remaining_amount: number
    due_date?: string
    status: 'open' | 'partial' | 'paid' | 'overdue' | 'written_off'
    created_at: string
}

export interface AccountPayable {
    id: number
    ap_code: string
    supplier_id?: number
    supplier?: { id: number, name: string }
    reference_type?: string
    reference_id?: number
    total_amount: number
    paid_amount: number
    remaining_amount: number
    due_date?: string
    status: 'open' | 'partial' | 'paid' | 'overdue'
    created_at: string
}

export interface DebtSummary {
    total_receivable: number
    total_payable: number
    by_status: Array<{ status: string, count: number, total: number }>
}

export const debtService = {
    // Account Receivables
    async getReceivables(params?: Record<string, any>): Promise<{ data: AccountReceivable[], meta?: any }> {
        const response = await httpClient.get('/admin/debts/receivables', { params })
        const result = (response.data as any).data
        return { data: result?.items || [], meta: result?.meta }
    },

    async getReceivableSummary(): Promise<DebtSummary> {
        const response = await httpClient.get('/admin/debts/receivables/summary')
        return (response.data as any).data
    },

    // Account Payables
    async getPayables(params?: Record<string, any>): Promise<{ data: AccountPayable[], meta?: any }> {
        const response = await httpClient.get('/admin/debts/payables', { params })
        const result = (response.data as any).data
        return { data: result?.items || [], meta: result?.meta }
    },

    async getPayableSummary(): Promise<DebtSummary> {
        const response = await httpClient.get('/admin/debts/payables/summary')
        return (response.data as any).data
    },

    // Make payment on AR
    async payReceivable(arId: number, data: { amount: number, fund_id?: number }): Promise<any> {
        const response = await httpClient.post(`/admin/debts/receivables/${arId}/pay`, data)
        return (response.data as any).data
    },

    // Make payment on AP
    async payPayable(apId: number, data: { amount: number, fund_id?: number }): Promise<any> {
        const response = await httpClient.post(`/admin/debts/payables/${apId}/pay`, data)
        return (response.data as any).data
    }
}

export default debtService
