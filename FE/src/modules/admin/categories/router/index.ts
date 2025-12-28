import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'categories',
        name: 'admin-categories',
        component: () => import('../views/management/CategoryListView.vue'),
        meta: { layout: 'admin', title: 'Danh mục' }
    }
]

export default routes
