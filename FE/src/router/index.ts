import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { getAdminRoutes, getLandingRoutes } from './autoRoutes'
import { useAuthStore } from '@/stores'

// Define routes using auto-loading
const routes: RouteRecordRaw[] = [
  // Page Panel - Blank Layout (No Header/Footer)
  {
    path: '/page-panel',
    component: () => import('@/layouts/BlankLayout.vue'),
    children: [
      {
        path: '',
        name: 'page-panel',
        component: () => import('@/modules/page-panel/views/index.vue'),
        meta: { title: 'Page Panel' }
      }
    ]
  },
  
  // Marketplace Module - Independent Layout
  {
    path: '/marketplace',
    component: () => import('@/layouts/marketplace/MarketplaceLayout.vue'),
    children: [
      {
        path: '',
        name: 'marketplace',
        component: () => import('@/modules/marketplace/views/index.vue'),
        meta: { title: 'Marketplace' }
      },
      {
        path: 'shop',
        name: 'marketplace-shop',
        component: () => import('@/modules/marketplace/views/ShopPage.vue'),
        meta: { title: 'Marketplace - Shop' }
      },
      {
        path: 'cart',
        name: 'marketplace-cart',
        component: () => import('@/modules/marketplace/views/CartPage.vue'),
        meta: { title: 'Marketplace - Cart' }
      },
      {
        path: 'checkout',
        name: 'marketplace-checkout',
        component: () => import('@/modules/marketplace/views/CheckoutPage.vue'),
        meta: { title: 'Marketplace - Checkout' }
      },
      {
        path: 'product/:id',
        name: 'marketplace-product',
        component: () => import('@/modules/marketplace/views/ProductDetailPage.vue'),
        meta: { title: 'Marketplace - Product' }
      }
    ]
  },

  // Landing (Frontend) routes
  {
    path: '/',
    component: () => import('@/layouts/landing/LandingLayout.vue'),
    children: [
      ...getLandingRoutes(),
      // 404 for landing
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/components/NotFound.vue')
      }
    ]
  },

  // Admin routes
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      ...getAdminRoutes(),
      // 404 for admin
      {
        path: ':pathMatch(.*)*',
        redirect: '/admin'
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

// Navigation guards
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()
  
  // Ensure auth is initialized before any routing decisions
  await authStore.fetchUser()

  // Check auth requirements
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Check admin requirements
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'home' })
  }

  // Redirect authenticated users from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'home' })
  }

  next()
})

export default router
