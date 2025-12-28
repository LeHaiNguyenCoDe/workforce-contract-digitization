import type { RouteRecordRaw } from 'vue-router'

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

export default routes
