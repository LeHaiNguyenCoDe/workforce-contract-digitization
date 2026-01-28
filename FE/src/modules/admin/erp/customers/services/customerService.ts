/**
 * Customer Service
 */
import { adminUserService } from '@/plugins/api/services'
import type { Customer } from '../models/customer'
import type { PaginatedData } from '@/plugins/api/types'

export const customerService = {
  async getAll(params?: Record<string, any>): Promise<PaginatedData<Customer>> {
    const response = await adminUserService.getAll(params)

    // Filter only customers if needed, but usually the backend should handles this
    // However, keeping the filter logic if the endpoint returns all users
    const items = response.items.filter((u: any) => u.role === 'customer' || !u.role)

    return {
      items: items as Customer[],
      meta: response.meta
    }
  },

  async getById(id: number): Promise<Customer> {
    return (await adminUserService.getById(id)) as Customer
  }
}

export default customerService
