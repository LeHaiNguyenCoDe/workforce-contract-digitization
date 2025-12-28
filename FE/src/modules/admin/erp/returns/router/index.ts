/**
 * Returns Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'returns',
        name: 'admin-returns',
        component: () => import('../views/management/List.vue'),
        meta: { title: 'Trả hàng / RMA' }
    }
]

export default routes
