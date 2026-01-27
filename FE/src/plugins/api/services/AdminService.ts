import { BaseApiService } from './BaseApiService'
import httpClient from '../httpClient'
import type { ApiResponse } from '../types'

// Simple paginated response for local use
export interface SimplePaginatedResponse<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

// ==================== RETURNS / RMA ====================

export interface ReturnItem {
    id: number
    return_id: number
    order_item_id: number
    product_id: number
    quantity: number
    received_quantity: number
    condition: 'good' | 'damaged' | 'defective'
    product?: any
}

export interface Return {
    id: number
    order_id: number
    customer_id: number
    status: 'pending' | 'approved' | 'rejected' | 'receiving' | 'completed' | 'cancelled'
    reason: string
    notes?: string
    refund_amount?: number
    refund_method?: 'original' | 'store_credit' | 'manual'
    created_at: string
    updated_at: string
    order?: any
    customer?: any
    items?: ReturnItem[]
}

export interface CreateReturnRequest {
    order_id: number
    reason: string
    items: Array<{
        order_item_id: number
        quantity: number
    }>
    notes?: string
}

class ReturnService extends BaseApiService<Return> {
    protected readonly endpoint = 'admin/returns'

    /**
     * Get all returns with filters
     */
    async getReturns(params?: {
        status?: string
        search?: string
        page?: number
        per_page?: number
    }): Promise<SimplePaginatedResponse<Return>> {
        const searchParams = new URLSearchParams()
        if (params?.status) searchParams.set('status', params.status)
        if (params?.search) searchParams.set('search', params.search)
        if (params?.page) searchParams.set('page', String(params.page))
        if (params?.per_page) searchParams.set('per_page', String(params.per_page))

        const query = searchParams.toString()
        const url = `${this.endpoint}${query ? `?${query}` : ''}`

        const response = await httpClient.get<ApiResponse<SimplePaginatedResponse<Return>>>(url)
        return response.data.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
    }

    /**
     * Get single return
     */
    async getReturn(id: number): Promise<Return> {
        const response = await httpClient.get<ApiResponse<Return>>(`${this.endpoint}/${id}`)
        return response.data.data!
    }

    /**
     * Create return request
     */
    async createReturn(data: CreateReturnRequest): Promise<Return> {
        const response = await httpClient.post<ApiResponse<Return>>(this.endpoint, data)
        return response.data.data!
    }

    /**
     * Approve return
     */
    async approveReturn(id: number): Promise<Return> {
        const response = await httpClient.post<ApiResponse<Return>>(`${this.endpoint}/${id}/approve`)
        return response.data.data!
    }

    /**
     * Reject return
     */
    async rejectReturn(id: number, reason?: string): Promise<Return> {
        const response = await httpClient.post<ApiResponse<Return>>(`${this.endpoint}/${id}/reject`, { reason })
        return response.data.data!
    }

    /**
     * Receive items
     */
    async receiveItems(id: number, items: Array<{ return_item_id: number; received_quantity: number; condition: string }>): Promise<Return> {
        const response = await httpClient.put<ApiResponse<Return>>(`${this.endpoint}/${id}/receive`, { items })
        return response.data.data!
    }

    /**
     * Complete return
     */
    async completeReturn(id: number, refund_method?: string): Promise<Return> {
        const response = await httpClient.post<ApiResponse<Return>>(`${this.endpoint}/${id}/complete`, { refund_method })
        return response.data.data!
    }

    /**
     * Cancel return
     */
    async cancelReturn(id: number): Promise<Return> {
        const response = await httpClient.post<ApiResponse<Return>>(`${this.endpoint}/${id}/cancel`)
        return response.data.data!
    }
}

// ==================== MEMBERSHIP ====================

export interface MembershipTier {
    id: number
    name: string
    min_points: number
    discount_percent: number
    benefits?: string[]
    color?: string
    member_count?: number
    created_at: string
    updated_at: string
}

export interface CustomerMembership {
    id: number
    customer_id: number
    tier_id: number
    total_points: number
    available_points: number
    lifetime_points: number
    tier?: MembershipTier
    customer?: any
}

export interface PointTransaction {
    id: number
    customer_id: number
    type: 'earn' | 'redeem' | 'adjust' | 'expire'
    points: number
    balance_after: number
    description: string
    reference_type?: string
    reference_id?: number
    created_at: string
}

export interface CreateTierRequest {
    name: string
    min_points: number
    discount_percent: number
    benefits?: string[]
    color?: string
}

class MembershipService {
    protected readonly endpoint = 'admin/membership'

    /**
     * Get all tiers
     */
    async getTiers(): Promise<MembershipTier[]> {
        const response = await httpClient.get<ApiResponse<MembershipTier[]>>(`${this.endpoint}/tiers`)
        return response.data.data || []
    }

    /**
     * Create tier
     */
    async createTier(data: CreateTierRequest): Promise<MembershipTier> {
        const response = await httpClient.post<ApiResponse<MembershipTier>>(`${this.endpoint}/tiers`, data)
        return response.data.data!
    }

    /**
     * Update tier
     */
    async updateTier(id: number, data: Partial<CreateTierRequest>): Promise<MembershipTier> {
        const response = await httpClient.put<ApiResponse<MembershipTier>>(`${this.endpoint}/tiers/${id}`, data)
        return response.data.data!
    }

    /**
     * Delete tier
     */
    async deleteTier(id: number): Promise<void> {
        await httpClient.delete(`${this.endpoint}/tiers/${id}`)
    }

    /**
     * Get customer membership
     */
    async getCustomerMembership(customerId: number): Promise<CustomerMembership> {
        const response = await httpClient.get<ApiResponse<CustomerMembership>>(`${this.endpoint}/customers/${customerId}`)
        return response.data.data!
    }

    /**
     * Get customer point transactions
     */
    async getCustomerTransactions(customerId: number, params?: { page?: number; per_page?: number }): Promise<SimplePaginatedResponse<PointTransaction>> {
        const searchParams = new URLSearchParams()
        if (params?.page) searchParams.set('page', String(params.page))
        if (params?.per_page) searchParams.set('per_page', String(params.per_page))

        const query = searchParams.toString()
        const url = `${this.endpoint}/customers/${customerId}/transactions${query ? `?${query}` : ''}`

        const response = await httpClient.get<ApiResponse<SimplePaginatedResponse<PointTransaction>>>(url)
        return response.data.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
    }

    /**
     * Redeem points
     */
    async redeemPoints(customerId: number, points: number, description?: string): Promise<PointTransaction> {
        const response = await httpClient.post<ApiResponse<PointTransaction>>(
            `${this.endpoint}/customers/${customerId}/redeem`,
            { points, description }
        )
        return response.data.data!
    }

    /**
     * Calculate discount for cart
     */
    async calculateDiscount(data: { customer_id: number; cart_total: number }): Promise<{ discount: number; tier: MembershipTier }> {
        const response = await httpClient.post<ApiResponse<{ discount: number; tier: MembershipTier }>>(
            `${this.endpoint}/calculate-discount`,
            data
        )
        return response.data.data!
    }
}

// Export singleton instances
export const returnService = new ReturnService()
export const membershipService = new MembershipService()
