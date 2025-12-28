/**
 * Automation Model
 */
export interface Automation {
    id: number
    name: string
    trigger: 'order_placed' | 'order_shipped' | 'customer_signup' | 'birthday' | 'inactive' | 'points_earned'
    action: 'send_email' | 'send_sms' | 'add_points' | 'apply_discount' | 'assign_tag'
    is_active: boolean
    conditions?: Record<string, any>
    config?: Record<string, any>
    created_at: string
}

export const triggerLabels: Record<string, string> = {
    order_placed: 'Đặt hàng mới',
    order_shipped: 'Đơn được giao',
    customer_signup: 'Khách đăng ký',
    birthday: 'Sinh nhật',
    inactive: 'Không hoạt động',
    points_earned: 'Tích điểm'
}

export const actionLabels: Record<string, string> = {
    send_email: 'Gửi Email',
    send_sms: 'Gửi SMS',
    add_points: 'Cộng điểm',
    apply_discount: 'Giảm giá',
    assign_tag: 'Gắn tag'
}
