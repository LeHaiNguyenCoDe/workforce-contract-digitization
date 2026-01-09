import { formatDate, truncate } from '@/utils'
import type { Article } from '../types'

export function formatArticleDate(article: Article): string {
  return formatDate(article.published_at || article.created_at || '')
}

export function getArticleExcerpt(article: Article, length = 150): string {
  if (article.excerpt) return article.excerpt
  return truncate(article.content.replace(/<[^>]*>/g, ''), length)
}

export function getArticleThumbnail(article: Article): string {
  return article.thumbnail || '/placeholder-article.jpg'
}

export function getReadingTime(article: Article): string {
  const words = article.content.replace(/<[^>]*>/g, '').split(/\s+/).length
  const minutes = Math.ceil(words / 200)
  return `${minutes} phút đọc`
}
