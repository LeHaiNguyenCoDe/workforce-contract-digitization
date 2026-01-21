/**
 * Expenses Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'finance/expenses',
    name: 'admin-finance-expenses',
    component: () => import('../views/management/List.vue'),
    meta: { title: 'Thu chi' }
  },
  {
    path: 'finance/expense-categories',
    name: 'admin-finance-expense-categories',
    component: () => import('../views/management/CategoryList.vue'),
    meta: { title: 'Danh mục Thu Chi' }
  }
]

// Menu is included in finance module - no separate menu here
export default routes
