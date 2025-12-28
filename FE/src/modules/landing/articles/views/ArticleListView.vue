<script setup lang="ts">
/**
 * Article List View
 * Uses useArticles composable for article logic
 */
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useArticles } from '../composables/useArticles'

const { t } = useI18n()

// Use composable
const {
    filteredArticles,
    isLoading,
    searchQuery,
    formatDate,
    setSearch
} = useArticles()

const getArticleImage = (article: any) => {
    return article.image || article.thumbnail || null
}
</script>

<template>
    <div class="container py-8">
        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ t('nav.articles') }}</h1>
            <p class="text-slate-400">Cập nhật những tin tức và bài viết mới nhất về sản phẩm và xu hướng</p>
            
            <!-- Search -->
            <div class="mt-8">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    :placeholder="t('common.search') + '...'" 
                    class="form-input max-w-md mx-auto"
                    @input="setSearch(($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i" class="h-80 bg-dark-700 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Articles Grid -->
        <div v-else-if="filteredArticles.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <RouterLink v-for="article in filteredArticles" :key="article.id" :to="`/articles/${article.id}`"
                class="group card overflow-hidden hover:scale-[1.02] transition-all duration-300">
                <!-- Image -->
                <div class="aspect-video bg-dark-700 rounded-xl mb-4 overflow-hidden -mx-6 -mt-6">
                    <img v-if="getArticleImage(article)" :src="getArticleImage(article)!" :alt="article.title"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1">
                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="pt-4">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2" />
                            <path d="M16 2v4" />
                            <path d="M8 2v4" />
                            <path d="M3 10h18" />
                        </svg>
                        <span>{{ formatDate(article.created_at) }}</span>
                    </div>

                    <h3 class="font-bold text-white mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">
                        {{ article.title }}
                    </h3>

                    <p class="text-sm text-slate-400 line-clamp-3">{{ article.excerpt || article.content?.substring(0, 150) }}</p>
                </div>
            </RouterLink>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1">
                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
            </svg>
            <h3 class="text-xl font-semibold text-slate-400 mb-2">Chưa có bài viết</h3>
            <p class="text-slate-500">Hãy quay lại sau để xem các bài viết mới</p>
        </div>
    </div>
</template>
