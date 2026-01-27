/**
 * usePermissions Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal } from '@/utils'
import type { Role } from '../models/permission'
import { permissionGroups } from '../models/permission'

const getMockRoles = (): Role[] => [
    { 
        id: 1, 
        name: 'admin', 
        display_name: 'Administrator', 
        permissions: [
            // All permissions
            'view_dashboard',
            'view_orders', 'create_orders', 'edit_orders', 'cancel_orders', 'delete_orders',
            'view_returns', 'create_returns', 'edit_returns', 'approve_returns',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_warehouse', 'create_inbound', 'create_outbound', 'adjust_stock', 'transfer_stock',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_finance', 'create_transactions', 'edit_transactions', 'manage_funds',
            'view_receivables', 'collect_receivables', 'write_off_receivables',
            'view_payables', 'pay_payables',
            'view_membership', 'edit_membership',
            'view_promotions', 'create_promotions', 'edit_promotions', 'delete_promotions',
            'view_points', 'manage_points',
            'view_articles', 'create_articles', 'edit_articles', 'delete_articles',
            'view_reports', 'export_reports',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_permissions', 'edit_permissions',
            'view_warehouses', 'create_warehouses', 'edit_warehouses',
            'view_settings', 'edit_settings',
            'view_audit_logs'
        ] 
    },
    { 
        id: 2, 
        name: 'manager', 
        display_name: 'Quản lý', 
        permissions: [
            'view_dashboard',
            'view_orders', 'edit_orders', 'cancel_orders',
            'view_returns', 'edit_returns', 'approve_returns',
            'view_customers', 'edit_customers',
            'view_products', 'edit_products',
            'view_categories',
            'view_warehouse', 'create_inbound', 'create_outbound',
            'view_suppliers',
            'view_finance', 'create_transactions',
            'view_receivables', 'collect_receivables',
            'view_payables', 'pay_payables',
            'view_membership',
            'view_promotions', 'edit_promotions',
            'view_points',
            'view_articles', 'edit_articles',
            'view_reports', 'export_reports',
            'view_users'
        ] 
    },
    { 
        id: 3, 
        name: 'staff', 
        display_name: 'Nhân viên', 
        permissions: [
            'view_dashboard',
            'view_orders',
            'view_customers',
            'view_products',
            'view_categories',
            'view_warehouse'
        ] 
    },
    { 
        id: 4, 
        name: 'warehouse', 
        display_name: 'Nhân viên kho', 
        permissions: [
            'view_dashboard',
            'view_products',
            'view_categories',
            'view_warehouse', 'create_inbound', 'create_outbound', 'adjust_stock',
            'view_suppliers'
        ] 
    }
]

export function usePermissions() {
    const swal = useSwal()

    const roles = ref<Role[]>([])
    const selectedRole = ref<Role | null>(null)
    const isLoading = ref(false)
    const isSaving = ref(false)

    async function fetchRoles() {
        isLoading.value = true
        try {
            await new Promise(r => setTimeout(r, 500))
            roles.value = getMockRoles()
            if (roles.value.length) selectedRole.value = roles.value[0]
        } finally {
            isLoading.value = false
        }
    }

    function selectRole(role: Role) {
        selectedRole.value = role
    }

    function hasPermission(permission: string): boolean {
        return selectedRole.value?.permissions.includes(permission) || false
    }

    function togglePermission(permission: string) {
        if (!selectedRole.value) return
        const idx = selectedRole.value.permissions.indexOf(permission)
        if (idx > -1) {
            selectedRole.value.permissions.splice(idx, 1)
        } else {
            selectedRole.value.permissions.push(permission)
        }
    }

    async function savePermissions() {
        isSaving.value = true
        try {
            await new Promise(r => setTimeout(r, 500))
            await swal.success('Đã lưu quyền!')
        } finally {
            isSaving.value = false
        }
    }

    onMounted(fetchRoles)

    return { roles, selectedRole, isLoading, isSaving, permissionGroups, fetchRoles, selectRole, hasPermission, togglePermission, savePermissions }
}
