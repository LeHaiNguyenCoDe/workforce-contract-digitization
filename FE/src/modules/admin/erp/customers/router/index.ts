/**
 * Customers Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'customers',
        name: 'admin-customers',
        component: () => import('../views/management/List.vue'),
        meta: { title: 'Khách hàng' }
    }
]

export default routes
