/**
 * Guest Chat Service
 * API service for guest chat on landing page
 */
import { httpClient } from '@/plugins/api/httpClient'

export interface IGuestInfo {
  name: string
  contact: string
}

export interface IGuestMessage {
  id: number
  content: string
  type?: 'text' | 'image' | 'system'
  is_guest: boolean
  created_at: string
  staff_name?: string
  staff_avatar?: string
  metadata?: any
  attachments?: {
    id: number
    file_name: string
    file_path: string
    file_type: string
    thumbnail_path?: string
  }[]
}

export interface IGuestSessionInfo {
  guest_name: string
  staff?: {
    id: number
    name: string
    avatar?: string
  } | null
  assigned_staff?: {
    id: number
    name: string
    avatar?: string
  } | null
}

export interface IGuestSessionResponse {
  session_token: string
  conversation_id: number
}

const API_BASE = '/frontend/guest-chat'

export const GuestChatService = {
  /**
   * Start a new guest chat session
   */
  async startSession(
    name: string,
    contact: string,
    contactType: 'email' | 'phone'
  ): Promise<IGuestSessionResponse> {
    const response = await httpClient.post<{ data: IGuestSessionResponse }>(`${API_BASE}/session`, {
      name,
      contact,
      contact_type: contactType
    })
    return response.data.data
  },

  /**
   * Get messages for a session
   */
  async getMessages(sessionToken: string): Promise<{ session: IGuestSessionInfo & { status: string }; messages: IGuestMessage[] }> {
    const response = await httpClient.get<{ data: { session: IGuestSessionInfo & { status: string }; messages: IGuestMessage[] } }>(`${API_BASE}/${sessionToken}/messages`)
    return response.data.data
  },

  /**
   * Send a message
   */
  async sendMessage(
    sessionToken: string,
    content: string,
    files?: File[]
  ): Promise<IGuestMessage> {
    if (files && files.length > 0) {
      const formData = new FormData()
      formData.append('content', content)
      files.forEach((file) => {
        formData.append('attachments[]', file)
      })

      const response = await httpClient.post<{ data: IGuestMessage }>(`${API_BASE}/${sessionToken}/messages`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      return response.data.data
    }

    const response = await httpClient.post<{ data: IGuestMessage }>(`${API_BASE}/${sessionToken}/messages`, {
      content
    })
    return response.data.data
  },

  /**
   * Get session information (e.g. assigned staff)
   */
  async getSessionInfo(sessionToken: string): Promise<IGuestSessionInfo> {
    const response = await httpClient.get<{ data: IGuestSessionInfo }>(`${API_BASE}/${sessionToken}/info`)
    return response.data.data
  },

  /**
   * Get session status
   */
  async getSessionStatus(sessionToken: string): Promise<{ active: boolean }> {
    const response = await httpClient.get<{ data: { active: boolean } }>(`${API_BASE}/${sessionToken}/status`)
    return response.data.data
  },

  /**
   * Assign staff to a guest chat session (Admin action)
   */
  async assignStaff(sessionToken: string, staffId: number): Promise<any> {
    const response = await httpClient.post(`${API_BASE}/${sessionToken}/assign`, {
      staff_id: staffId
    })
    return response.data.data
  }
}
