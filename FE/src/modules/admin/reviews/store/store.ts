/**
 * Reviews Store
 * Manages state for review management
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminReviewService, type Review } from '@/plugins/api/services/ReviewService'
import httpClient from '@/plugins/api/httpClient'

export const useReviewStore = defineStore('admin-reviews', () => {
  // State
  const reviews = ref<Review[]>([])
  const isLoading = ref(false)
  const statusFilter = ref('')

  // Getters
  const hasReviews = computed(() => reviews.value.length > 0)

  // Actions
  async function fetchReviews(params?: { status?: string }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {}
      if (params?.status) queryParams.status = params.status

      const response = await httpClient.get('/admin/reviews', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        reviews.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        reviews.value = data.data
      } else {
        reviews.value = []
      }
    } catch (error) {
      console.error('Failed to fetch reviews:', error)
      reviews.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function approveReview(id: number): Promise<boolean> {
    try {
      await adminReviewService.approve(id)
      const review = reviews.value.find(r => r.id === id)
      if (review) {
        review.status = 'approved'
      }
      return true
    } catch (error) {
      console.error('Failed to approve review:', error)
      throw error
    }
  }

  async function rejectReview(id: number): Promise<boolean> {
    try {
      await adminReviewService.reject(id)
      const review = reviews.value.find(r => r.id === id)
      if (review) {
        review.status = 'rejected'
      }
      return true
    } catch (error) {
      console.error('Failed to reject review:', error)
      throw error
    }
  }

  async function deleteReview(id: number): Promise<boolean> {
    try {
      await adminReviewService.delete(id)
      reviews.value = reviews.value.filter(r => r.id !== id)
      return true
    } catch (error) {
      console.error('Failed to delete review:', error)
      return false
    }
  }

  function setStatusFilter(status: string) {
    statusFilter.value = status
  }

  function reset() {
    reviews.value = []
    statusFilter.value = ''
  }

  return {
    // State
    reviews,
    isLoading,
    statusFilter,
    // Getters
    hasReviews,
    // Actions
    fetchReviews,
    approveReview,
    rejectReview,
    deleteReview,
    setStatusFilter,
    reset
  }
})

