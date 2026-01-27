/**
 * Loyalty Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'loyalty',
        name: 'loyalty',
        component: () => import('../views/LoyaltyView.vue'),
        meta: { title: 'Điểm thưởng', requiresAuth: true }
    }
]

export default routes
