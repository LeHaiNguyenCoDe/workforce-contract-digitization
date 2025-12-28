import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'promotions',
        name: 'admin-promotions',
        component: () => import('../views/management/PromotionListView.vue'),
        meta: { layout: 'admin', title: 'Khuyến mãi' }
    }
]

export default routes
