import httpClient from '@/plugins/api/httpClient'

export interface Fund {
    id: number
    name: string
    code: string
    type: 'cash' | 'bank' | 'other'
    balance: number
    is_default: boolean
    is_active: boolean
}

export interface FinanceTransaction {
    id: number
    transaction_code: string
    fund_id: number
    fund?: Fund
    type: 'receipt' | 'payment'
    amount: number
    balance_before: number
    balance_after: number
    transaction_date: string
    reference_type?: string
    reference_id?: number
    description?: string
    payment_method?: string
    status: 'pending' | 'approved' | 'rejected'
    created_at: string
}

export interface FinanceSummary {
    total_receipt: number
    total_payment: number
    net: number
    fund_balances: Fund[]
}

export const financeService = {
    // Funds
    async getFunds(): Promise<Fund[]> {
        const response = await httpClient.get('/admin/finance/funds')
        return (response.data as any).data || []
    },

    // Transactions
    async getTransactions(params?: Record<string, any>): Promise<{ data: FinanceTransaction[], meta?: any }> {
        const response = await httpClient.get('/admin/finance/transactions', { params })
        const result = response.data as any
        return { data: result.data || [], meta: result.meta }
    },

    async createTransaction(data: Partial<FinanceTransaction>): Promise<FinanceTransaction> {
        const response = await httpClient.post('/admin/finance/transactions', data)
        return (response.data as any).data
    },

    // Summary
    async getSummary(fromDate?: string, toDate?: string): Promise<FinanceSummary> {
        const params: Record<string, string> = {}
        if (fromDate) params.from_date = fromDate
        if (toDate) params.to_date = toDate
        const response = await httpClient.get('/admin/finance/summary', { params })
        return (response.data as any).data
    },

    // Order Payment Collection
    async collectOrderPayment(orderId: number, data: { amount: number, fund_id?: number, payment_method?: string }): Promise<FinanceTransaction> {
        const response = await httpClient.post(`/admin/orders/${orderId}/collect-payment`, data)
        return (response.data as any).data
    }
}

export default financeService
