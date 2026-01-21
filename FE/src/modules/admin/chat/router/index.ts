import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  {
    path: 'chat',
    name: 'admin-chat',
    component: () => import('../views/ChatView.vue'),
    meta: {
      title: 'Chat',
      icon: 'message'
    }
  }
]

// Menu is part of "Tools" group - defined in advanced-modules
export default routes
