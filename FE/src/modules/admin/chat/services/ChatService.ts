import { httpClient } from '@/plugins/api/httpClient'
import type {
  IConversation,
  IMessage,
  IMessageAttachment,
  IPaginatedResponse
} from '../models/Chat'

const API_BASE = '/chat'

export const ChatService = {
  /**
   * Get all conversations
   * @param type - Filter: 'guest', 'private', 'group', or undefined for all
   */
  async getConversations(page = 1, perPage = 20, type?: 'guest' | 'private' | 'group'): Promise<IPaginatedResponse<IConversation>> {
    const response = await httpClient.get(`${API_BASE}/conversations`, {
      params: { page, per_page: perPage, type }
    }) as { data: { data: IPaginatedResponse<IConversation> } }
    return response.data.data
  },

  /**
   * Get a single conversation
   */
  async getConversation(conversationId: number): Promise<IConversation> {
    const response = await httpClient.get(`${API_BASE}/conversations/${conversationId}`) as { data: { data: IConversation } }
    return response.data.data
  },

  /**
   * Start a private chat
   */
  async startPrivateChat(userId: number): Promise<IConversation> {
    const response = await httpClient.post(`${API_BASE}/conversations/private`, {
      user_id: userId
    }) as { data: { data: IConversation } }
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
    }) as { data: { data: IConversation } }
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
    }) as { data: { data: IMessage[] } }
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
    ) as { data: { data: IMessage } }
    return response.data.data
  },

  /**
   * Mark messages as read
   */
  async markAsRead(conversationId: number, messageId?: number): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/read`, {
      message_id: messageId
    })
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
  },

  /**
   * Update conversation settings (mute, pin, etc.)
   */
  async updateConversationSettings(
    conversationId: number,
    settings: { 
      is_muted?: boolean; 
      is_pinned?: boolean;
      read_receipts_enabled?: boolean;
      messaging_permissions?: 'all' | 'admin_only';
      disappearing_messages_ttl?: number | null;
    }
  ): Promise<void> {
    await httpClient.patch(`${API_BASE}/conversations/${conversationId}/settings`, settings)
  },

  /**
   * Search messages in a conversation
   */
  async searchMessages(
    conversationId: number,
    query: string,
    limit = 50
  ): Promise<IMessage[]> {
    const response = await httpClient.get(`${API_BASE}/conversations/${conversationId}/messages/search`, {
      params: { q: query, limit }
    }) as { data: { data: IMessage[] } }
    return response.data.data
  },

  /**
   * Get conversation media (images, videos, or files)
   * @param type - 'media' for images/videos, 'files' for documents
   */
  async getConversationMedia(
    conversationId: number,
    type: 'media' | 'files' = 'media',
    limit = 50
  ): Promise<Array<IMessageAttachment & { message_id?: number; created_at?: string }>> {
    const response = await httpClient.get(`${API_BASE}/conversations/${conversationId}/attachments`, {
      params: { type, limit }
    }) as { data: { data: Array<IMessageAttachment & { message_id?: number; created_at?: string }> } }
    return response.data.data
  },

  /**
   * Get pinned messages in a conversation
   */
  async getPinnedMessages(conversationId: number): Promise<IMessage[]> {
    const response = await httpClient.get(`${API_BASE}/conversations/${conversationId}/messages/pinned`) as { data: { data: IMessage[] } }
    return response.data.data
  },

  /**
   * Pin/unpin a message
   */
  async togglePinMessage(messageId: number, isPinned: boolean): Promise<void> {
    await httpClient.patch(`${API_BASE}/messages/${messageId}/pin`, { is_pinned: isPinned })
  },

  /**
   * Block a user in a conversation
   */
  async blockUser(conversationId: number): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/block`)
  },

  /**
   * Unblock a user in a conversation
   */
  async unblockUser(conversationId: number): Promise<void> {
    await httpClient.delete(`${API_BASE}/conversations/${conversationId}/block`)
  },

  /**
   * Report a conversation
   */
  async reportConversation(conversationId: number, reason: string): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/report`, { reason })
  },

  /**
   * Initiate a call
   */
  async initiateCall(conversationId: number, toUserId: number, type: 'audio' | 'video'): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/call/initiate`, {
      to_user_id: toUserId,
      call_type: type
    })
  },

  /**
   * Send WebRTC signal
   */
  async sendCallSignal(conversationId: number, toUserId: number, type: 'offer' | 'answer' | 'ice-candidate', payload: any): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/call/signal`, {
      to_user_id: toUserId,
      type,
      payload
    })
  },

  /**
   * Update call status
   */
  async updateCallStatus(conversationId: number, status: string, callType: 'audio' | 'video', toUserId?: number, metadata: any = {}): Promise<void> {
    await httpClient.post(`${API_BASE}/conversations/${conversationId}/call/status`, {
      to_user_id: toUserId,
      status,
      call_type: callType,
      metadata
    })
  }
}
