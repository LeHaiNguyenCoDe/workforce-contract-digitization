/**
 * Customers Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'customers',
    name: 'admin-customers',
    component: () => import('../views/management/List.vue'),
    meta: { title: 'Khách hàng' }
  }
]

// Management group menu - includes Customers, Reviews, Staff
export const menu: MenuItem = {
  id: 'management',
  label: 'admin.management',
  icon: 'ri-group-line',
  order: 40,
  children: [
    { id: 'customers', label: 'admin.customers', to: '/admin/customers' },
    { id: 'reviews', label: 'admin.reviews', to: '/admin/reviews' },
    { id: 'staff', label: 'admin.staff', to: '/admin/employees' },
  ]
}

export default routes
