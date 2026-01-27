/**
 * Profile Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'profile',
        component: () => import('../ProfileLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/profile/cart'
            },
            {
                path: 'cart',
                name: 'profile-cart',
                component: () => import('@/modules/landing/cart/views/CartView.vue'),
                meta: { title: 'Giỏ hàng' }
            },
            {
                path: 'info',
                name: 'profile-info',
                component: () => import('../views/ProfileInfoView.vue'),
                meta: { title: 'Thông tin cá nhân' }
            },
            {
                path: 'address',
                name: 'profile-address',
                component: () => import('../views/ShippingAddressView.vue'),
                meta: { title: 'Địa chỉ giao hàng' }
            },
            {
                path: 'payment',
                name: 'profile-payment',
                component: () => import('../views/PaymentMethodsView.vue'),
                meta: { title: 'Phương thức thanh toán' }
            }
        ]
    }
]

export default routes
