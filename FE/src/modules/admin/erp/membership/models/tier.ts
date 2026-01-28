/**
 * Membership Model
 */
export interface MembershipTier {
    id: number
    name: string
    code: string
    min_points: number
    max_points: number | null
    discount_percent: number
    point_multiplier: number
    color: string
    benefits: string[]
    member_count?: number
    created_at: string
    updated_at: string
}

export interface CreateTierPayload {
    name: string
    code: string
    min_points: number
    max_points: number | null
    discount_percent: number
    point_multiplier: number
    color: string
    benefits: string[]
}
