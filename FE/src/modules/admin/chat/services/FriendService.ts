import { httpClient } from '@/plugins/api/httpClient'
import type { IFriendship, IUser, IPaginatedResponse } from '../models/Chat'

const API_BASE = '/friends'

export const FriendService = {
  /**
   * Get all friends
   */
  async getFriends(page = 1, perPage = 20): Promise<IPaginatedResponse<IUser>> {
    const response = await httpClient.get(API_BASE, {
      params: { page, per_page: perPage }
    })
    return response.data.data
  },

  /**
   * Get pending friend requests
   */
  async getPendingRequests(page = 1, perPage = 20): Promise<IPaginatedResponse<IFriendship>> {
    const response = await httpClient.get(`${API_BASE}/pending`, {
      params: { page, per_page: perPage }
    })
    return response.data.data
  },

  /**
   * Get sent friend requests
   */
  async getSentRequests(page = 1, perPage = 20): Promise<IPaginatedResponse<IFriendship>> {
    const response = await httpClient.get(`${API_BASE}/sent`, {
      params: { page, per_page: perPage }
    })
    return response.data.data
  },

  /**
   * Send a friend request
   */
  async sendRequest(userId: number): Promise<IFriendship> {
    const response = await httpClient.post(`${API_BASE}/request`, {
      user_id: userId
    })
    return response.data.data
  },

  /**
   * Accept a friend request
   */
  async acceptRequest(friendshipId: number): Promise<IFriendship> {
    const response = await httpClient.post(`${API_BASE}/${friendshipId}/accept`)
    return response.data.data
  },

  /**
   * Reject a friend request
   */
  async rejectRequest(friendshipId: number): Promise<void> {
    await httpClient.post(`${API_BASE}/${friendshipId}/reject`)
  },

  /**
   * Cancel a sent friend request
   */
  async cancelRequest(friendshipId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/${friendshipId}/cancel`)
  },

  /**
   * Unfriend a user
   */
  async unfriend(userId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/${userId}/unfriend`)
  },

  /**
   * Block a user
   */
  async blockUser(userId: number): Promise<IFriendship> {
    const response = await httpClient.post(`${API_BASE}/${userId}/block`)
    return response.data.data
  },

  /**
   * Search users to add as friends.
   * If no query, returns all users.
   */
  async searchUsers(query?: string): Promise<IUser[]> {
    const response = await httpClient.get(`${API_BASE}/search`, {
      params: query ? { q: query } : {}
    })
    return response.data.data
  },

  /**
   * Get all users for selection
   */
  async getAllUsers(): Promise<IUser[]> {
    const response = await httpClient.get(`${API_BASE}/search`)
    return response.data.data
  }
}
