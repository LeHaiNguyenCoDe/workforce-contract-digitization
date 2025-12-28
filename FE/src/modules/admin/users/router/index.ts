import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'users',
        name: 'admin-users',
        component: () => import('../views/management/UserListView.vue'),
        meta: { layout: 'admin', title: 'Người dùng' }
    }
]

export default routes
