/**
 * Landing Articles Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Article, ArticleFilters } from './types'

export const useArticleStore = defineStore('landing-articles', () => {
  // State
  const articles = ref<Article[]>([])
  const currentArticle = ref<Article | null>(null)
  const isLoading = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const filters = ref<ArticleFilters>({})

  // Getters
  const hasArticles = computed(() => articles.value.length > 0)

  // Actions
  async function fetchArticles(params?: ArticleFilters & { page?: number }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value
      }
      if (params?.search) queryParams.search = params.search
      if (params?.category) queryParams.category = params.category

      const response = await httpClient.get<any>('/articles', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        articles.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        articles.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch articles:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchArticleById(id: number | string): Promise<Article | null> {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>(`/articles/${id}`)
      const data = response.data as any
      currentArticle.value = data?.data || data
      return currentArticle.value
    } catch (error) {
      console.error('Failed to fetch article:', error)
      return null
    } finally {
      isLoading.value = false
    }
  }

  function reset() {
    articles.value = []
    currentArticle.value = null
    currentPage.value = 1
  }

  return {
    articles,
    currentArticle,
    isLoading,
    currentPage,
    totalPages,
    filters,
    hasArticles,
    fetchArticles,
    fetchArticleById,
    reset
  }
})
