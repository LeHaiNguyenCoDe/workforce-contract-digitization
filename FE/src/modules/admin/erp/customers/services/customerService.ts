/**
 * Customer Service
 */
import { adminUserService } from '@/plugins/api/services'
import type { Customer } from '../models/customer'

export const customerService = {
    async getAll(params?: Record<string, any>) {
        const response = await adminUserService.getAll(params)
        // Filter only customers
        const customers = (response.data || response).filter((u: any) => u.role === 'customer' || !u.role)
        return {
            data: customers as Customer[],
            current_page: response.current_page || 1,
            last_page: response.last_page || 1,
            total: customers.length
        }
    },

    async getById(id: number) {
        return await adminUserService.getById(id) as Customer
    }
}

export default customerService
