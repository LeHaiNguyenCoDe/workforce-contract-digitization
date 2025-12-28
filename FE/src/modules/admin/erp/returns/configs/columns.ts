/**
 * Return Table Columns Config
 */
export const returnColumns = [
    { key: 'id', label: 'Mã RMA', width: '100px' },
    { key: 'order_id', label: 'Đơn hàng', width: '100px' },
    { key: 'customer', label: 'Khách hàng' },
    { key: 'reason', label: 'Lý do' },
    { key: 'refund_amount', label: 'Hoàn tiền', width: '120px', align: 'right' as const },
    { key: 'status', label: 'Trạng thái', width: '120px' },
    { key: 'created_at', label: 'Ngày tạo', width: '150px' }
]

/**
 * Return Status Options
 */
export const returnStatusOptions = [
    { value: '', label: 'Tất cả' },
    { value: 'pending', label: 'Chờ duyệt' },
    { value: 'approved', label: 'Đã duyệt' },
    { value: 'rejected', label: 'Từ chối' },
    { value: 'receiving', label: 'Đang nhận hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

/**
 * Return Reason Options
 */
export const returnReasonOptions = [
    { value: 'Sản phẩm bị lỗi', label: 'Sản phẩm bị lỗi' },
    { value: 'Không đúng mô tả', label: 'Không đúng mô tả' },
    { value: 'Đổi size/màu', label: 'Đổi size/màu' },
    { value: 'Khách đổi ý', label: 'Khách đổi ý' },
    { value: 'Khác', label: 'Khác' }
]

/**
 * Status Labels & Classes
 */
export const returnStatusLabels: Record<string, string> = {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    rejected: 'Từ chối',
    receiving: 'Đang nhận',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
}

export const returnStatusClasses: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    approved: 'bg-info/10 text-info',
    rejected: 'bg-error/10 text-error',
    receiving: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-slate-500/10 text-slate-400'
}
