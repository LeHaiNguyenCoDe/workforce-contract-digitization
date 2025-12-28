/**
 * Audit Logs Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'settings/audit-logs',
        name: 'admin-settings-audit-logs',
        component: () => import('../views/management/List.vue'),
        meta: { title: 'Nhật ký hoạt động' }
    }
]

export default routes
