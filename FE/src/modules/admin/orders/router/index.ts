import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('../views/management/OrderListView.vue'),
        meta: { layout: 'admin', title: 'Đơn hàng' }
    }
]

export default routes
