import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'products',
        name: 'admin-products',
        component: () => import('../views/management/ProductListView.vue'),
        meta: { layout: 'admin', title: 'Sản phẩm' }
    }
]

export default routes
