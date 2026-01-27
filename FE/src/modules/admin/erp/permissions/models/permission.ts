/**
 * Permission Model
 */
export interface Role {
    id: number
    name: string
    display_name: string
    permissions: string[]
}

export interface Permission {
    name: string
    display_name: string
    group: string
}

export const permissionGroups = [
    // Dashboard
    { name: 'dashboard', label: 'Dashboard', icon: '📊', permissions: ['view_dashboard'] },
    
    // Bán hàng
    { name: 'orders', label: 'Đơn hàng', icon: '🛒', permissions: ['view_orders', 'create_orders', 'edit_orders', 'cancel_orders', 'delete_orders'] },
    { name: 'returns', label: 'Trả hàng/RMA', icon: '↩️', permissions: ['view_returns', 'create_returns', 'edit_returns', 'approve_returns'] },
    { name: 'customers', label: 'Khách hàng', icon: '👥', permissions: ['view_customers', 'create_customers', 'edit_customers', 'delete_customers'] },
    
    // Kho
    { name: 'products', label: 'Sản phẩm', icon: '📦', permissions: ['view_products', 'create_products', 'edit_products', 'delete_products'] },
    { name: 'categories', label: 'Danh mục', icon: '📁', permissions: ['view_categories', 'create_categories', 'edit_categories', 'delete_categories'] },
    { name: 'warehouse', label: 'Quản lý kho', icon: '🏭', permissions: ['view_warehouse', 'create_inbound', 'create_outbound', 'adjust_stock', 'transfer_stock'] },
    
    // Mua hàng
    { name: 'suppliers', label: 'Nhà cung cấp', icon: '🚚', permissions: ['view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers'] },
    
    // Tài chính
    { name: 'finance', label: 'Tài chính', icon: '💰', permissions: ['view_finance', 'create_transactions', 'edit_transactions', 'manage_funds'] },
    { name: 'receivables', label: 'Phải thu', icon: '📥', permissions: ['view_receivables', 'collect_receivables', 'write_off_receivables'] },
    { name: 'payables', label: 'Phải trả', icon: '📤', permissions: ['view_payables', 'pay_payables'] },
    
    // Marketing
    { name: 'membership', label: 'Hạng thành viên', icon: '⭐', permissions: ['view_membership', 'edit_membership'] },
    { name: 'promotions', label: 'Khuyến mãi', icon: '🎁', permissions: ['view_promotions', 'create_promotions', 'edit_promotions', 'delete_promotions'] },
    { name: 'points', label: 'Điểm thưởng', icon: '🎯', permissions: ['view_points', 'manage_points'] },
    
    // Nội dung
    { name: 'articles', label: 'Bài viết', icon: '📝', permissions: ['view_articles', 'create_articles', 'edit_articles', 'delete_articles'] },
    
    // Báo cáo
    { name: 'reports', label: 'Báo cáo', icon: '📈', permissions: ['view_reports', 'export_reports'] },
    
    // Cấu hình
    { name: 'users', label: 'Nhân sự', icon: '👤', permissions: ['view_users', 'create_users', 'edit_users', 'delete_users'] },
    { name: 'permissions', label: 'Phân quyền', icon: '🔐', permissions: ['view_permissions', 'edit_permissions'] },
    { name: 'warehouses', label: 'Chi nhánh/Kho', icon: '🏠', permissions: ['view_warehouses', 'create_warehouses', 'edit_warehouses'] },
    { name: 'settings', label: 'Cài đặt hệ thống', icon: '⚙️', permissions: ['view_settings', 'edit_settings'] },
    { name: 'audit', label: 'Nhật ký hệ thống', icon: '📋', permissions: ['view_audit_logs'] },
]
