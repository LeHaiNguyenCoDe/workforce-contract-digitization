/**
 * Return Service - API calls
 */
import httpClient from '@/plugins/api/httpClient'
import type { Return, CreateReturnPayload } from '../models/return'

const BASE_URL = '/admin/returns'

export const erpReturnService = {
    async getAll(params?: Record<string, any>) {
        const response = await httpClient.get(BASE_URL, { params })
        return response.data as { data: Return[]; current_page: number; last_page: number; total: number }
    },

    async getById(id: number) {
        const response = await httpClient.get(`${BASE_URL}/${id}`)
        return response.data as Return
    },

    async create(payload: CreateReturnPayload) {
        const response = await httpClient.post(BASE_URL, payload)
        return response.data as Return
    },

    async approve(id: number) {
        const response = await httpClient.post(`${BASE_URL}/${id}/approve`)
        return response.data
    },

    async reject(id: number, reason?: string) {
        const response = await httpClient.post(`${BASE_URL}/${id}/reject`, { reason })
        return response.data
    },

    async receive(id: number, items?: any[]) {
        const response = await httpClient.post(`${BASE_URL}/${id}/receive`, { items })
        return response.data
    },

    async complete(id: number, refundMethod?: string) {
        const response = await httpClient.post(`${BASE_URL}/${id}/complete`, { refund_method: refundMethod })
        return response.data
    },

    async cancel(id: number) {
        const response = await httpClient.post(`${BASE_URL}/${id}/cancel`)
        return response.data
    }
}

export default erpReturnService
