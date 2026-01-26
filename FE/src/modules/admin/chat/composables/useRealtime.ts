/**
 * Real-time Chat Composable
 * Handles WebSocket subscriptions for user and conversation channels
 *
 * Refactored from 447 lines to ~200 lines by extracting:
 * - useRealtimeCore.ts - Echo instance management
 * - useRealtimeEvents.ts - Event dispatching
 * - useGlobalPolling.ts - Polling fallback
 * - LRUSet.ts - Memory-safe message deduplication
 */
import { ref, onMounted, onUnmounted } from 'vue'
import { useChatStore } from '../stores/chatStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useAuthStore } from '@/stores/auth'
import type { IConversation, IMessage, INotification } from '../models/Chat'
import type { TypingEvent, FriendRequestEvent, GuestChatStartedEvent } from '../types/events'
import { NotificationHelper } from '../helpers/notificationHelper'
import { AudioHelper } from '../helpers/audioHelper'
import { getEcho, disconnectEcho, useRealtimeCore } from './useRealtimeCore'
import { dispatchMessageToast, dispatchGuestChatToast, dispatchSelectConversation } from './useRealtimeEvents'
import { processedMessageIds } from '../utils/LRUSet'
import { useGlobalPolling } from './useGlobalPolling'

// ============================================
// State
// ============================================
const currentUserId = ref<number | null>(null)
const subscribedConversations = ref<Set<number>>(new Set())

// ============================================
// Main Composable
// ============================================

