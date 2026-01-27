import { useI18n } from 'vue-i18n'

// Order columns with translation keys
export const orderColumnKeys = [
    { key: 'order_number', labelKey: 'admin.orderCode', width: '120px' },
    { key: 'customer', labelKey: 'admin.customer' },
    { key: 'total', labelKey: 'admin.totalAmount' },
    { key: 'stock', labelKey: 'admin.warehouse' },
    { key: 'status', labelKey: 'common.status' },
    { key: 'created_at', labelKey: 'admin.orderDate' }
]

// Helper to get translated columns
export function useOrderColumns() {
    const { t } = useI18n()
    return orderColumnKeys.map(col => ({
        ...col,
        label: t(col.labelKey)
    }))
}

// Keep original for backward compatibility
export const orderColumns = [
    { key: 'order_number', label: 'Mã đơn', width: '120px' },
    { key: 'customer', label: 'Khách hàng' },
    { key: 'total', label: 'Tổng tiền' },
    { key: 'stock', label: 'Kho hàng' },
    { key: 'status', label: 'Trạng thái' },
    { key: 'created_at', label: 'Ngày đặt' }
]

