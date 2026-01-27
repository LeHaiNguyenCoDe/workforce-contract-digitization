/**
 * Articles Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'example',
        name: 'example',
        component: () => import('../views/index.vue'),
        meta: { title: 'Tin tức' }
    },
]

export default routes
