/**
 * Chat Composables Index
 * Export all chat-related composables for easy importing
 */

// Core realtime functionality
export { useRealtime, useAutoConnect, useGlobalPolling } from './useRealtime'
export { useRealtimeCore, getEcho, disconnectEcho } from './useRealtimeCore'
export { useRealtimeEvents, dispatchMessageToast, dispatchGuestChatToast, dispatchSelectConversation, dispatchNewMessageArrived } from './useRealtimeEvents'
export { useGlobalPolling as useGlobalPollingStandalone } from './useGlobalPolling'

// Conversation info shared composable
export { useConversationInfo, useTypingIndicator, getInitials, getAvatarColorById, formatTypingText } from './useConversationInfo'
export type { ConversationInfo, TypingInfo } from './useConversationInfo'

// Voice recorder
export { useVoiceRecorder } from './useVoiceRecorder'
