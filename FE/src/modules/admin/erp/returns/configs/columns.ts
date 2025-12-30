import { useI18n } from 'vue-i18n'

/**
 * Return Table Columns Config with Translation Keys
 */
export const returnColumnKeys = [
    { key: 'id', labelKey: 'admin.rmaCode', width: '100px' },
    { key: 'order_id', labelKey: 'admin.orderNumber', width: '100px' },
    { key: 'customer', labelKey: 'admin.customer' },
    { key: 'reason', labelKey: 'admin.reason' },
    { key: 'refund_amount', labelKey: 'admin.refundAmount', width: '120px', align: 'right' as const },
    { key: 'status', labelKey: 'common.status', width: '120px' },
    { key: 'created_at', labelKey: 'admin.createdDate', width: '150px' }
]

/**
 * Return Status Options with Translation Keys
 */
export const returnStatusOptionKeys = [
    { value: '', labelKey: 'common.all' },
    { value: 'pending', labelKey: 'admin.statusPending' },
    { value: 'approved', labelKey: 'admin.statusApproved' },
    { value: 'rejected', labelKey: 'admin.statusRejected' },
    { value: 'receiving', labelKey: 'admin.statusReceiving' },
    { value: 'completed', labelKey: 'admin.statusCompleted' },
    { value: 'cancelled', labelKey: 'admin.statusCancelled' }
]

/**
 * Return Reason Options with Translation Keys
 */
export const returnReasonOptionKeys = [
    { value: 'Sản phẩm bị lỗi', labelKey: 'admin.reasonDefective' },
    { value: 'Không đúng mô tả', labelKey: 'admin.reasonWrongDescription' },
    { value: 'Đổi size/màu', labelKey: 'admin.reasonChangeSize' },
    { value: 'Khách đổi ý', labelKey: 'admin.reasonChangeMind' },
    { value: 'Khác', labelKey: 'admin.reasonOther' }
]

/**
 * Helpers to get translated configurations
 */
export function useReturnConfigs() {
    const { t } = useI18n()

    const columns = returnColumnKeys.map(col => ({
        ...col,
        label: t(col.labelKey)
    }))

    const statusOptions = returnStatusOptionKeys.map(opt => ({
        value: opt.value,
        label: t(opt.labelKey)
    }))

    const reasonOptions = returnReasonOptionKeys.map(opt => ({
        value: opt.value,
        label: t(opt.labelKey)
    }))

    const statusLabels: Record<string, string> = {
        pending: t('admin.statusPending'),
        approved: t('admin.statusApproved'),
        rejected: t('admin.statusRejected'),
        receiving: t('admin.statusReceiving'),
        completed: t('admin.statusCompleted'),
        cancelled: t('admin.statusCancelled')
    }

    return {
        columns,
        statusOptions,
        reasonOptions,
        statusLabels
    }
}

/**
 * Keep original exports for backward compatibility if needed, 
 * but they won't be reactive to language changes.
 * Recommended to use useReturnConfigs() in components.
 */
export const returnColumns = returnColumnKeys.map(col => ({ key: col.key, label: col.labelKey, width: col.width }))
export const returnStatusOptions = returnStatusOptionKeys.map(opt => ({ value: opt.value, label: opt.labelKey }))
export const returnReasonOptions = returnReasonOptionKeys.map(opt => ({ value: opt.value, label: opt.labelKey }))
export const returnStatusLabels: Record<string, string> = {
    pending: 'admin.statusPending',
    approved: 'admin.statusApproved',
    rejected: 'admin.statusRejected',
    receiving: 'admin.statusReceiving',
    completed: 'admin.statusCompleted',
    cancelled: 'admin.statusCancelled'
}

export const returnStatusClasses: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    approved: 'bg-info/10 text-info',
    rejected: 'bg-error/10 text-error',
    receiving: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-slate-500/10 text-slate-400'
}

