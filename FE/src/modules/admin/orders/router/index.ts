import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'orders',
    name: 'admin-orders',
    component: () => import('../views/management/OrderListView.vue'),
    meta: { layout: 'admin', title: 'Đơn hàng' }
  }
]

// Sales group menu - includes Products, Categories, Orders, Returns
export const menu: MenuItem = {
  id: 'sales',
  label: 'admin.sales',
  icon: 'ri-store-line',
  order: 10,
  children: [
    { id: 'products', label: 'admin.products', to: '/admin/products' },
    { id: 'categories', label: 'admin.categories', to: '/admin/categories' },
    { id: 'orders', label: 'admin.orders', to: '/admin/orders' },
    { id: 'returns', label: 'admin.returns', to: '/admin/returns' },
  ]
}

export default routes
