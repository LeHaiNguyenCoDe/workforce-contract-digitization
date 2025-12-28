/**
 * Permissions Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'settings/permissions',
        name: 'admin-settings-permissions',
        component: () => import('../views/management/List.vue'),
        meta: { title: 'Phân quyền' }
    }
]

export default routes
