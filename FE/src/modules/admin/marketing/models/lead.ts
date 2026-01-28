/**
 * Lead Management Models
 */

export interface Lead {
    id: number
    full_name: string
    email: string | null
    phone: string | null
    company: string | null
    source: LeadSource
    source_detail: string | null
    score: number
    status: LeadStatus
    temperature: LeadTemperature
    estimated_value: number
    notes: string | null
    assigned_to: number | null
    converted_at: string | null
    converted_user_id: number | null
    converted_order_id: number | null
    metadata: Record<string, any> | null
    created_at: string
    updated_at: string
    assigned_user?: User
    activities?: LeadActivity[]
}

export interface LeadActivity {
    id: number
    lead_id: number
    user_id: number | null
    type: string
    description: string | null
    metadata: Record<string, any> | null
    created_at: string
}

export interface User {
    id: number
    name: string
    email: string
}

export type LeadSource =
    | 'website'
    | 'facebook'
    | 'google'
    | 'instagram'
    | 'tiktok'
    | 'referral'
    | 'event'
    | 'cold_call'
    | 'landing_page'
    | 'other'

export type LeadStatus =
    | 'new'
    | 'contacted'
    | 'qualified'
    | 'proposal'
    | 'negotiation'
    | 'converted'
    | 'lost'
    | 'disqualified'

export type LeadTemperature = 'cold' | 'warm' | 'hot'

export interface LeadStats {
    total_leads: number
    by_status: Record<LeadStatus, number>
    by_temperature: Record<LeadTemperature, number>
    by_source: Record<LeadSource, number>
    conversion_rate: number
    total_value: number
    avg_score: number
}

export interface LeadFilters {
    search?: string
    status?: LeadStatus | ''
    temperature?: LeadTemperature | ''
    source?: LeadSource | ''
    assigned_to?: number
    min_score?: number
}

export interface LeadForm {
    full_name: string
    email: string
    phone: string
    company: string
    source: LeadSource | ''
    notes: string
    estimated_value?: number
}

export interface PaginatedResponse<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}
