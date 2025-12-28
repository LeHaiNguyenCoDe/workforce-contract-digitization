/**
 * Membership Service
 */
import httpClient from '@/plugins/api/httpClient'
import type { MembershipTier, CreateTierPayload } from '../models/tier'

const BASE_URL = '/admin/membership/tiers'

export const tierService = {
    async getAll() {
        const response = await httpClient.get(BASE_URL)
        // API returns { status: 'success', data: [...] }
        const tiers = response.data?.data || response.data
        if (!tiers || !Array.isArray(tiers)) {
            throw new Error('Invalid tiers data received')
        }
        return tiers as MembershipTier[]
    },

    async create(payload: CreateTierPayload) {
        const response = await httpClient.post(BASE_URL, payload)
        return (response.data?.data || response.data) as MembershipTier
    },

    async update(id: number, payload: Partial<CreateTierPayload>) {
        const response = await httpClient.put(`${BASE_URL}/${id}`, payload)
        return (response.data?.data || response.data) as MembershipTier
    },

    async delete(id: number) {
        await httpClient.delete(`${BASE_URL}/${id}`)
    }
}

export default tierService
