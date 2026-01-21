import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'products',
    name: 'admin-products',
    component: () => import('../views/management/ProductListView.vue'),
    meta: { layout: 'admin', title: 'Sản phẩm' }
  },
  {
    path: 'products/create',
    name: 'admin-products-create',
    component: () => import('../views/management/ProductFormView.vue'),
    meta: { layout: 'admin', title: 'Tạo sản phẩm' }
  },
  {
    path: 'products/:id/edit',
    name: 'admin-products-edit',
    component: () => import('../views/management/ProductFormView.vue'),
    meta: { layout: 'admin', title: 'Chỉnh sửa sản phẩm' }
  },
  {
    path: 'products/:id',
    name: 'admin-products-view',
    component: () => import('../views/management/ProductFormView.vue'),
    meta: { layout: 'admin', title: 'Chi tiết sản phẩm' }
  },
  // Categories (Part of Products Module)
  {
    path: 'categories',
    name: 'admin-categories',
    component: () => import('../views/categories/CategoryListView.vue'),
    meta: { layout: 'admin', title: 'Danh mục' }
  }
]

// Menu is part of "Sales" group - defined in orders module
export default routes

