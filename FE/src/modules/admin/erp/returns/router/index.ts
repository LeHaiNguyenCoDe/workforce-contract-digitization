/**
 * Returns Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  {
    path: 'returns',
    name: 'admin-returns',
    component: () => import('../views/management/List.vue'),
    meta: { title: 'Trả hàng / RMA' }
  }
]

// Menu is part of "Sales" group - defined in orders module
export default routes
