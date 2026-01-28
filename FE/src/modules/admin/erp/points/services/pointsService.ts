/**
 * Points Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { PointTransaction, CustomerPoints } from '../models/point'

export const pointsService = {
    async getCustomerPoints(customerId: number) {
        const response = await httpClient.get(`/admin/customers/${customerId}/points`, {
            params: { _t: Date.now() } // Cache busting
        })
        return (response.data as any).data as CustomerPoints
    },

    async getTransactions(customerId: number, params?: Record<string, any>) {
        const response = await httpClient.get(`/admin/customers/${customerId}/points/transactions`, {
            params: { ...params, _t: Date.now() } // Cache busting
        })
        const data = (response.data as any).data
        return {
            data: (data.items || data) as PointTransaction[],
            current_page: data.meta?.current_page || 1,
            last_page: data.meta?.last_page || 1
        }
    },

    async adjustPoints(customerId: number, amount: number, description: string) {
        const response = await httpClient.post(`/admin/customers/${customerId}/points/adjust`, { amount, description })
        return response.data
    },

    async redeemPoints(customerId: number, amount: number, description?: string) {
        const response = await httpClient.post(`/admin/customers/${customerId}/points/redeem`, { amount, description })
        return response.data
    }
}

export default pointsService
