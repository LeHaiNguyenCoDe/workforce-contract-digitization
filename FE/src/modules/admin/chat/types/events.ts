/**
 * TypeScript interfaces for real-time events
 * Provides type safety for Echo channel events
 */
import type { IMessage, IConversation, IFriendship, IUser, INotification } from '../models/Chat'

// ============================================
// Echo Configuration Types
// ============================================

export interface EchoConfig {
  broadcaster: 'reverb' | 'pusher'
  key: string
  wsHost: string
  wsPort: number
  wssPort: number
  forceTLS: boolean
  enabledTransports: ('ws' | 'wss')[]
  authEndpoint: string
  withCredentials: boolean
  authorizer?: (channel: EchoChannel) => ChannelAuthorizer
}

export interface EchoChannel {
  name: string
}

export interface ChannelAuthorizer {
  authorize: (socketId: string, callback: AuthCallback) => void
}

export type AuthCallback = (error: boolean, data?: AuthResponse | Error) => void

export interface AuthResponse {
  auth: string
  channel_data?: string
}

// ============================================
// Connection State Types
// ============================================

export interface ConnectionState {
  previous: string
  current: string
}

export type ConnectionStatus =
  | 'initialized'
  | 'connecting'
  | 'connected'
  | 'unavailable'
  | 'failed'
  | 'disconnected'

// ============================================
// Event Data Types
// ============================================

export interface MessageSentEvent {
  message: IMessage
  conversation_id: number
}

export interface MessageSentData extends IMessage {
  // IMessage already contains all fields
}

export interface TypingEvent {
  user_id: number
  user_name: string
  conversation_id: number
  is_typing: boolean
}

export interface NotificationNewEvent {
  notification: INotification
}

export interface FriendRequestEvent {
  id: number
  friendship: IFriendship
  sender: IUser
}

export interface GuestChatStartedEvent {
  conversation: IConversation
  guest_name?: string
}

export interface ConversationUpdatedEvent {
  conversation: IConversation
  action: 'created' | 'updated' | 'deleted'
}

export interface UserStatusEvent {
  user_id: number
  is_online: boolean
  last_seen_at?: string
}

export interface MessageReadEvent {
  conversation_id: number
  user_id: number
  message_id: number
  read_at: string
}

export interface MessageDeletedEvent {
  message_id: number
  conversation_id: number
  deleted_by: number
}

// ============================================
// Channel Event Mapping
// ============================================

export interface UserChannelEvents {
  '.notification.new': INotification
  '.message.sent': IMessage
  '.friend.request': FriendRequestEvent
  '.user.status': UserStatusEvent
}

export interface ConversationChannelEvents {
  '.message.sent': IMessage
  '.user.typing': TypingEvent
  '.message.read': MessageReadEvent
  '.message.deleted': MessageDeletedEvent
  '.conversation.updated': ConversationUpdatedEvent
}

export interface AdminChannelEvents {
  '.guest.chat.started': GuestChatStartedEvent
}

// ============================================
// Custom Window Events
// ============================================

export interface ChatSelectConversationDetail {
  conversationId: number
}

export interface NotificationShowToastDetail {
  id?: string | number
  type: 'message' | 'notification' | 'friend_request'
  content?: string
  data?: Record<string, unknown>
  created_at?: string
  conversation_id?: number
  user?: IUser
}

export interface NewMessageArrivedDetail {
  message: IMessage
  isFromSelf: boolean
}

// Type augmentation for custom window events
declare global {
  interface WindowEventMap {
    'chat:select-conversation': CustomEvent<ChatSelectConversationDetail>
    'chat:new-message-arrived': CustomEvent<NewMessageArrivedDetail>
    'notification:show-toast': CustomEvent<NotificationShowToastDetail>
  }
}

// ============================================
// Utility Types
// ============================================

export type EventHandler<T> = (data: T) => void | Promise<void>

export interface SubscriptionOptions {
  onSubscribed?: () => void
  onError?: (error: unknown) => void
}
