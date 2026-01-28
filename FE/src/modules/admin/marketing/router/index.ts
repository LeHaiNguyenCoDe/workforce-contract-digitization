import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  {
    path: 'marketing',
    // component: () => import('@/layouts/AdminLayout.vue'),
    meta: { 
      title: 'Marketing',
      requiresAuth: true,
      requiresAdmin: true 
    },
    children: [
      // Marketing Dashboard/Main
      {
        path: '',
        name: 'admin-marketing',
        component: () => import('@/components/Marketing/index.vue'),
        meta: { title: 'Marketing' }
      },
      
      // Leads Management
      {
        path: 'leads',
        name: 'admin-marketing-leads',
        component: () => import('@/components/Marketing/LeadsManagement.vue'),
        meta: { title: 'Lead Management' }
      },
      
      // Coupons Management
      {
        path: 'coupons',
        name: 'admin-marketing-coupons',
        component: () => import('@/components/Marketing/CouponsManagement.vue'),
        meta: { title: 'Coupon Management' }
      },
      
      // Segmentation Management
      {
        path: 'segments',
        name: 'admin-marketing-segments',
        component: () => import('@/components/Marketing/SegmentationManagement.vue'),
        meta: { title: 'Customer Segmentation' }
      },
      
      // Analytics Dashboard
      {
        path: 'analytics',
        name: 'admin-marketing-analytics',
        component: () => import('@/components/Marketing/AnalyticsDashboard.vue'),
        meta: { title: 'Marketing Analytics' }
      }
    ]
  }
]
