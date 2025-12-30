import Echo from 'laravel-echo'
import axios from 'axios'
import Pusher from 'pusher-js'
import { ref, onMounted, onUnmounted } from 'vue'
import { useChatStore } from '../stores/chatStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useAuthStore } from '@/stores/auth' // Adjusted import path if needed, verified via grep
import type { IConversation, IMessage, INotification } from '../models/Chat'
import { NotificationHelper } from '../helpers/notificationHelper'
import { AudioHelper } from '../helpers/audioHelper'

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
const isConnected = ref(false)
const currentUserId = ref<number | null>(null)
const subscribedConversations = ref<Set<number>>(new Set())
const processedMessageIds = new Set<number>()

// Clear processed IDs periodically to prevent memory leaks
setInterval(() => {
    if (processedMessageIds.size > 100) {
        processedMessageIds.clear()
    }
}, 60000)

/**
 * Get or create Echo instance
 */
export function getEcho(): Echo<any> {
  if (!echoInstance) {
    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY || 'workforce-chat-key',
      wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
      wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
      wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: '/api/v1/broadcasting/auth',
      // Ensure cookies are sent for authorization
      withCredentials: true,
      authorizer: (channel: any) => {
        return {
          authorize: (socketId: string, callback: Function) => {
            console.log(`Realtime: Authorizing channel ${channel.name} with socket ${socketId}`)
            // Use axios to see the RAW response body
            axios.post('/api/v1/broadcasting/auth', {
              socket_id: socketId,
              channel_name: channel.name
            }, {
              withCredentials: true,
              headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
              }
            })
            .then((response: any) => {
              // console.log('Realtime: Auth Response Status:', response.status)
              if (!response.data || typeof response.data !== 'object') {
                console.error('Realtime: Invalid JSON response from auth endpoint:', response.data)
              }
              callback(false, response.data)
            })
            .catch((error: any) => {
              console.error(`Realtime: Failed authorizing ${channel.name}`, error.response?.status, error.response?.data)
              callback(true, error)
            })
          }
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

  /**
   * Connect to the real-time server
   */
  function connect(userId: number) {
    if (!userId) {
      console.error('Realtime: Cannot connect without userId')
      return
    }

    if (isConnected.value && currentUserId.value === userId) {
      console.log('Realtime: Already connected as user', userId)
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
        AudioHelper.playMessageSound()
      })
      .listen('.message.sent', async (data: IMessage) => {
        console.log('Realtime: Received message.sent on user channel', data)
        const isGuest = data.metadata?.is_guest || (data as any).is_guest
        console.log(`Realtime: Message isGuest: ${isGuest}, conversation_id: ${data.conversation_id}`)
        
        const messageId = Number(data.id)
        const isListeningOnConversation = subscribedConversations.value.has(data.conversation_id)
        
        // Skip sender's own messages on this channel
        if (Number(data.user_id) === Number(userId)) {
          return
        }

        // If we are listening on the conversation channel, we don't need to process it here
        if (isListeningOnConversation) {
          console.log(`Realtime: Skipping user channel processing for message ${messageId} as we are listening on conversation.${data.conversation_id}`)
          return
        }

        if (processedMessageIds.has(messageId)) {
          console.log(`Realtime: Already processed message ${messageId}, skipping duplicate (User Channel)`)
          return
        }
        processedMessageIds.add(messageId)

        console.log('Realtime: Processing global notification for message (User Channel)')
        await chatStore.handleNewMessage(data)
        
        // Dispatch global event for NotificationBell toast
        window.dispatchEvent(new CustomEvent('notification:show-toast', { 
          detail: {
            ...data,
            type: 'message'
          }
        }))
        
        // Trigger browser notification if tab is hidden
        if (document.hidden) {
          console.log('Realtime: Showing browser notification')
          AudioHelper.playMessageSound()
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

    // Subscribe to admin-specific guest chat notifications
    const userRole = useAuthStore().user?.role
    const userRoles = useAuthStore().user?.roles
    const adminRoles = ['admin', 'manager', 'super_admin']
    
    let isAdmin = adminRoles.includes(userRole || '')
    if (!isAdmin && Array.isArray(userRoles)) {
      isAdmin = userRoles.some((r: any) => adminRoles.includes(r.name || r))
    }

    if (isAdmin) {
      console.log('Realtime: Subscribing to admin.guest-chats')
      echo.private('admin.guest-chats')
        .listen('.guest.chat.started', (data: { conversation: IConversation }) => {
          console.log('Realtime: Received guest.chat.started', data)
          // Add extra properties for filtering consistency if they come from PHP
          const conv = data.conversation
          if (conv && !chatStore.conversations.some(c => c.id === conv.id)) {
            console.log(`Realtime: Adding new guest conversation ${conv.id} to list`)
            chatStore.conversations.unshift(conv)
            AudioHelper.playMessageSound()
            
            // Dispatch global event for NotificationBell toast
            window.dispatchEvent(new CustomEvent('notification:show-toast', { 
              detail: {
                id: `guest_${conv.id}`,
                type: 'message',
                data: {
                  content: `Guest ${conv.guest_session?.guest_name || 'New Guest'} started a chat`,
                  conversation_id: conv.id
                },
                created_at: new Date().toISOString()
              }
            }))
          } else {
            console.log(`Realtime: Guest conversation ${conv?.id} already exists or invalid`)
          }
        })
    }

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
        const messageId = Number(data.id)
        if (processedMessageIds.has(messageId)) {
          console.log(`Realtime: Already processed message ${messageId}, skipping duplicate (Conv Channel)`)
          return
        }
        processedMessageIds.add(messageId)

        console.log(`Realtime: Received message.sent on conversation.${conversationId}`, data)
        chatStore.handleNewMessage(data)
        
        // Suppress sound for own messages
        if (Number(data.user_id) !== Number(currentUserId.value)) {
            AudioHelper.playMessageSound()
        }
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
    processedMessageIds.clear()
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
