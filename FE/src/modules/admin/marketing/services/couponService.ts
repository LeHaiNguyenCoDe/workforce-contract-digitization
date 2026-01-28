/**
 * Coupon Service - API calls for coupon management
 */
import httpClient from '@/plugins/api/httpClient'
import type { Coupon, CouponStats, CouponFilters, CouponForm, CouponValidation, PaginatedResponse } from '../models'

export const couponService = {
    /**
     * Get all coupons with filters and pagination
     */
    async getAll(filters?: CouponFilters, page = 1, perPage = 20) {
        const params: Record<string, any> = {
            page,
            per_page: perPage,
            _t: Date.now()
        }

        if (filters) {
            if (filters.search) params.search = filters.search
            if (filters.type) params.type = filters.type
            if (filters.status) params.status = filters.status
        }

        const response = await httpClient.get('/marketing/coupons', { params })
        const data = (response.data as any).data

        return {
            data: (data.data || data) as Coupon[],
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || perPage,
            total: data.total || 0
        } as PaginatedResponse<Coupon>
    },

    /**
     * Get single coupon by ID
     */
    async getById(id: number) {
        const response = await httpClient.get(`/marketing/coupons/${id}`)
        return (response.data as any).data as Coupon
    },

    /**
     * Create new coupon
     */
    async create(data: CouponForm) {
        const response = await httpClient.post('/marketing/coupons', data)
        return (response.data as any).data as Coupon
    },

    /**
     * Update existing coupon
     */
    async update(id: number, data: Partial<CouponForm>) {
        const response = await httpClient.put(`/marketing/coupons/${id}`, data)
        return (response.data as any).data as Coupon
    },

    /**
     * Delete coupon
     */
    async delete(id: number) {
        await httpClient.delete(`/marketing/coupons/${id}`)
    },

    /**
     * Validate coupon code
     */
    async validate(code: string, orderAmount?: number) {
        const response = await httpClient.post('/marketing/coupons/validate', {
            code,
            order_amount: orderAmount
        })
        return (response.data as any).data as CouponValidation
    },

    /**
     * Get coupon statistics
     */
    async getStats() {
        const response = await httpClient.get('/marketing/coupons/stats/overview', {
            params: { _t: Date.now() }
        })
        return (response.data as any).data as CouponStats
    },

    /**
     * Generate coupon codes in batch
     */
    async generateCodes(couponId: number, quantity: number, prefix?: string) {
        const response = await httpClient.post(`/marketing/coupons/${couponId}/generate-codes`, {
            coupon_id: couponId,
            quantity,
            prefix
        })
        return (response.data as any).data
    },

    /**
     * Export coupons
     */
    async export(filters?: { status?: string; type?: string; date_from?: string; date_to?: string }) {
        const response = await httpClient.get('/marketing/coupons/export', {
            params: filters,
            responseType: 'blob'
        })
        return response.data
    }
}

export default couponService
