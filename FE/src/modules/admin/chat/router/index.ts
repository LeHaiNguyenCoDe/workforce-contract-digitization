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
