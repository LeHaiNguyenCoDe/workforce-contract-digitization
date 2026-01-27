/**
 * Customer Table Columns Config
 */
export const customerColumns = [
    { key: 'id', label: 'ID', width: '80px' },
    { key: 'name', label: 'Tên khách hàng' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Điện thoại', width: '130px' },
    { key: 'total_orders', label: 'Đơn hàng', width: '100px', align: 'center' as const },
    { key: 'total_spent', label: 'Tổng chi tiêu', width: '150px', align: 'right' as const },
    { key: 'active', label: 'Trạng thái', width: '100px' },
    { key: 'created_at', label: 'Ngày tạo', width: '120px' }
]
