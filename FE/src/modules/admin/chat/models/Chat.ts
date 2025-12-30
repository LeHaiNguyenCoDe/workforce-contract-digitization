// Chat Types and Interfaces
export interface IUser {
  id: number
  name: string
  email?: string
  avatar?: string | null
  last_seen_at?: string | null
  is_online?: boolean
}

export interface IConversation {
  id: number
  name: string | null
  type: 'private' | 'group'
  avatar: string | null
  created_by: number
  created_at: string
  updated_at: string
  users: IConversationUser[]
  latest_message?: IMessage | null
  unread_count?: number
  pivot?: {
    role: 'member' | 'admin'
    last_read_at: string | null
    is_muted: boolean
    is_pinned: boolean
  }
}

export interface IConversationUser extends IUser {
  pivot?: {
    role: 'member' | 'admin'
    last_read_at: string | null
    is_muted: boolean
    is_pinned: boolean
  }
}

export interface IMessage {
  id: number
  conversation_id: number
  user_id: number
  content: string | null
  type: 'text' | 'image' | 'file' | 'system'
  metadata: Record<string, any> | null
  reply_to_id: number | null
  is_edited: boolean
  created_at: string
  updated_at: string
  user?: IUser
  attachments?: IMessageAttachment[]
  reply_to?: IMessageReply | null
}

export interface IMessageReply {
  id: number
  content: string | null
  user_name?: string
}

export interface IMessageAttachment {
  id: number
  file_name: string
  file_path: string
  file_type: string
  file_size: number
  thumbnail_path: string | null
}

export interface IFriendship {
  id: number
  user_id: number
  friend_id: number
  status: 'pending' | 'accepted' | 'blocked'
  created_at: string
  updated_at: string
  user?: IUser
  friend?: IUser
}

export interface INotification {
  id: string
  user_id: number
  type: string
  notifiable_type: string | null
  notifiable_id: number | null
  data: Record<string, any>
  read_at: string | null
  created_at: string
}

// Form types
export interface ISendMessageForm {
  content?: string
  type?: 'text' | 'image' | 'file'
  reply_to_id?: number | null
  attachments?: File[]
}

export interface ICreateGroupForm {
  name: string
  member_ids: number[]
  avatar?: File | null
}

// API Response types
export interface IPaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface IApiResponse<T> {
  success: boolean
  data: T
  message: string
}
