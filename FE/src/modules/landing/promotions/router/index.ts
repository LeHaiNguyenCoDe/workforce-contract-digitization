/**
 * Promotions Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'promotions',
        name: 'promotions',
        component: () => import('../views/PromotionListView.vue'),
        meta: { title: 'Khuyến mãi' }
    },
    {
        path: 'promotions/:id',
        name: 'promotion-detail',
        component: () => import('../views/PromotionDetailView.vue'),
        meta: { title: 'Chi tiết khuyến mãi' }
    }
]

export default routes
