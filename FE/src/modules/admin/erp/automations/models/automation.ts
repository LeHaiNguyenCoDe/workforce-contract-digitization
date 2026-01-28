/**
 * Automation Model
 */
export interface Automation {
    id: number
    name: string
    trigger: 'order_placed' | 'order_completed' | 'order_cancelled' | 'customer_registered' | 'cart_abandoned' | 'product_viewed' | 'review_submitted'
    action: 'email' | 'sms' | 'notification' | 'webhook'
    is_active: boolean
    conditions?: Record<string, any>
    config?: Record<string, any>
    created_at: string
}

export const triggerLabels: Record<string, string> = {
    order_placed: 'Đặt hàng mới',
    order_completed: 'Hoàn thành đơn',
    order_cancelled: 'Hủy đơn hàng',
    customer_registered: 'Khách đăng ký',
    cart_abandoned: 'Bỏ giỏ hàng',
    product_viewed: 'Xem sản phẩm',
    review_submitted: 'Gửi đánh giá'
}

export const actionLabels: Record<string, string> = {
    email: 'Gửi Email',
    sms: 'Gửi SMS',
    notification: 'Thông báo',
    webhook: 'Webhook'
}
