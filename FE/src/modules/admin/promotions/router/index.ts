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

// Marketing group menu - organized by logical workflow and priority
export const menu: MenuItem = {
  id: 'marketing',
  label: 'admin.marketing',
  icon: 'ri-megaphone-line',
  order: 30,
  children: [
    // Marketing Campaigns Section - Main features
    { id: 'marketing-overview', label: 'marketing.overview', to: '/admin/marketing', icon: 'ri-dashboard-3-line' },
    { id: 'marketing-leads', label: 'marketing.leads', to: '/admin/marketing/leads', icon: 'ri-contacts-line' },
    { id: 'marketing-segments', label: 'marketing.segments', to: '/admin/marketing/segments', icon: 'ri-community-line' },
    { id: 'marketing-coupons', label: 'marketing.coupons', to: '/admin/marketing/coupons', icon: 'ri-ticket-2-line' },
    { id: 'marketing-analytics', label: 'marketing.analytics', to: '/admin/marketing/analytics', icon: 'ri-bar-chart-2-line' },
    // Promotions & Loyalty Section - Supporting features
    { id: 'promotions', label: 'admin.promotions', to: '/admin/promotions' },
    { id: 'membership', label: 'admin.membership', to: '/admin/marketing/membership' },
    { id: 'points', label: 'admin.points', to: '/admin/marketing/points' },
    { id: 'automations', label: 'admin.automation', to: '/admin/marketing/automations' },
  ]
}

export default routes
