import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { ref, onMounted, onUnmounted } from 'vue'
import { useChatStore } from '../stores/chatStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useAuthStore } from '@/stores/auth' // Adjusted import path if needed, verified via grep
import type { IMessage, INotification } from '../models/Chat'
import { NotificationHelper } from '../helpers/notificationHelper'

// Make Pusher available globally for Echo
declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo<any>
  }
}

// Initialize Pusher
window.Pusher = Pusher

let echoInstance: Echo<any> | null = null

/**
 * Get or create Echo instance
 */
export function getEcho(): Echo<any> {
  if (!echoInstance) {
    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY || 'workforce-chat-key',
      wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
      wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
      wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: '/broadcasting/auth',
      auth: {
        headers: {
          'X-XSRF-TOKEN': getCsrfToken()
        }
      }
    })

    // Debug connection events
    echoInstance.connector.pusher.connection.bind('state_change', (states: any) => {
      console.log('Echo connection state change:', states)
    })

    echoInstance.connector.pusher.connection.bind('error', (err: any) => {
      console.error('Echo connection error:', err)
    })
  }
  return echoInstance
}

function getCsrfToken(): string {
  const token = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
  return token ? decodeURIComponent(token) : ''
}

/**
 * Composable for real-time chat functionality
 */
export function useRealtime() {
  const chatStore = useChatStore()
  const notificationStore = useNotificationStore()
  const authStore = useAuthStore()
  
  const isConnected = ref(false)
  const currentUserId = ref<number | null>(null)
  const subscribedConversations = ref<Set<number>>(new Set())

  /**
   * Initialize realtime connection
   */
  function connect(userId: number) {
    if (!userId) {
      console.error('Realtime: Cannot connect without userId')
      return
    }
    console.log(`Realtime: Connecting as user ${userId}`)
    currentUserId.value = userId
    const echo = getEcho()

    // Subscribe to user's private channel for notifications
    console.log(`Realtime: Subscribing to user.${userId}`)
    echo.private(`user.${userId}`)
      .listen('.notification.new', (data: INotification) => {
        notificationStore.handleNewNotification(data)
      })
      .listen('.message.sent', (data: IMessage) => {
        console.log('Realtime: Received message.sent on user channel', data)
        // Handle message globally (for unread counts and browser notifications)
        if (Number(data.user_id) !== Number(userId)) {
          console.log('Realtime: Processing global notification for message')
          chatStore.handleNewMessage(data)
          
          // Trigger browser notification if not currently viewing this conversation or tab is hidden
          if (chatStore.currentConversation?.id !== data.conversation_id || document.hidden) {
            console.log('Realtime: Showing browser notification')
            NotificationHelper.showNotification(data.user?.name || 'New Message', {
              body: typeof data.content === 'string' ? data.content : 'Sent an attachment',
              tag: `chat-${data.conversation_id}`,
              onClick: () => {
                window.dispatchEvent(new CustomEvent('chat:select-conversation', { 
                  detail: { conversationId: data.conversation_id } 
                }))
              }
            })
          }
        }
      })
      .listen('.friend.request', (data: any) => {
        // Handle friend request notification
        notificationStore.handleNewNotification({
          id: data.id || crypto.randomUUID(),
          user_id: userId,
          type: 'friend_request',
          notifiable_type: 'Friendship',
          notifiable_id: data.id,
          data: data,
          read_at: null,
          created_at: new Date().toISOString()
        })
      })

    isConnected.value = true
  }

  /**
   * Subscribe to a conversation's messages
   */
  function subscribeToConversation(conversationId: number) {
    if (subscribedConversations.value.has(conversationId)) {
      return
    }

    const echo = getEcho()
    
    console.log(`Realtime: Subscribing to conversation.${conversationId}`)
    echo.private(`conversation.${conversationId}`)
      .subscribed(() => {
        console.log(`Realtime: Successfully subscribed to conversation.${conversationId}`)
      })
      .error((error: any) => {
        console.error(`Realtime: Error subscribing to conversation.${conversationId}`, error)
      })
      .listen('.message.sent', (data: IMessage) => {
        console.log(`Realtime: Received message.sent on conversation.${conversationId}`, data)
        chatStore.handleNewMessage(data)
      })
      .listen('.user.typing', (data: { user_id: number; user_name: string; is_typing: boolean }) => {
        chatStore.handleUserTyping(conversationId, data.user_id, data.user_name, data.is_typing)
      })

    subscribedConversations.value.add(conversationId)
  }

  /**
   * Unsubscribe from a conversation
   */
  function unsubscribeFromConversation(conversationId: number) {
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
  function disconnect() {
    if (!echoInstance) return

    // Leave all conversation channels
    subscribedConversations.value.forEach(convId => {
      echoInstance?.leave(`conversation.${convId}`)
    })
    subscribedConversations.value.clear()

    // Leave user channel
    if (currentUserId.value) {
      echoInstance.leave(`user.${currentUserId.value}`)
    }

    isConnected.value = false
    currentUserId.value = null
  }

  /**
   * Send typing indicator
   */
  function sendTypingIndicator(conversationId: number, isTyping: boolean) {
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

/**
 * Auto-connect composable for use in components
 */
export function useAutoConnect() {
  const { connect, disconnect } = useRealtime()
  const authStore = useAuthStore()
  
  onMounted(async () => {
    if (!authStore.isInitialized) {
      await authStore.fetchUser()
    }
    
    if (authStore.user?.id) {
      connect(authStore.user.id)
    } else {
      console.warn('Realtime: AutoConnect failed, no user found in authStore')
    }
  })

  onUnmounted(() => {
    disconnect()
  })
}
