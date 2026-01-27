/**
 * Review Model
 */
export interface Review {
    id: number
    product_id: number
    product_name?: string
    customer_id: number
    customer_name?: string
    rating: number
    comment: string
    is_approved: boolean
    created_at: string
}

export const reviewColumns = [
    { key: 'id', label: 'ID', width: '80px' },
    { key: 'product_name', label: 'Sản phẩm' },
    { key: 'customer_name', label: 'Khách hàng' },
    { key: 'rating', label: 'Đánh giá', width: '120px', align: 'center' as const },
    { key: 'is_approved', label: 'Trạng thái', width: '100px' },
    { key: 'created_at', label: 'Ngày', width: '140px' }
]
