/**
 * Real-time Events Composable
 * Centralized event dispatching for chat notifications
 */
import type { IMessage, IConversation } from '../models/Chat'
import type { NotificationShowToastDetail, NewMessageArrivedDetail, ChatSelectConversationDetail } from '../types/events'

/**
 * Dispatch a toast notification event
 */
export function dispatchToastNotification(data: NotificationShowToastDetail): void {
  window.dispatchEvent(new CustomEvent('notification:show-toast', { detail: data }))
}

/**
 * Dispatch a message toast notification
 */
export function dispatchMessageToast(message: IMessage): void {
  dispatchToastNotification({
    id: message.id,
    type: 'message',
    content: typeof message.content === 'string' ? message.content : 'Sent an attachment',
    conversation_id: message.conversation_id,
    user: message.user,
    created_at: message.created_at
  })
}

/**
 * Dispatch a guest chat notification
 */
export function dispatchGuestChatToast(conversation: IConversation): void {
  const guestName = conversation.guest_session?.guest_name || 'New Guest'
  dispatchToastNotification({
    id: `guest_${conversation.id}`,
    type: 'message',
    content: `Guest ${guestName} started a chat`,
    data: { conversation_id: conversation.id },
    created_at: new Date().toISOString()
  })
}

/**
 * Dispatch new message arrived event for UI updates
 */
export function dispatchNewMessageArrived(message: IMessage, isFromSelf: boolean): void {
  const detail: NewMessageArrivedDetail = { message, isFromSelf }
  window.dispatchEvent(new CustomEvent('chat:new-message-arrived', { detail }))
}

/**
 * Dispatch select conversation event
 */
export function dispatchSelectConversation(conversationId: number): void {
  const detail: ChatSelectConversationDetail = { conversationId }
  window.dispatchEvent(new CustomEvent('chat:select-conversation', { detail }))
}

/**
 * Composable for real-time events
 */
export function useRealtimeEvents() {
  return {
    dispatchToastNotification,
    dispatchMessageToast,
    dispatchGuestChatToast,
    dispatchNewMessageArrived,
    dispatchSelectConversation
  }
}
