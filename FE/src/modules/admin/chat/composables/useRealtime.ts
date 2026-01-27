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
import { useCallStore } from '../stores/callStore'

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
  const callStore = useCallStore()
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
    console.log(`Realtime: Subscribing to user.${userId}`)
    echo.private(`user.${userId}`)
      .subscribed(() => {
        console.log(`Realtime: Successfully subscribed to user.${userId}`)
      })
      .error((error: any) => {
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
      .listen('.call.signal', async (data: any) => {
        console.log('Realtime: Received call signal', data.type)
        if (data.type === 'offer') {
          // Store already contains caller info from status_changed 'ringing'
          await callStore.handleOffer(data.payload)
        } else if (data.type === 'answer') {
          await callStore.handleAnswer(data.payload)
        } else if (data.type === 'ice-candidate') {
          await callStore.handleCandidate(data.payload)
        }
      })
      .listen('.call.status_changed', async (data: any) => {
        console.log('Realtime: Call status changed event received:', data.status, data)
        const isSelf = Number(data.from_user_id) === Number(userId)
        
        if (data.status === 'ringing') {
          if (isSelf) return // Ignore own ringing signals

          // Try various ways to find caller info
          let caller = (chatStore.currentConversationPartner && chatStore.currentConversationPartner.id === data.from_user_id) 
                         ? chatStore.currentConversationPartner 
                         : null
          
          if (!caller) {
            // Search in all conversations
            for (const conv of chatStore.conversations) {
              const found = conv.users.find(u => u.id === data.from_user_id)
              if (found) {
                caller = found
                break
              }
            }
          }
          
          if (!caller) {
            console.warn('Realtime: Caller info not found in store, using fallback data')
            // Fallback: minimal user object if not found
            caller = { id: data.from_user_id, name: 'Someone' } as any
          }

          callStore.handleIncomingCall({
            conversation_id: data.conversation_id,
            from_user_id: data.from_user_id,
            call_type: data.call_type,
            caller: caller as any
          })
        } else if (data.status === 'accepted') {
          if (isSelf) return 
          
          console.log('Realtime: Call accepted by partner:', data.from_user_id)
          callStore.callStatus = 'active'
          callStore.startTimer()
          
          if (!callStore.localStream) {
            console.log('Realtime: Starting local stream on accepted (caller side)')
            await callStore.startLocalStream()
          }
        } else if (['rejected', 'busy', 'ended'].includes(data.status)) {
          console.log(`Realtime: Processing ${data.status} signal from ${data.from_user_id} (isSelf: ${isSelf})`)
          if (isSelf) {
            console.log(`Realtime: Ignoring self-emitted ${data.status} signal`)
            return
          }
          console.log(`Realtime: Call ${data.status} by partner`)
          callStore.resetCall(`partner_${data.status}`)
        }
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

    const result = await chatStore.handleNewMessage(data)
    dispatchMessageToast(data)

    // Trigger browser notification if tab is hidden
    if (document.hidden && !result.isMuted) {
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
      .error((error: any) => {
        console.error(`Realtime: Error subscribing to conversation.${conversationId}`, error)
      })
      .listen('.message.sent', (data: IMessage) => {
        handleConversationMessage(data, conversationId)
      })
      .listen('.user.typing', (data: TypingEvent) => {
        chatStore.handleUserTyping(conversationId, data.user_id, data.user_name, data.is_typing)
      })
      .listen('.message.read', (data: { user_id: number; message_id: number }) => {
        chatStore.handleMessageRead(data.user_id, data.message_id)
      })

    // Subscribe to presence channel
    const echoAny = echo as any
    echoAny.join(`presence.conversation.${conversationId}`)
      .here((users: Array<{ id: number; name: string }>) => {
        chatStore.setUsersOnline(users.map(u => u.id))
      })
      .joining((user: { id: number; name: string }) => {
        chatStore.setUserOnline(user.id)
      })
      .leaving((user: { id: number; name: string }) => {
        chatStore.setUserOffline(user.id)
      })
      .error((error: any) => {
        console.error(`Realtime: Presence error for conversation.${conversationId}`, error)
      })

    subscribedConversations.value.add(conversationId)
  }

  /**
   * Handle message received on conversation channel
   */
  async function handleConversationMessage(data: IMessage, conversationId: number): Promise<void> {
    const messageId = Number(data.id)

    // Check for duplicates
    if (processedMessageIds.has(messageId)) {
      return
    }
    processedMessageIds.add(messageId)

    const result = await chatStore.handleNewMessage(data)

    // Skip notification for own messages
    if (Number(data.user_id) !== Number(currentUserId.value)) {
      if (!result.isMuted) {
        AudioHelper.playMessageSound()
      }
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
    echo.leave(`presence.conversation.${conversationId}`)
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
      echo.leave(`presence.conversation.${convId}`)
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
    console.log('useAutoConnect: Mounting...')
    if (!authStore.isInitialized) {
      await authStore.fetchUser()
    }

    if (authStore.user?.id) {
      console.log('useAutoConnect: Connecting user', authStore.user.id)
      connect(authStore.user.id)
      // Start global polling as a fallback
      startGlobalPolling(30000)
    } else {
      console.warn('useAutoConnect: No user found after fetch')
    }
  })

  onUnmounted(() => {
    disconnect()
    stopGlobalPolling()
  })
}
