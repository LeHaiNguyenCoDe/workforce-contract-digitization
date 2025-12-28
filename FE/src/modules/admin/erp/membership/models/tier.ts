/**
 * Membership Model
 */
export interface MembershipTier {
    id: number
    name: string
    min_points: number
    discount_percent: number
    color: string
    benefits: string[]
    member_count?: number
    created_at: string
    updated_at: string
}

export interface CreateTierPayload {
    name: string
    min_points: number
    discount_percent: number
    color: string
    benefits: string[]
}
