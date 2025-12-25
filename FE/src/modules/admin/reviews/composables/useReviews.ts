/**
 * Composable for Reviews
 * Provides reusable logic for review management
 */

import { computed } from 'vue'
import { useReviewStore } from '../store/store'
import { useSwal } from '@/shared/utils'
import type { Review } from '@/plugins/api/services/ReviewService'

export function useReviews() {
  const store = useReviewStore()
  const swal = useSwal()

  // Computed
  const filteredReviews = computed(() => {
    if (!store.statusFilter) return store.reviews
    return store.reviews.filter(r => r.status === store.statusFilter)
  })

  // Methods
  function formatDate(date: string) {
    return new Date(date).toLocaleDateString('vi-VN')
  }

  function getStatusColor(status: string) {
    const colors: Record<string, string> = {
      pending: 'bg-warning/10 text-warning',
      approved: 'bg-success/10 text-success',
      rejected: 'bg-error/10 text-error'
    }
    return colors[status] || 'bg-slate-500/10 text-slate-400'
  }

  function getStatusText(status: string) {
    const texts: Record<string, string> = {
      pending: 'Chờ duyệt',
      approved: 'Đã duyệt',
      rejected: 'Từ chối'
    }
    return texts[status] || status
  }

  function setStatusFilter(value: string) {
    store.setStatusFilter(value)
    store.fetchReviews({ status: value || undefined })
  }

  async function approveReview(review: Review) {
    try {
      await store.approveReview(review.id)
      await swal.success('Duyệt đánh giá thành công!')
    } catch (error: any) {
      console.error('Failed to approve review:', error)
      await swal.error(error.response?.data?.message || 'Duyệt đánh giá thất bại!')
    }
  }

  async function rejectReview(review: Review) {
    try {
      await store.rejectReview(review.id)
      await swal.success('Từ chối đánh giá thành công!')
    } catch (error: any) {
      console.error('Failed to reject review:', error)
      await swal.error(error.response?.data?.message || 'Từ chối đánh giá thất bại!')
    }
  }

  async function deleteReview(id: number) {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa đánh giá này?')
    if (!confirmed) return

    try {
      await store.deleteReview(id)
      await swal.success('Xóa đánh giá thành công!')
    } catch (error: any) {
      console.error('Failed to delete review:', error)
      await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
  }

  return {
    // Computed
    filteredReviews,
    // Methods
    formatDate,
    getStatusColor,
    getStatusText,
    setStatusFilter,
    approveReview,
    rejectReview,
    deleteReview
  }
}

