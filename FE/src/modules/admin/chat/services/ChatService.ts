import { httpClient } from '@/plugins/api/httpClient'
import type {
  IConversation,
  IMessage,
  IPaginatedResponse
} from '../models/Chat'

const API_BASE = '/chat'

export const ChatService = {
  /**
   * Get all conversations
   */
  async getConversations(page = 1, perPage = 20): Promise<IPaginatedResponse<IConversation>> {
    const response = await httpClient.get(`${API_BASE}/conversations`, {
      params: { page, per_page: perPage }
    })
    return response.data.data
  },

  /**
   * Start a private chat
   */
  async startPrivateChat(userId: number): Promise<IConversation> {
    const response = await httpClient.post(`${API_BASE}/conversations/private`, {
      user_id: userId
    })
    return response.data.data
  },

  /**
   * Create a group chat
   */
  async createGroup(data: { name: string; member_ids: number[]; avatar?: File }): Promise<IConversation> {
    const formData = new FormData()
    formData.append('name', data.name)
    data.member_ids.forEach(id => formData.append('member_ids[]', id.toString()))
    if (data.avatar) {
      formData.append('avatar', data.avatar)
    }

    const response = await httpClient.post(`${API_BASE}/conversations/group`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data.data
  },

  /**
   * Get messages in a conversation
   */
  async getMessages(
    conversationId: number,
    limit = 50,
    beforeId?: number
  ): Promise<IMessage[]> {
    const response = await httpClient.get(`${API_BASE}/conversations/${conversationId}/messages`, {
      params: { limit, before_id: beforeId }
    })
    return response.data.data
  },

  /**
   * Send a message
   */
  async sendMessage(
    conversationId: number,
    data: { content: string; attachments?: File[]; reply_to_id?: number }
  ): Promise<IMessage> {
    const formData = new FormData()
    formData.append('content', data.content || '')
    if (data.reply_to_id) {
      formData.append('reply_to_id', data.reply_to_id.toString())
    }
    if (data.attachments && data.attachments.length > 0) {
      data.attachments.forEach((file) => {
        formData.append('attachments[]', file)
      })
    }

    const response = await httpClient.post(
      `${API_BASE}/conversations/${conversationId}/messages`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    return response.data.data
  },

  /**
   * Mark messages as read
   */
  async markAsRead(conversationId: number): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/read`)
  },

  /**
   * Add members to a group
   */
  async addMembers(conversationId: number, userIds: number[]): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/members`, {
      user_ids: userIds
    })
  },

  /**
   * Remove a member from a group
   */
  async removeMember(conversationId: number, userId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/conversations/${conversationId}/members/${userId}`)
  },

  /**
   * Leave a conversation
   */
  async leaveConversation(conversationId: number): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/leave`)
  },

  /**
   * Delete a conversation
   */
  async deleteConversation(conversationId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/conversations/${conversationId}`)
  },

  /**
   * Delete a message
   */
  async deleteMessage(messageId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/messages/${messageId}`)
  }
}
