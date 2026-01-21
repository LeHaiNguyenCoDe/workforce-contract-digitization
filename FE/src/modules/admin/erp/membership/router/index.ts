/**
 * Membership Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  {
    path: 'marketing/membership',
    name: 'admin-marketing-membership',
    component: () => import('../views/management/List.vue'),
    meta: { title: 'Hạng thành viên' }
  }
]

// Menu is part of "Marketing" group - defined in promotions module
export default routes
