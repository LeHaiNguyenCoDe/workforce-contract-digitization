/**
 * Orders Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'checkout',
        name: 'checkout',
        component: () => import('../views/CheckoutView.vue'),
        meta: { title: 'Thanh toán', requiresAuth: true }
    },
    {
        path: 'orders',
        name: 'orders',
        component: () => import('../views/OrderListView.vue'),
        meta: { title: 'Đơn hàng', requiresAuth: true }
    },
    {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('../views/OrderDetailView.vue'),
        meta: { title: 'Chi tiết đơn hàng', requiresAuth: true }
    },
    {
        path: 'orders/:id/success',
        name: 'order-success',
        component: () => import('../views/OrderSuccessView.vue'),
        meta: { title: 'Đặt hàng thành công', requiresAuth: true }
    }
]

export default routes
