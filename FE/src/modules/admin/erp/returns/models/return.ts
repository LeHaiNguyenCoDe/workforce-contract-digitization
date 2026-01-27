/**
 * Return Model - TypeScript interfaces
 */

export interface ReturnItem {
    id: number
    return_id: number
    order_item_id: number
    product_id: number
    quantity: number
    received_quantity: number
    condition: 'good' | 'damaged' | 'defective'
    product?: any
}

export interface Return {
    id: number
    order_id: number
    customer_id: number
    status: ReturnStatus
    reason: string
    notes?: string
    refund_amount?: number
    refund_method?: 'original' | 'store_credit' | 'manual'
    created_at: string
    updated_at: string
    order?: any
    customer?: any
    items?: ReturnItem[]
}

export type ReturnStatus = 'pending' | 'approved' | 'rejected' | 'receiving' | 'completed' | 'cancelled'

export interface ReturnFilters {
    status?: ReturnStatus | ''
    search?: string
}

export interface CreateReturnPayload {
    order_id: number
    reason: string
    items?: Array<{
        order_item_id: number
        quantity: number
    }>
    notes?: string
}
