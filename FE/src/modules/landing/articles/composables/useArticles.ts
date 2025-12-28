/**
 * Articles Composable
 */

import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export function useArticles() {
  // State
  const articles = ref<any[]>([])
  const currentArticle = ref<any>(null)
  const isLoading = ref(true)
  const searchQuery = ref('')

  // Computed
  const filteredArticles = computed(() => {
    if (!searchQuery.value) return articles.value
    const query = searchQuery.value.toLowerCase()
    return articles.value.filter(a =>
      a.title.toLowerCase().includes(query) ||
      a.excerpt?.toLowerCase().includes(query)
    )
  })

  // Methods
  function formatDate(date: string) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  function setSearch(query: string) {
    searchQuery.value = query
  }

  async function fetchArticles() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/articles', { params: { per_page: 12 } })
      const data = response.data as any
      
      if (data?.data?.data && Array.isArray(data.data.data)) {
        articles.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        articles.value = data.data
      } else {
        articles.value = []
      }
    } catch (error) {
      console.error('Failed to fetch articles:', error)
      articles.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchArticleById(idOrSlug: string | number) {
    isLoading.value = true
    try {
      const response = await httpClient.get(`/frontend/articles/${idOrSlug}`)
      const data = response.data as any
      currentArticle.value = data?.data || data
    } catch (error) {
      console.error('Failed to fetch article:', error)
      currentArticle.value = null
    } finally {
      isLoading.value = false
    }
  }

  onMounted(fetchArticles)

  return {
    articles,
    currentArticle,
    isLoading,
    searchQuery,
    filteredArticles,
    formatDate,
    setSearch,
    fetchArticles,
    fetchArticleById
  }
}
