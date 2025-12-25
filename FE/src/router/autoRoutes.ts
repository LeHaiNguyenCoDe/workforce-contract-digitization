import type { RouteRecordRaw } from 'vue-router'

/**
 * Auto-generate routes from module structure
 * Convention: each module has a views/ folder with Vue components
 */

// Import all views using Vite's glob import
const landingViews = import.meta.glob('@/modules/landing/modules/**/views/*.vue')
const adminViews = import.meta.glob('@/modules/admin/**/views/*.vue')

/**
 * Parse view path to route config
 */
function parseViewPath(path: string, loader: () => Promise<unknown>): RouteRecordRaw | null {
  // Extract module name and view name from path
  const match = path.match(/modules\/([^/]+)\/views\/([^/]+)\.vue$/)
  if (!match) return null

  const [, moduleName, viewName] = match
  
  let routePath = ''
  let routeName = ''

  if (viewName.toLowerCase().includes('list') || viewName === 'index') {
    routePath = `/${moduleName}`
    routeName = moduleName
  } else if (viewName.toLowerCase().includes('detail')) {
    routePath = `/${moduleName}/:id`
    routeName = `${moduleName}-detail`
  } else if (viewName.toLowerCase().includes('create')) {
    routePath = `/${moduleName}/create`
    routeName = `${moduleName}-create`
  } else if (viewName.toLowerCase().includes('edit')) {
    routePath = `/${moduleName}/:id/edit`
    routeName = `${moduleName}-edit`
  } else {
    const viewPath = viewName
      .replace(/View$/, '')
      .replace(/([A-Z])/g, '-$1')
      .toLowerCase()
      .replace(/^-/, '')
    routePath = `/${moduleName}/${viewPath}`
    routeName = `${moduleName}-${viewPath}`
  }

  return {
    path: routePath,
    name: routeName,
    component: loader,
    meta: { module: moduleName }
  }
}

/**
 * Generate routes from glob imports
 */
function generateRoutes(views: Record<string, () => Promise<unknown>>): RouteRecordRaw[] {
  const routes: RouteRecordRaw[] = []
  
  for (const [path, loader] of Object.entries(views)) {
    const route = parseViewPath(path, loader)
    if (route) {
      routes.push(route)
    }
  }

  return routes
}

// Export auto-generated routes
export const landingRoutes = generateRoutes(landingViews)
export const adminRoutes = generateRoutes(adminViews)

/**
 * Special routes that need manual definition
 */
export const specialLandingRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/modules/landing/modules/home/views/HomeView.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/modules/landing/modules/auth/views/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/modules/landing/modules/auth/views/RegisterView.vue'),
    meta: { guest: true }
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: () => import('@/modules/landing/modules/orders/views/CheckoutView.vue'),
    meta: { requiresAuth: true }
  }
]

export const specialAdminRoutes: RouteRecordRaw[] = [
  {
    path: '',
    name: 'admin-dashboard',
    component: () => import('@/modules/admin/dashboard/views/DashboardView.vue')
  }
]

