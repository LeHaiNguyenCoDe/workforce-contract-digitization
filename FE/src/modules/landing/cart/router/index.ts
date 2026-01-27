/**
 * Cart Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'cart',
        redirect: '/profile/cart'
    }
]

export default routes
