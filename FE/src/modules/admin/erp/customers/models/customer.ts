/**
 * Customer Model
 */
export interface Customer {
    id: number
    name: string
    email: string
    phone?: string
    address?: string
    role: string
    active: boolean
    total_orders?: number
    total_spent?: number
    created_at: string
    orders?: any[]
}

export interface CustomerFilters {
    search?: string
    active?: boolean
}
