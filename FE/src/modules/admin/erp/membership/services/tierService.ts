/**
 * Membership Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { MembershipTier, CreateTierPayload } from '../models/tier'

const BASE_URL = '/admin/membership/tiers'

export const tierService = {
    async getAll() {
        const response = await httpClient.get(BASE_URL)
        const data = (response.data as any).data
        const tiers = Array.isArray(data) ? data : (data.items || data)
        if (!Array.isArray(tiers)) {
            throw new Error('Invalid tiers data received')
        }
        return tiers as MembershipTier[]
    },

    async create(payload: CreateTierPayload) {
        const response = await httpClient.post(BASE_URL, payload)
        const data = (response.data as any).data
        return (data.data || data) as MembershipTier
    },

    async update(id: number, payload: Partial<CreateTierPayload>) {
        const response = await httpClient.put(`${BASE_URL}/${id}`, payload)
        const data = (response.data as any).data
        return (data.data || data) as MembershipTier
    },

    async delete(id: number) {
        await httpClient.delete(`${BASE_URL}/${id}`)
    }
}

export default tierService
