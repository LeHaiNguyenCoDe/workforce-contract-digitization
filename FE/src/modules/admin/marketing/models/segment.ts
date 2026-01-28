/**
 * Customer Segmentation Models
 */

export interface CustomerSegment {
    id: number
    name: string
    description: string | null
    type: SegmentType
    conditions: SegmentCondition[] | null
    color: string
    is_active: boolean
    auto_update: boolean
    customers_count: number
    created_by: number | null
    last_calculated_at: string | null
    created_at: string
    updated_at: string
}

export type SegmentType = 'static' | 'dynamic'

export interface SegmentCondition {
    field: string
    operator: 'equals' | 'not_equals' | 'greater_than' | 'less_than' | 'contains' | 'in' | 'not_in'
    value: any
}

export interface SegmentCustomer {
    id: number
    name: string
    email: string
    phone: string | null
    added_at: string
}

export interface SegmentStats {
    total_segments: number
    dynamic_segments: number
    static_segments: number
    average_segment_size: number
    largest_segment: {
        name: string
        size: number
    }
}

export interface SegmentFilters {
    search?: string
    type?: SegmentType | ''
    is_active?: boolean
}

export interface SegmentForm {
    name: string
    description: string
    type: SegmentType
    color: string
    conditions: string // JSON string for conditions
    is_active?: boolean
}
