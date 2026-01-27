/**
 * AuditLog Model
 */
export interface AuditLog {
    id: number
    user_id: number
    user_name: string
    action: 'create' | 'update' | 'delete' | 'login' | 'logout' | 'export'
    entity_type: string
    entity_id: number
    old_values?: Record<string, any>
    new_values?: Record<string, any>
    ip_address?: string
    user_agent?: string
    created_at: string
}

export const actionLabels: Record<string, string> = {
    create: 'Tạo mới',
    update: 'Cập nhật',
    delete: 'Xóa',
    login: 'Đăng nhập',
    logout: 'Đăng xuất',
    export: 'Xuất dữ liệu'
}

export const actionClasses: Record<string, string> = {
    create: 'bg-success/10 text-success',
    update: 'bg-info/10 text-info',
    delete: 'bg-error/10 text-error',
    login: 'bg-primary/10 text-primary',
    logout: 'bg-slate-500/10 text-slate-400',
    export: 'bg-warning/10 text-warning'
}
