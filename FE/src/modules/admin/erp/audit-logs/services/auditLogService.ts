/**
 * Audit Log Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { AuditLog } from '../models/auditLog'

const BASE_URL = '/admin/audit-logs'

export const auditLogService = {
    async getAll(params?: Record<string, any>) {
        const response = await httpClient.get(BASE_URL, { params })
        const data = (response.data as any).data
        return {
            items: data.items,
            meta: data.meta
        } as { items: AuditLog[]; meta: { current_page: number; last_page: number; total: number } }
    },

    async getById(id: number) {
        const response = await httpClient.get(`${BASE_URL}/${id}`)
        return (response.data as any).data as AuditLog
    }
}

export default auditLogService
