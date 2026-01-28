/**
 * Automation Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { Automation } from '../models/automation'

const BASE_URL = '/admin/automations'

export const automationService = {
    async getAll() {
        const response = await httpClient.get(BASE_URL)
        const data = (response.data as any).data
        return (data.items || data) as Automation[]
    },

    async create(data: Partial<Automation>) {
        const response = await httpClient.post(BASE_URL, data)
        return (response.data as any).data as Automation
    },

    async update(id: number, data: Partial<Automation>) {
        const response = await httpClient.put(`${BASE_URL}/${id}`, data)
        return (response.data as any).data as Automation
    },

    async delete(id: number) {
        const response = await httpClient.delete(`${BASE_URL}/${id}`)
        return (response.data as any).data
    },

    async toggleActive(id: number) {
        const response = await httpClient.post(`${BASE_URL}/${id}/toggle`)
        return (response.data as any).data
    }
}

export default automationService
