/**
 * Marketplace Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: '',
        name: 'marketplace-home',
        component: () => import('../views/index.vue'),
        meta: { title: 'Marketplace' }
    }
]

export default routes
