/**
 * Automations Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  {
    path: 'marketing/automations',
    name: 'admin-marketing-automations',
    component: () => import('../views/management/List.vue'),
    meta: { title: 'Automations' }
  }
]

// Menu is part of "Marketing" group - defined in promotions module
export default routes
