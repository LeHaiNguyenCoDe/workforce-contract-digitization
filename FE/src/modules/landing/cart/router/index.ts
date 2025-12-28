/**
 * Cart Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'cart',
        name: 'cart',
        component: () => import('../views/CartView.vue'),
        meta: { title: 'Giỏ hàng' }
    }
]

export default routes
