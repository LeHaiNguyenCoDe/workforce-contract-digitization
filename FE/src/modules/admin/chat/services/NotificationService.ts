import { httpClient } from '@/plugins/api/httpClient'
import type { INotification, IPaginatedResponse } from '../models/Chat'

const API_BASE = '/notifications'

export const NotificationService = {
  /**
   * Get all notifications
   */
  async getNotifications(page = 1, perPage = 20): Promise<IPaginatedResponse<INotification>> {
    const response = await httpClient.get(API_BASE, {
      params: { page, per_page: perPage }
    })
    return response.data.data
  },

  /**
   * Get unread notifications count
   */
  async getUnreadCount(): Promise<number> {
    const response = await httpClient.get(`${API_BASE}/unread-count`)
    return response.data.data.count
  },

  /**
   * Mark a notification as read
   */
  async markAsRead(notificationId: string): Promise<void> {
    await httpClient.post(`${API_BASE}/${notificationId}/read`)
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead(): Promise<void> {
    await httpClient.post(`${API_BASE}/read-all`)
  },

  /**
   * Delete a notification
   */
  async deleteNotification(notificationId: string): Promise<void> {
    await httpClient.delete(`${API_BASE}/${notificationId}`)
  },

  /**
   * Delete all notifications
   */
  async deleteAllNotifications(): Promise<void> {
    await httpClient.delete(API_BASE)
  }
}
