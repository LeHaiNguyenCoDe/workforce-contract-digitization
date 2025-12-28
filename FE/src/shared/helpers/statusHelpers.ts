/**
 * Status helpers for Admin views
 */

// Return/RMA Status
export const returnStatusLabels: Record<string, string> = {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    rejected: 'Từ chối',
    receiving: 'Đang nhận hàng',
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

export function getReturnStatusLabel(status: string): string {
    return returnStatusLabels[status] || status
}

export function getReturnStatusClass(status: string): string {
    return returnStatusClasses[status] || 'bg-slate-500/10 text-slate-400'
}

// Order Status
export const orderStatusLabels: Record<string, string> = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    shipped: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy'
}

export const orderStatusClasses: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    processing: 'bg-info/10 text-info',
    shipped: 'bg-primary/10 text-primary',
    delivered: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error'
}

export function getOrderStatusLabel(status: string): string {
    return orderStatusLabels[status] || status
}

export function getOrderStatusClass(status: string): string {
    return orderStatusClasses[status] || 'bg-slate-500/10 text-slate-400'
}

// User Status
export const userStatusLabels: Record<string, string> = {
    active: 'Hoạt động',
    inactive: 'Không hoạt động',
    banned: 'Bị khóa'
}

export const userStatusClasses: Record<string, string> = {
    active: 'bg-success/10 text-success',
    inactive: 'bg-slate-500/10 text-slate-400',
    banned: 'bg-error/10 text-error'
}

// Point Transaction Type
export const pointTransactionLabels: Record<string, string> = {
    earn: 'Tích điểm',
    redeem: 'Đổi điểm',
    adjust: 'Điều chỉnh',
    expire: 'Hết hạn'
}

export const pointTransactionClasses: Record<string, string> = {
    earn: 'bg-success/10 text-success',
    redeem: 'bg-warning/10 text-warning',
    adjust: 'bg-info/10 text-info',
    expire: 'bg-error/10 text-error'
}

// Audit Log Actions
export const auditActionLabels: Record<string, string> = {
    create: 'Tạo mới',
    update: 'Cập nhật',
    delete: 'Xóa',
    login: 'Đăng nhập',
    logout: 'Đăng xuất'
}

export const auditActionClasses: Record<string, string> = {
    create: 'bg-success/10 text-success',
    update: 'bg-info/10 text-info',
    delete: 'bg-error/10 text-error',
    login: 'bg-primary/10 text-primary',
    logout: 'bg-slate-500/10 text-slate-400'
}
