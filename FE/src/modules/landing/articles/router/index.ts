/**
 * Articles Module Routes
 */
import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'articles',
        name: 'articles',
        component: () => import('../views/ArticleListView.vue'),
        meta: { title: 'Tin tức' }
    },
    {
        path: 'articles/:id',
        name: 'article-detail',
        component: () => import('../views/ArticleDetailView.vue'),
        meta: { title: 'Chi tiết bài viết' }
    }
]

export default routes
