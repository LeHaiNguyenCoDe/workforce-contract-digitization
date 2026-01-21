import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'finance/dashboard',
    name: 'admin-finance-dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { title: 'Tổng quan Tài chính' }
  },
  {
    path: 'finance/receivables',
    name: 'admin-finance-receivables',
    component: () => import('../views/ReceivablesList.vue'),
    meta: { title: 'Công nợ phải thu' }
  },
  {
    path: 'finance/payables',
    name: 'admin-finance-payables',
    component: () => import('../views/PayablesList.vue'),
    meta: { title: 'Công nợ phải trả' }
  }
]

// Menu configuration for this module - with submenu
export const menu: MenuItem = {
  id: 'finance',
  label: 'admin.finance',
  icon: 'ri-money-dollar-circle-line',
  order: 60,
  children: [
    { id: 'finance-dashboard', label: 'admin.dashboard', to: '/admin/finance/dashboard' },
    { id: 'finance-receivables', label: 'admin.receivables', to: '/admin/finance/receivables' },
    { id: 'finance-payables', label: 'admin.payables', to: '/admin/finance/payables' },
  ]
}

export default routes
