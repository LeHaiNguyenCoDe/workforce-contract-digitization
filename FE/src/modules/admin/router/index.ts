import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'articles',
        name: 'admin-articles',
        component: () => import('../views/ArticleListView.vue'),
        meta: { layout: 'admin', title: 'Tin tức' }
    }
]

export default routes
