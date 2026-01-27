import type { RouteRecordRaw } from 'vue-router'

/**
 * MenuItem interface for auto-menu configuration
 * Each module can export a 'menu' to be automatically collected
 */
export interface MenuItem {
  id: string
  label: string          // Translation key (e.g., 't-products', 'admin.products')
  icon?: string          // Icon class (e.g., 'ri-dashboard-2-line')
  to?: string            // Route path (e.g., '/admin/products')
  order?: number         // Menu order (lower = higher priority)
  badge?: {
    text: string         // Badge text or translation key
    variant: string      // Badge variant (e.g., 'success', 'danger')
  }
  children?: MenuItem[]  // Sub menu items
  meta?: {
    section?: 'main' | 'pages' | 'settings'  // Menu section
    hidden?: boolean     // Hide from menu but keep in config
    roles?: string[]     // Required roles to show this menu
  }
}

/**
 * Module export interface - what each module router should export
 */
export interface ModuleExport {
  routes?: RouteRecordRaw[]
  default?: RouteRecordRaw[]
  menu?: MenuItem | MenuItem[]
}

// Import all module routers using Vite's eager glob import
const adminModuleRouters = import.meta.glob<ModuleExport>(
  ['@/modules/admin/**/router/index.ts', '!@/modules/admin/erp/**'],
  { eager: true }
)

const erpModuleRouters = import.meta.glob<ModuleExport>(
  '@/modules/admin/erp/**/router/index.ts',
  { eager: true }
)

const landingModuleRouters = import.meta.glob<{ routes: RouteRecordRaw[]; default?: RouteRecordRaw[] }>(
  '@/modules/landing/**/router/index.ts',
  { eager: true }
)

const standaloneModuleRouters = import.meta.glob<ModuleExport>(
  '@/modules/!(admin|landing)/**/router/index.ts',
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

/**
 * Collect all menus from module routers
 */
function collectModuleMenus(routers: Record<string, ModuleExport>): MenuItem[] {
  const menus: MenuItem[] = []

  for (const [path, module] of Object.entries(routers)) {
    if (module.menu) {
      const menuItems = Array.isArray(module.menu) ? module.menu : [module.menu]
      menus.push(...menuItems.map(item => ({
        ...item,
        _source: path // Debug: track where menu came from
      })))
    }
  }

  // Sort by order (default order = 100)
  return menus.sort((a, b) => (a.order ?? 100) - (b.order ?? 100))
}

// Export auto-generated routes
export const erpModuleRoutes = collectModuleRoutes(erpModuleRouters)
export const adminModuleRoutes = collectModuleRoutes(adminModuleRouters)
export const landingModuleRoutes = collectModuleRoutes(landingModuleRouters)
export const standaloneModuleRoutes = collectModuleRoutes(standaloneModuleRouters)

// Export auto-collected menus
export const adminModuleMenus = collectModuleMenus(adminModuleRouters)
export const erpModuleMenus = collectModuleMenus(erpModuleRouters)

/**
 * Get all admin menus (from modules + special menus)
 */
export function getAdminMenus(): MenuItem[] {
  // Dashboard is always first
  const dashboardMenu: MenuItem = {
    id: 'dashboard',
    label: 't-dashboards',
    icon: 'ri-dashboard-2-line',
    to: '/admin',
    order: 0
  }

  return [dashboardMenu, ...adminModuleMenus, ...erpModuleMenus]
}

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
  return [...landingModuleRoutes, ...standaloneModuleRoutes]
}
