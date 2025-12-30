import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { INotification } from '../models/Chat'
import { NotificationService } from '../services/NotificationService'

export const useNotificationStore = defineStore('notifications', () => {
  // State
  const notifications = ref<INotification[]>([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const hasMore = ref(true)
  const currentPage = ref(1)

  // Getters
  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.read_at)
  })

  const readNotifications = computed(() => {
    return notifications.value.filter(n => n.read_at)
  })

  // Actions
  async function fetchNotifications(page = 1, unreadOnly = false) {
    isLoading.value = true
    try {
      const response = await NotificationService.getNotifications(page, 20, unreadOnly)
      if (page === 1) {
        notifications.value = response.data
      } else {
        notifications.value.push(...response.data)
      }
      currentPage.value = page
      hasMore.value = response.current_page < response.last_page
      return response
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUnreadCount() {
    unreadCount.value = await NotificationService.getUnreadCount()
    return unreadCount.value
  }

  async function markAsRead(notificationId: string) {
    await NotificationService.markAsRead(notificationId)
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  }

  async function markAllAsRead() {
    await NotificationService.markAllAsRead()
    notifications.value.forEach(n => {
      if (!n.read_at) {
        n.read_at = new Date().toISOString()
      }
    })
    unreadCount.value = 0
  }

  async function deleteNotification(notificationId: string) {
    await NotificationService.deleteNotification(notificationId)
    const index = notifications.value.findIndex(n => n.id === notificationId)
    if (index !== -1) {
      const notification = notifications.value[index]
      if (!notification.read_at) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      notifications.value.splice(index, 1)
    }
  }

  async function deleteAll() {
    await NotificationService.deleteAll()
    notifications.value = []
    unreadCount.value = 0
  }

  // WebSocket handlers
  function handleNewNotification(notification: INotification) {
    // Add to top of list
    notifications.value.unshift(notification)
    unreadCount.value++
  }

  function $reset() {
    notifications.value = []
    unreadCount.value = 0
    isLoading.value = false
    hasMore.value = true
    currentPage.value = 1
  }

  return {
    // State
    notifications,
    unreadCount,
    isLoading,
    hasMore,
    currentPage,

    // Getters
    unreadNotifications,
    readNotifications,

    // Actions
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    deleteAll,
    handleNewNotification,
    $reset
  }
})
