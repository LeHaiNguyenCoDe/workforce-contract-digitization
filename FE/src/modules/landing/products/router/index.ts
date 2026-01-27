/**
 * Products Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'products',
        name: 'products',
        component: () => import('../views/ProductListView.vue'),
        meta: { title: 'Sản phẩm' }
    },
    {
        path: 'products/:id',
        name: 'product-detail',
        component: () => import('../views/ProductDetailView.vue'),
        meta: { title: 'Chi tiết sản phẩm' }
    }
]

export default routes
