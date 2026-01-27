/**
 * Home Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: '',
        name: 'home',
        component: () => import('../views/HomeView.vue'),
        meta: { title: 'Trang chủ' }
    }
]

export default routes
