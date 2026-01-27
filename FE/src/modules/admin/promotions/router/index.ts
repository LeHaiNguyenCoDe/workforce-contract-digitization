import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'promotions',
    name: 'admin-promotions',
    component: () => import('../views/management/PromotionListView.vue'),
    meta: { layout: 'admin', title: 'Khuyến mãi' }
  }
]

// Marketing group menu - includes Promotions, Membership, Points, Automation
export const menu: MenuItem = {
  id: 'marketing',
  label: 'admin.marketing',
  icon: 'ri-megaphone-line',
  order: 30,
  children: [
    { id: 'promotions', label: 'admin.promotions', to: '/admin/promotions' },
    { id: 'membership', label: 'admin.membership', to: '/admin/marketing/membership' },
    { id: 'points', label: 'admin.points', to: '/admin/marketing/points' },
    { id: 'automations', label: 'admin.automation', to: '/admin/marketing/automations' },
  ]
}

export default routes
