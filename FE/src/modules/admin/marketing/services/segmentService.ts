/**
 * Segment Service - API calls for customer segmentation
 */
import httpClient from '@/plugins/api/httpClient'
import type { CustomerSegment, SegmentStats, SegmentFilters, SegmentForm, SegmentCustomer, SegmentCondition, PaginatedResponse } from '../models'

export const segmentService = {
    /**
     * Get all segments with filters and pagination
     */
    async getAll(filters?: SegmentFilters, page = 1, perPage = 20) {
        const params: Record<string, any> = {
            page,
            per_page: perPage,
            _t: Date.now()
        }

        if (filters) {
            if (filters.search) params.search = filters.search
            if (filters.type) params.type = filters.type
            if (filters.is_active !== undefined) params.is_active = filters.is_active
        }

        const response = await httpClient.get('/marketing/segments', { params })
        const data = (response.data as any).data

        return {
            data: (data.data || data) as CustomerSegment[],
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || perPage,
            total: data.total || 0
        } as PaginatedResponse<CustomerSegment>
    },

    /**
     * Get single segment by ID
     */
    async getById(id: number) {
        const response = await httpClient.get(`/marketing/segments/${id}`)
        return (response.data as any).data as CustomerSegment
    },

    /**
     * Create new segment
     */
    async create(data: SegmentForm) {
        const payload: any = {
            name: data.name,
            description: data.description,
            type: data.type,
            color: data.color
        }

        // Parse conditions if provided
        if (data.conditions) {
            try {
                payload.conditions = JSON.parse(data.conditions)
            } catch {
                payload.conditions = null
            }
        }

        const response = await httpClient.post('/marketing/segments', payload)
        return (response.data as any).data as CustomerSegment
    },

    /**
     * Update existing segment
     */
    async update(id: number, data: Partial<SegmentForm>) {
        const payload: any = { ...data }

        // Parse conditions if provided as string
        if (typeof data.conditions === 'string' && data.conditions) {
            try {
                payload.conditions = JSON.parse(data.conditions)
            } catch {
                payload.conditions = null
            }
        }

        const response = await httpClient.put(`/marketing/segments/${id}`, payload)
        return (response.data as any).data as CustomerSegment
    },

    /**
     * Delete segment
     */
    async delete(id: number) {
        await httpClient.delete(`/marketing/segments/${id}`)
    },

    /**
     * Get customers in segment
     */
    async getCustomers(segmentId: number, page = 1, perPage = 20) {
        const response = await httpClient.get(`/marketing/segments/${segmentId}/customers`, {
            params: { page, per_page: perPage, _t: Date.now() }
        })
        const data = (response.data as any).data

        return {
            data: (data.data || data) as SegmentCustomer[],
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || perPage,
            total: data.total || 0
        } as PaginatedResponse<SegmentCustomer>
    },

    /**
     * Add customers to static segment
     */
    async addCustomers(segmentId: number, customerIds: number[]) {
        const response = await httpClient.post(`/marketing/segments/${segmentId}/customers/add`, {
            customer_ids: customerIds
        })
        return (response.data as any).data
    },

    /**
     * Remove customers from static segment
     */
    async removeCustomers(segmentId: number, customerIds: number[]) {
        const response = await httpClient.post(`/marketing/segments/${segmentId}/customers/remove`, {
            customer_ids: customerIds
        })
        return (response.data as any).data
    },

    /**
     * Get segments for a specific customer
     */
    async getCustomerSegments(userId: number) {
        const response = await httpClient.get(`/marketing/segments/customer/${userId}`)
        return (response.data as any).data as CustomerSegment[]
    },

    /**
     * Preview segment conditions (see which customers would match)
     */
    async preview(conditions: SegmentCondition[]) {
        const response = await httpClient.post('/marketing/segments/preview', { conditions })
        return (response.data as any).data
    },

    /**
     * Recalculate dynamic segment membership
     */
    async calculate(segmentId: number) {
        const response = await httpClient.post(`/marketing/segments/${segmentId}/calculate`)
        return (response.data as any).data
    },

    /**
     * Recalculate all dynamic segments
     */
    async recalculateAll() {
        const response = await httpClient.post('/marketing/segments/recalculate-all')
        return (response.data as any).data
    },

    /**
     * Get segment statistics
     */
    async getStats() {
        const response = await httpClient.get('/marketing/analytics/segments', {
            params: { _t: Date.now() }
        })
        return (response.data as any).data as SegmentStats
    },

    /**
     * Get single segment stats
     */
    async getSegmentStats(segmentId: number) {
        const response = await httpClient.get(`/marketing/segments/${segmentId}/stats`)
        return (response.data as any).data
    }
}

export default segmentService
