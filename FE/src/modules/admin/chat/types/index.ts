/**
 * Chat Types Index
 * Export all chat-related types
 */

// Event types for real-time
export type {
  EchoConfig,
  EchoChannel,
  ChannelAuthorizer,
  AuthCallback,
  AuthResponse,
  ConnectionState,
  ConnectionStatus,
  MessageSentEvent,
  MessageSentData,
  TypingEvent,
  NotificationNewEvent,
  FriendRequestEvent,
  GuestChatStartedEvent,
  ConversationUpdatedEvent,
  UserStatusEvent,
  MessageReadEvent,
  MessageDeletedEvent,
  UserChannelEvents,
  ConversationChannelEvents,
  AdminChannelEvents,
  ChatSelectConversationDetail,
  NotificationShowToastDetail,
  NewMessageArrivedDetail,
  EventHandler,
  SubscriptionOptions
} from './events'

// Re-export model types for convenience
export type {
  IUser,
  IConversation,
  IConversationUser,
  IMessage,
  IMessageReply,
  IMessageAttachment,
  IFriendship,
  INotification,
  ISendMessageForm,
  ICreateGroupForm,
  IPaginatedResponse,
  IApiResponse
} from '../models/Chat'
