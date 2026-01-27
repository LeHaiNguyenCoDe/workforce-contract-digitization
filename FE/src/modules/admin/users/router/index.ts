import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'users',
    name: 'admin-users',
    component: () => import('../views/management/UserListView.vue'),
    meta: { layout: 'admin', title: 'Người dùng' }
  }
]

// System group menu - includes Users, Permissions, Audit Logs, Settings
export const menu: MenuItem = {
  id: 'system',
  label: 'admin.system',
  icon: 'ri-settings-4-line',
  order: 90,
  children: [
    { id: 'users', label: 'admin.users', to: '/admin/users' },
    { id: 'permissions', label: 'admin.permissions', to: '/admin/settings/permissions' },
    { id: 'audit-logs', label: 'admin.auditLogs', to: '/admin/settings/audit-logs' },
    { id: 'settings', label: 'admin.settings', to: '/admin/settings' },
  ]
}

export default routes
