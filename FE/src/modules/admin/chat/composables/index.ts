/**
 * Chat Composables Index
 * Export all chat-related composables for easy importing
 *
 * Note: Only export public composables that are used by components.
 * Internal utilities (useGlobalPolling, useRealtimeCore, useRealtimeEvents)
 * are not exported to avoid duplicate imports.
 */

// Core realtime functionality
export { useRealtime, useAutoConnect } from './useRealtime'

// Conversation info shared composable
export { useConversationInfo, useTypingIndicator } from './useConversationInfo'
export type { ConversationInfo, TypingInfo } from './useConversationInfo'

// Voice recorder
export { useVoiceRecorder } from './useVoiceRecorder'

// Conversation details sidebar functionality
export { useConversationDetails } from './useConversationDetails'
export type { ConversationDetailsState, SharedMedia } from './useConversationDetails'
