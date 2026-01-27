/**
 * Categories Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'categories/:id',
        name: 'category-products',
        component: () => import('../views/CategoryProductsView.vue'),
        meta: { title: 'Danh mục sản phẩm' }
    }
]

export default routes
