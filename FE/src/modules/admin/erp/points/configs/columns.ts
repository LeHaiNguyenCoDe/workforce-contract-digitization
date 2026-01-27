/**
 * Points configs
 */
export const transactionColumns = [
    { key: 'id', label: 'ID', width: '80px' },
    { key: 'type', label: 'Loại', width: '100px' },
    { key: 'amount', label: 'Số điểm', width: '100px', align: 'right' as const },
    { key: 'balance_after', label: 'Còn lại', width: '100px', align: 'right' as const },
    { key: 'description', label: 'Mô tả' },
    { key: 'created_at', label: 'Ngày', width: '150px' }
]

export const transactionTypeLabels: Record<string, string> = {
    earn: 'Tích điểm',
    redeem: 'Đổi điểm',
    adjust: 'Điều chỉnh',
    expire: 'Hết hạn'
}

export const transactionTypeClasses: Record<string, string> = {
    earn: 'bg-success/10 text-success',
    redeem: 'bg-error/10 text-error',
    adjust: 'bg-info/10 text-info',
    expire: 'bg-slate-500/10 text-slate-400'
}
