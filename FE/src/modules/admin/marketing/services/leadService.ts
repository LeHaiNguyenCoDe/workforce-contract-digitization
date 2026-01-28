/**
 * Lead Service - API calls for lead management
 */
import httpClient from '@/plugins/api/httpClient'
import type { Lead, LeadStats, LeadFilters, LeadForm, LeadActivity, PaginatedResponse } from '../models/lead'

export const leadService = {
    /**
     * Get all leads with filters and pagination
     */
    async getAll(filters?: LeadFilters, page = 1, perPage = 20) {
        const params: Record<string, any> = {
            page,
            per_page: perPage,
            _t: Date.now() // Cache busting
        }

        if (filters) {
            if (filters.search) params.search = filters.search
            if (filters.status) params.status = filters.status
            if (filters.temperature) params.temperature = filters.temperature
            if (filters.source) params.source = filters.source
            if (filters.assigned_to) params.assigned_to = filters.assigned_to
            if (filters.min_score) params.min_score = filters.min_score
        }

        const response = await httpClient.get('/marketing/leads', { params })
        const data = (response.data as any).data

        return {
            data: (data.data || data) as Lead[],
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || perPage,
            total: data.total || 0
        } as PaginatedResponse<Lead>
    },

    /**
     * Get single lead by ID
     */
    async getById(id: number) {
        const response = await httpClient.get(`/marketing/leads/${id}`)
        return (response.data as any).data as Lead
    },

    /**
     * Create new lead
     */
    async create(data: LeadForm) {
        const response = await httpClient.post('/marketing/leads', data)
        return (response.data as any).data as Lead
    },

    /**
     * Update existing lead
     */
    async update(id: number, data: Partial<LeadForm>) {
        const response = await httpClient.put(`/marketing/leads/${id}`, data)
        return (response.data as any).data as Lead
    },

    /**
     * Delete lead
     */
    async delete(id: number) {
        await httpClient.delete(`/marketing/leads/${id}`)
    },

    /**
     * Assign lead to user
     */
    async assign(leadId: number, userId: number) {
        const response = await httpClient.post(`/marketing/leads/${leadId}/assign`, { user_id: userId })
        return (response.data as any).data as Lead
    },

    /**
     * Convert lead to customer
     */
    async convert(leadId: number, userId: number, orderId?: number) {
        const response = await httpClient.post(`/marketing/leads/${leadId}/convert`, {
            user_id: userId,
            order_id: orderId
        })
        return (response.data as any).data as Lead
    },

    /**
     * Add activity to lead
     */
    async addActivity(leadId: number, type: string, description?: string, metadata?: Record<string, any>) {
        const response = await httpClient.post(`/marketing/leads/${leadId}/activities`, {
            type,
            description,
            metadata
        })
        return (response.data as any).data as LeadActivity
    },

    /**
     * Get lead statistics
     */
    async getStats(dateFrom?: string, dateTo?: string) {
        const params: Record<string, any> = { _t: Date.now() }
        if (dateFrom) params.date_from = dateFrom
        if (dateTo) params.date_to = dateTo

        const response = await httpClient.get('/marketing/leads/stats/overview', { params })
        return (response.data as any).data as LeadStats
    },

    /**
     * Get lead pipeline/funnel
     */
    async getPipeline() {
        const response = await httpClient.get('/marketing/leads/stats/pipeline')
        return (response.data as any).data
    },

    /**
     * Import leads from CSV file
     */
    async importFromCsv(file: File) {
        const formData = new FormData()
        formData.append('file', file)

        const response = await httpClient.post('/marketing/leads/import/bulk', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        return (response.data as any).data
    }
}

export default leadService
