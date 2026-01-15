import type { RouteRecordRaw } from 'vue-router'

const foodieRoutes: RouteRecordRaw[] = [
  {
    path: '/foodie',
    name: 'foodie-home',
    component: () => import('../views/FoodieHomeView.vue'),
    meta: {
      title: 'Cheers - Order Your Celebration',
      layout: 'blank'
    }
  }
]

export default foodieRoutes
