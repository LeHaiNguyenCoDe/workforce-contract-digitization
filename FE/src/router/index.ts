import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { getAdminRoutes, getLandingRoutes } from './autoRoutes'

// Define routes using auto-loading
const routes: RouteRecordRaw[] = [
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
        component: () => import('@/shared/components/NotFound.vue')
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
  const { useAuthStore } = await import('@/stores')
  const authStore = useAuthStore()

  // Fetch user if not loaded
  if (!authStore.user && !authStore.isLoading) {
    await authStore.fetchUser()
  }

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
