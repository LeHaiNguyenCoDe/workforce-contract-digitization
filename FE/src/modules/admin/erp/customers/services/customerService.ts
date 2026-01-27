/**
 * Customer Service
 */
import { adminUserService } from '@/plugins/api/services'
import type { Customer } from '../models/customer'

export const customerService = {
    async getAll(params?: Record<string, any>) {
        const response = await adminUserService.getAll(params)
        // Filter only customers
        const rawData = response.items || (Array.isArray(response) ? response : [])
        const customers = rawData.filter((u: any) => u.role === 'customer' || !u.role)
        
        return {
            data: customers as Customer[],
            current_page: response.meta?.current_page || 1,
            last_page: response.meta?.last_page || 1,
            total: response.meta?.total || customers.length
        }
    },

    async getById(id: number) {
        return await adminUserService.getById(id) as Customer
    }
}

export default customerService
