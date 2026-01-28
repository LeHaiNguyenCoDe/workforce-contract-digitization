/**
 * Permission Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { Role } from '../models/permission'

const BASE_URL = '/admin/roles'

export const permissionService = {
    async getAllRoles() {
        const response = await httpClient.get(BASE_URL)
        const data = (response.data as any).data
        return (data.items || data) as Role[]
    },

    async getRole(id: number) {
        const response = await httpClient.get(`${BASE_URL}/${id}`)
        return (response.data as any).data as Role
    },

    async updateRolePermissions(id: number, permissions: string[]) {
        const response = await httpClient.put(`${BASE_URL}/${id}/permissions`, { permissions })
        return (response.data as any).data
    },

    async createRole(data: Partial<Role>) {
        const response = await httpClient.post(BASE_URL, data)
        return (response.data as any).data as Role
    },

    async deleteRole(id: number) {
        const response = await httpClient.delete(`${BASE_URL}/${id}`)
        return (response.data as any).data
    }
}

export default permissionService
