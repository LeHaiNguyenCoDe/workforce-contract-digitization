/**
 * Composable for Promotions
 * Provides reusable logic for promotion management
 */

import { ref } from 'vue'
import { useSwal } from '@/shared/utils'
import { adminPromotionService } from '@/plugins/api/services/PromotionService'
import type { Promotion } from '@/plugins/api/services/PromotionService'

export function usePromotions() {
  const store = useAdminPromotionStore()
  const swal = useSwal()

  // Local state
  const showModal = ref(false)
  const editingPromotion = ref<Promotion | null>(null)

  // Methods
  function formatDate(date?: string) {
    return date ? new Date(date).toLocaleDateString('vi-VN') : '-'
  }

  function formatDiscount(promo: Promotion) {
    return promo.type === 'percent'
      ? `${promo.value}%`
      : new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(promo.value)
  }

  function isExpired(promo: Promotion) {
    return promo.ends_at ? new Date(promo.ends_at) < new Date() : false
  }

  function generateCode(name: string): string {
    return name.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10) + Math.random().toString(36).substring(2, 5).toUpperCase()
  }

  function openCreateModal() {
    editingPromotion.value = null
    store.selectedPromotion = null
    store.resetPromotionForm()
    showModal.value = true
  }

  async function openEditModal(promo: Promotion) {
    try {
      // Fetch full promotion data by ID to ensure we have all fields
      const fullPromo = await adminPromotionService.getById(promo.id)
      editingPromotion.value = fullPromo
      store.selectedPromotion = fullPromo
      store.promotionForm = {
        name: fullPromo.name,
        code: fullPromo.code || '',
        type: fullPromo.type,
        value: fullPromo.value,
        starts_at: fullPromo.starts_at?.split('T')[0] || '',
        ends_at: fullPromo.ends_at?.split('T')[0] || '',
        is_active: fullPromo.is_active ?? true
      }
    } catch (error) {
      console.error('Failed to fetch promotion details:', error)
      // Fallback to using promotion data from list
      editingPromotion.value = promo
      store.selectedPromotion = promo
      store.promotionForm = {
        name: promo.name,
        code: promo.code || '',
        type: promo.type,
        value: promo.value,
        starts_at: promo.starts_at?.split('T')[0] || '',
        ends_at: promo.ends_at?.split('T')[0] || '',
        is_active: promo.is_active ?? true
      }
    }
    showModal.value = true
  }

  async function savePromotion() {
    if (store.isSaving) return
    if (!store.promotionForm.name || !store.promotionForm.value) {
      await swal.warning('Vui lòng điền đầy đủ thông tin bắt buộc!')
      return
    }

    try {
      const payload: any = {
        name: store.promotionForm.name,
        type: store.promotionForm.type,
        value: store.promotionForm.value,
        is_active: store.promotionForm.is_active
      }
      if (store.promotionForm.code) payload.code = store.promotionForm.code
      if (store.promotionForm.starts_at) payload.starts_at = store.promotionForm.starts_at
      if (store.promotionForm.ends_at) payload.ends_at = store.promotionForm.ends_at

      if (editingPromotion.value) {
        await store.updatePromotion(editingPromotion.value.id, payload)
        await swal.success('Cập nhật khuyến mãi thành công!')
      } else {
        if (!payload.code) payload.code = generateCode(store.promotionForm.name)
        await store.createPromotion(payload)
        await swal.success('Tạo khuyến mãi thành công!')
      }

      showModal.value = false
      editingPromotion.value = null
      store.selectedPromotion = null
    } catch (error: any) {
      console.error('Failed to save promotion:', error)
      await swal.error(error.response?.data?.message || 'Lưu thất bại!')
    }
  }

  async function deletePromotion(id: number) {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa khuyến mãi này?')
    if (!confirmed) return

    try {
      await store.deletePromotion(id)
      await swal.success('Xóa khuyến mãi thành công!')
    } catch (error: any) {
      console.error('Failed to delete promotion:', error)
      await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
  }

  return {
    // State
    showModal,
    editingPromotion,
    // Methods
    formatDate,
    formatDiscount,
    isExpired,
    openCreateModal,
    openEditModal,
    savePromotion,
    deletePromotion
  }
}

