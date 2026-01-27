/**
 * Orders Config
 */

export const orderStatusOptions = [
    { value: 'all', label: 'Tất cả' },
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'shipped', label: 'Đang giao' },
    { value: 'delivered', label: 'Đã giao' },
    { value: 'cancelled', label: 'Đã hủy' }
]

export const paymentMethods = [
    { value: 'cod', label: 'Thanh toán khi nhận hàng (COD)' },
    { value: 'bank_transfer', label: 'Chuyển khoản ngân hàng' },
    { value: 'momo', label: 'Ví MoMo' },
    { value: 'vnpay', label: 'VNPay' }
]
