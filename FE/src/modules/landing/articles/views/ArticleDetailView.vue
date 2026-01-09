<script setup lang="ts">
/**
 * Article Detail View
 * Uses useArticles composable for article fetching and formatting
 */
import { onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useArticles } from '../composables/useArticles'
import { sanitizeHtml } from '@/utils/sanitize'

const { t } = useI18n()
const route = useRoute()

// Use composable
const {
    currentArticle: article,
    isLoading,
    formatDate,
    fetchArticleById
} = useArticles()

// Sanitize article content to prevent XSS
const sanitizedContent = computed(() => {
    return article.value?.content ? sanitizeHtml(article.value.content) : ''
})

onMounted(() => {
    if (route.params.id) {
        fetchArticleById(route.params.id as string)
    }
})
</script>

<template>
    <div class="container py-8">
        <!-- Loading -->
        <div v-if="isLoading" class="max-w-3xl mx-auto animate-pulse">
            <div class="h-10 bg-dark-700 rounded-lg w-3/4 mb-4"></div>
            <div class="h-6 bg-dark-700 rounded-lg w-1/4 mb-8"></div>
            <div class="aspect-video bg-dark-700 rounded-2xl mb-8"></div>
            <div class="space-y-3">
                <div class="h-4 bg-dark-700 rounded"></div>
                <div class="h-4 bg-dark-700 rounded w-5/6"></div>
                <div class="h-4 bg-dark-700 rounded w-4/6"></div>
            </div>
        </div>

        <!-- Article -->
        <article v-else-if="article" class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
                <RouterLink to="/" class="hover:text-white">{{ t('nav.home') }}</RouterLink>
                <span>/</span>
                <RouterLink to="/articles" class="hover:text-white">{{ t('nav.articles') }}</RouterLink>
                <span>/</span>
                <span class="text-white line-clamp-1">{{ article.title }}</span>
            </nav>

            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ article.title }}</h1>
                <div class="flex items-center gap-4 text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2" />
                            <path d="M16 2v4" />
                            <path d="M8 2v4" />
                            <path d="M3 10h18" />
                        </svg>
                        <span>{{ formatDate(article.created_at) }}</span>
                    </div>
                    <div v-if="article.author" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span>{{ article.author.name }}</span>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="article.image" class="aspect-video bg-dark-800 rounded-2xl overflow-hidden mb-8">
                <img :src="article.image" :alt="article.title" class="w-full h-full object-cover" />
            </div>

            <!-- Content -->
            <div class="prose prose-invert prose-lg max-w-none">
                <!-- XSS sanitized content -->
                <div v-html="sanitizedContent" class="text-slate-300 leading-relaxed space-y-4"></div>
            </div>

            <!-- Back Button -->
            <div class="mt-12 pt-8 border-t border-white/10">
                <RouterLink to="/articles" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                    Quay lại danh sách
                </RouterLink>
            </div>
        </article>

        <!-- Not Found -->
        <div v-else class="text-center py-16">
            <h2 class="text-2xl font-bold text-slate-400 mb-4">Không tìm thấy bài viết</h2>
            <RouterLink to="/articles" class="btn btn-primary">Quay lại danh sách</RouterLink>
        </div>
    </div>
</template>
