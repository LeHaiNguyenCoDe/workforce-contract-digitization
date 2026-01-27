/**
 * Wishlist Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'wishlist',
        name: 'wishlist',
        component: () => import('../views/WishlistView.vue'),
        meta: { title: 'Yêu thích', requiresAuth: true }
    }
]

export default routes
