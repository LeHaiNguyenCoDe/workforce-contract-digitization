/**
 * Points Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'marketing/points',
        name: 'admin-marketing-points',
        component: () => import('../views/management/List.vue'),
        meta: { title: 'Điểm thưởng' }
    }
]

export default routes
