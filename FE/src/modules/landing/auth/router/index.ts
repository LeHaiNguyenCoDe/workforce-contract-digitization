/**
 * Auth Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'login',
        name: 'login',
        component: () => import('../views/LoginView.vue'),
        meta: { title: 'Đăng nhập', guest: true }
    },
    {
        path: 'register',
        name: 'register',
        component: () => import('../views/RegisterView.vue'),
        meta: { title: 'Đăng ký', guest: true }
    }
]

export default routes
