/**
 * Points Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { PointTransaction, CustomerPoints } from '../models/point'

export const pointsService = {
    async getCustomerPoints(customerId: number) {
        const response = await httpClient.get(`/admin/customers/${customerId}/points`)
        return response.data as CustomerPoints
    },

    async getTransactions(customerId: number, params?: Record<string, any>) {
        const response = await httpClient.get(`/admin/customers/${customerId}/points/transactions`, { params })
        return response.data as { data: PointTransaction[]; current_page: number; last_page: number }
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