export function useRealtime() {
  const chatStore = useChatStore()
  const notificationStore = useNotificationStore()
  const { isConnected } = useRealtimeCore()

  /**
   * Connect to the real-time server and subscribe to user channel
   */
  function connect(userId: number): void {
    if (!userId) {
      console.error('Realtime: Cannot connect without userId')
      return
    }

    if (isConnected.value && currentUserId.value === userId) {
      return
    }

    currentUserId.value = userId
    const echo = getEcho()

    // Subscribe to user's private channel for notifications
    echo.private(`user.${userId}`)
      .subscribed(() => {
        // Connected successfully
      })
      .error((error: unknown) => {
        console.error(`Realtime: Error subscribing to user.${userId}`, error)
      })
      .listen('.notification.new', (data: INotification) => {
        notificationStore.handleNewNotification(data)
        AudioHelper.playMessageSound()
      })
      .listen('.message.sent', async (data: IMessage) => {
        await handleUserChannelMessage(data, userId)
      })
      .listen('.friend.request', (data: FriendRequestEvent) => {
        handleFriendRequest(data, userId)
      })

    // Subscribe to admin-specific guest chat notifications
    subscribeToAdminChannel(echo)
  }

  /**
   * Handle message received on user channel
   */
  async function handleUserChannelMessage(data: IMessage, userId: number): Promise<void> {
    const messageId = Number(data.id)
    const isListeningOnConversation = subscribedConversations.value.has(data.conversation_id)

    // Skip sender's own messages on this channel
    if (Number(data.user_id) === Number(userId)) {
      return
    }

    // If we are listening on the conversation channel, skip processing here
    if (isListeningOnConversation) {
      return
    }

    // Check for duplicates using LRU Set
    if (processedMessageIds.has(messageId)) {
      return
    }
    processedMessageIds.add(messageId)

    await chatStore.handleNewMessage(data)
    dispatchMessageToast(data)

    // Trigger browser notification if tab is hidden
    if (document.hidden) {
      AudioHelper.playMessageSound()
      NotificationHelper.showNotification(data.user?.name || 'New Message', {
        body: typeof data.content === 'string' ? data.content : 'Sent an attachment',
        tag: `chat-${data.conversation_id}`,
        onClick: () => dispatchSelectConversation(data.conversation_id)
      })
    }
  }

  /**
   * Handle friend request notification
   */
  function handleFriendRequest(data: FriendRequestEvent, userId: number): void {
    notificationStore.handleNewNotification({
      id: String(data.id || crypto.randomUUID()),
      user_id: userId,
      type: 'friend_request',
      notifiable_type: 'Friendship',
      notifiable_id: data.id,
      data: data,
      read_at: null,
      created_at: new Date().toISOString()
    })
  }

  /**
   * Subscribe to admin channel for guest chats
   */
  function subscribeToAdminChannel(echo: ReturnType<typeof getEcho>): void {
    const authStore = useAuthStore()
    const userRole = authStore.user?.role
    const userRoles = authStore.user?.roles as Array<{ name?: string } | string> | undefined
    const adminRoles = ['admin', 'manager', 'super_admin']

    let isAdmin = adminRoles.includes(userRole || '')
    if (!isAdmin && Array.isArray(userRoles)) {
      isAdmin = userRoles.some((r) => adminRoles.includes(typeof r === 'string' ? r : r.name || ''))
    }

    if (isAdmin) {
      echo.private('admin.guest-chats')
        .listen('.guest.chat.started', (data: GuestChatStartedEvent) => {
          const conv = data.conversation
          if (conv && !chatStore.conversations.some(c => c.id === conv.id)) {
            chatStore.conversations.unshift(conv)
            AudioHelper.playMessageSound()
            dispatchGuestChatToast(conv)
          }
        })
    }
  }

  /**
   * Subscribe to a conversation's messages
   */
  function subscribeToConversation(conversationId: number): void {
    if (subscribedConversations.value.has(conversationId)) {
      return
    }

    const echo = getEcho()

    echo.private(`conversation.${conversationId}`)
      .subscribed(() => {
        // Subscribed successfully
      })
      .error((error: unknown) => {
        console.error(`Realtime: Error subscribing to conversation.${conversationId}`, error)
      })
      .listen('.message.sent', (data: IMessage) => {
        handleConversationMessage(data, conversationId)
      })
      .listen('.user.typing', (data: TypingEvent) => {
        chatStore.handleUserTyping(conversationId, data.user_id, data.user_name, data.is_typing)
      })

    subscribedConversations.value.add(conversationId)
  }

  /**
   * Handle message received on conversation channel
   */
  function handleConversationMessage(data: IMessage, conversationId: number): void {
    const messageId = Number(data.id)

    // Check for duplicates
    if (processedMessageIds.has(messageId)) {
      return
    }
    processedMessageIds.add(messageId)

    chatStore.handleNewMessage(data)

    // Skip notification for own messages
    if (Number(data.user_id) !== Number(currentUserId.value)) {
      AudioHelper.playMessageSound()
      dispatchMessageToast(data)

      // Show browser notification if tab is hidden
      if (document.hidden) {
        NotificationHelper.showNotification(data.user?.name || 'New Message', {
          body: typeof data.content === 'string' ? data.content : 'Sent an attachment',
          tag: `chat-${data.conversation_id}`,
          onClick: () => dispatchSelectConversation(data.conversation_id)
        })
      }
    }
  }

  /**
   * Unsubscribe from a conversation
   */
  function unsubscribeFromConversation(conversationId: number): void {
    if (!subscribedConversations.value.has(conversationId)) {
      return
    }

    const echo = getEcho()
    echo.leave(`conversation.${conversationId}`)
    subscribedConversations.value.delete(conversationId)
    chatStore.clearTypingUsers()
  }

  /**
   * Disconnect from all channels
   */
  function disconnect(): void {
    const echo = getEcho()

    // Leave all conversation channels
    subscribedConversations.value.forEach(convId => {
      echo.leave(`conversation.${convId}`)
    })
    subscribedConversations.value.clear()

    // Leave user channel
    if (currentUserId.value) {
      echo.leave(`user.${currentUserId.value}`)
    }

    // Leave admin channel
    echo.leave('admin.guest-chats')

    // Disconnect Echo
    disconnectEcho()
    currentUserId.value = null
    processedMessageIds.clear()
  }

  /**
   * Send typing indicator
   */
  function sendTypingIndicator(conversationId: number, isTyping: boolean): void {
    const echo = getEcho()
    echo.private(`conversation.${conversationId}`)
      .whisper('typing', { is_typing: isTyping })
  }

  return {
    isConnected,
    connect,
    disconnect,
    subscribeToConversation,
    unsubscribeFromConversation,
    sendTypingIndicator
  }
}

// ============================================
// Auto-Connect Composable
// ============================================

/**
 * Auto-connect and auto-poll composable for use in Layouts
 */
export function useAutoConnect() {
  const { connect, disconnect } = useRealtime()
  const { startGlobalPolling, stopGlobalPolling } = useGlobalPolling()
  const authStore = useAuthStore()

  onMounted(async () => {
    if (!authStore.isInitialized) {
      await authStore.fetchUser()
    }

    if (authStore.user?.id) {
      connect(authStore.user.id)
      // Start global polling as a fallback
      startGlobalPolling(30000)
    }
  })

  onUnmounted(() => {
    disconnect()
    stopGlobalPolling()
  })
}

// Re-export for backward compatibility
export { useGlobalPolling } from './useGlobalPolling'
