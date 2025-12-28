import type { RouteRecordRaw } from 'vue-router'

/**
 * Auto-generate routes from module structure
 * Convention: each module has a router/index.ts exporting routes array
 */

// Import all module routers using Vite's eager glob import
const adminModuleRouters = import.meta.glob<{ routes: RouteRecordRaw[]; default?: RouteRecordRaw[] }>(
  ['@/modules/admin/**/router/index.ts', '!@/modules/admin/erp/**'],
  { eager: true }
)

const erpModuleRouters = import.meta.glob<{ routes: RouteRecordRaw[]; default?: RouteRecordRaw[] }>(
  '@/modules/admin/erp/**/router/index.ts',
  { eager: true }
)

const landingModuleRouters = import.meta.glob<{ routes: RouteRecordRaw[]; default?: RouteRecordRaw[] }>(
  '@/modules/landing/**/router/index.ts',
  { eager: true }
)

/**
 * Collect all routes from module routers
 */
function collectModuleRoutes(
  routers: Record<string, { routes?: RouteRecordRaw[]; default?: RouteRecordRaw[] }>
): RouteRecordRaw[] {
  const routes: RouteRecordRaw[] = []

  for (const [, module] of Object.entries(routers)) {
    const moduleRoutes = module.routes || module.default || []
    if (Array.isArray(moduleRoutes)) {
      routes.push(...moduleRoutes)
    }
  }

  return routes
}

// Export auto-generated routes
export const erpModuleRoutes = collectModuleRoutes(erpModuleRouters)
export const adminModuleRoutes = collectModuleRoutes(adminModuleRouters)
export const landingModuleRoutes = collectModuleRoutes(landingModuleRouters)

/**
 * Special routes that need manual definition (dashboard, etc.)
 */
export const specialAdminRoutes: RouteRecordRaw[] = [
  {
    path: '',
    name: 'admin-dashboard',
    component: () => import('@/modules/admin/dashboard/views/DashboardView.vue')
  }
]

/**
 * Get all admin routes (special + auto-loaded)
 */
export function getAdminRoutes(): RouteRecordRaw[] {
  return [...specialAdminRoutes, ...adminModuleRoutes, ...erpModuleRoutes]
}

/**
 * Get all landing routes (auto-loaded from modules)
 */
export function getLandingRoutes(): RouteRecordRaw[] {
  return [...landingModuleRoutes]
}


