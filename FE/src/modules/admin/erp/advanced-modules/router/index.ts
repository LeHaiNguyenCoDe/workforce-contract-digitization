/**
 * Advanced Modules Routes
 * Routes for new advanced ERP modules: Shipping, HR, Tasks, Calendar, Warranties, API Logs, Import/Export
 */
import type { RouteRecordRaw } from 'vue-router'
import type { MenuItem } from '@/router/autoRoutes'

export const routes: RouteRecordRaw[] = [
  // Settings
  {
    path: 'settings',
    name: 'admin-settings',
    component: () => import('../../views/SettingsView.vue'),
    meta: { title: 'Cài đặt hệ thống' }
  },
  // Quotations
  {
    path: 'quotations',
    name: 'admin-quotations',
    component: () => import('../../views/QuotationsView.vue'),
    meta: { title: 'Báo giá' }
  },
  // Shipping Partners
  {
    path: 'shipping',
    name: 'admin-shipping',
    component: () => import('../../views/ShippingView.vue'),
    meta: { title: 'Đối tác vận chuyển' }
  },
  // HR - Employees
  {
    path: 'employees',
    name: 'admin-employees',
    component: () => import('../../views/EmployeesView.vue'),
    meta: { title: 'Nhân viên' }
  },
  // Tasks (Kanban)
  {
    path: 'tasks',
    name: 'admin-tasks',
    component: () => import('../../views/TasksView.vue'),
    meta: { title: 'Công việc' }
  },
  // Calendar
  {
    path: 'calendar',
    name: 'admin-calendar',
    component: () => import('../../views/CalendarView.vue'),
    meta: { title: 'Lịch hẹn' }
  },
  // Warranties
  {
    path: 'warranties',
    name: 'admin-warranties',
    component: () => import('../../views/WarrantiesView.vue'),
    meta: { title: 'Bảo hành' }
  },
  // API Logs
  {
    path: 'api-logs',
    name: 'admin-api-logs',
    component: () => import('../../views/ApiLogsView.vue'),
    meta: { title: 'API Logs' }
  },
  // Import/Export
  {
    path: 'import-export',
    name: 'admin-import-export',
    component: () => import('../../views/ImportExportView.vue'),
    meta: { title: 'Import/Export' }
  },
  // Email Marketing
  {
    path: 'email-marketing',
    name: 'admin-email-marketing',
    component: () => import('../../views/EmailCampaignsView.vue'),
    meta: { title: 'Email Marketing' }
  },
]

// Tools group menu - Tasks, Calendar (standalone items not in other groups)
export const menu: MenuItem[] = [
  {
    id: 'tools',
    label: 'admin.tools',
    icon: 'ri-tools-line',
    order: 50,
    children: [
      { id: 'tasks', label: 'admin.tasks', to: '/admin/tasks' },
      { id: 'calendar', label: 'admin.calendar', to: '/admin/calendar' },
      { id: 'chat', label: 'admin.chat', to: '/admin/chat' },
    ]
  }
]

export default routes
