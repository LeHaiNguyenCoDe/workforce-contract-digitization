import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'reviews',
        name: 'admin-reviews',
        component: () => import('../views/management/ReviewListView.vue'),
        meta: { layout: 'admin', title: 'Đánh giá' }
    }
]

export default routes
