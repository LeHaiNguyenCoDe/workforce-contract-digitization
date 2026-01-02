import Echo from 'laravel-echo'
import httpClient from '@/plugins/api/httpClient'
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

// Initialize Pusher globally for Laravel Echo
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
            // console.log(`Realtime: Authorizing channel ${channel.name} with socket ${socketId}`)
            // Use httpClient which has the correct baseURL configured
            httpClient.post('/broadcasting/auth', {
              socket_id: socketId,
              channel_name: channel.name
            }, {
              headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
              }
            })
            .then((response: any) => {
              // console.log(`Realtime: Auth OK for ${channel.name} (Socket: ${socketId})`)
              // Pusher expects {auth: "..."} object
              if (response.data && (response.data.auth || typeof response.data === 'object')) {
                callback(false, response.data)
              } else {
                console.error('Realtime: Invalid auth response format:', response.data)
                callback(true, { error: 'Invalid response format' })
              }
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
      return states
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
      // console.log('Realtime: Already connected as user', userId)
      return
    }

    // console.log(`Realtime: Connecting as user ${userId}`)
    currentUserId.value = userId
    const echo = getEcho()

    // Subscribe to user's private channel for notifications
    echo.private(`user.${userId}`)
      .subscribed(() => {
      })
      .error((error: any) => {
        console.error(`Realtime: Error subscribing to user.${userId}`, error)
      })
      .listen('.notification.new', (data: INotification) => {
        notificationStore.handleNewNotification(data)
        AudioHelper.playMessageSound()
      })
      .listen('.message.sent', async (data: IMessage) => {
        const messageId = Number(data.id)
        const isListeningOnConversation = subscribedConversations.value.has(data.conversation_id)
        
        // Skip sender's own messages on this channel
        if (Number(data.user_id) === Number(userId)) {
          return
        }

        // If we are listening on the conversation channel, we don't need to process it here
        if (isListeningOnConversation) {
          return
        }

        if (processedMessageIds.has(messageId)) {
          return
        }
        processedMessageIds.add(messageId)

        await chatStore.handleNewMessage(data)
        
        window.dispatchEvent(new CustomEvent('notification:show-toast', { 
          detail: {
            ...data,
            type: 'message'
          }
        }))
        
        // Trigger browser notification if tab is hidden
        if (document.hidden) {
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
      // console.log('Realtime: Subscribing to admin.guest-chats')
      echo.private('admin.guest-chats')
        .listen('.guest.chat.started', (data: { conversation: IConversation }) => {
          // console.log('Realtime: Received guest.chat.started', data)
          // Add extra properties for filtering consistency if they come from PHP
          const conv = data.conversation
          if (conv && !chatStore.conversations.some(c => c.id === conv.id)) {
            // console.log(`Realtime: Adding new guest conversation ${conv.id} to list`)
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
            // console.log(`Realtime: Guest conversation ${conv?.id} already exists or invalid`)
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
    
    // console.log(`Realtime: Subscribing to conversation.${conversationId}`)
    echo.private(`conversation.${conversationId}`)
      .subscribed(() => {
        // console.log(`Realtime: Successfully subscribed to conversation.${conversationId}`)
      })
      .error((error: any) => {
        console.error(`Realtime: Error subscribing to conversation.${conversationId}`, error)
      })
      .listen('.message.sent', (data: IMessage) => {
        const messageId = Number(data.id)
        if (processedMessageIds.has(messageId)) {
          // console.log(`Realtime: Already processed message ${messageId}, skipping duplicate (Conv Channel)`)
          return
        }
        processedMessageIds.add(messageId)

        // console.log(`Realtime: Received message.sent on conversation.${conversationId}`, data)
        chatStore.handleNewMessage(data)
        
        // Skip notification for own messages
        if (Number(data.user_id) !== Number(currentUserId.value)) {
          AudioHelper.playMessageSound()
          
          // Dispatch toast notification event for NotificationBell
          window.dispatchEvent(new CustomEvent('notification:show-toast', { 
            detail: {
              ...data,
              type: 'message'
            }
          }))
          
          // Show browser notification if tab is hidden
          if (document.hidden) {
            // console.log('Realtime: Showing browser notification')
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

import { ChatService } from '../services/ChatService'

// ... existing code ... (at the end)

/**
 * Global polling monitor for messages when WebSocket is unreliable
 */
let globalMessagePollingInterval: ReturnType<typeof setInterval> | null = null

export function useGlobalPolling() {
  const chatStore = useChatStore()
  const authStore = useAuthStore()

  async function pollForNewMessages() {
    // Only poll if window is visible and user is authenticated
    if (document.hidden || !authStore.user?.id) return

    try {
      // 1. Refresh conversation list to get unread counts/latest messages
      await chatStore.fetchConversations(1, true)
      
      // 2. If there's a current conversation, fetch latest messages for it
      if (chatStore.currentConversation) {
        const messages = await ChatService.getMessages(chatStore.currentConversation.id, 50)
        for (const msg of messages) {
          // handleNewMessage checks for duplicates
          await chatStore.handleNewMessage(msg)
        }
      }
      
      // 3. Refresh unread notification count
      const notificationStore = useNotificationStore()
      await notificationStore.fetchUnreadCount()
      
      // console.log('Realtime: Global polling sync completed')
    } catch (error) {
      console.warn('Realtime: Global polling failed', error)
    }
  }

  function startGlobalPolling(intervalMs: number = 30000) {
    if (globalMessagePollingInterval) return
    
    // Initial poll
    pollForNewMessages()
    
    globalMessagePollingInterval = setInterval(pollForNewMessages, intervalMs)
  }

  function stopGlobalPolling() {
    if (globalMessagePollingInterval) {
      clearInterval(globalMessagePollingInterval)
      globalMessagePollingInterval = null
      // console.log('Realtime: Global polling stopped')
    }
  }

  return {
    startGlobalPolling,
    stopGlobalPolling
  }
}

/**
 * Auto-connect and auto-poll composable for use in Layouts
 */
export function useAutoConnect() {
  // console.log('Realtime: useAutoConnect called')
  const { connect, disconnect } = useRealtime()
  const { startGlobalPolling, stopGlobalPolling } = useGlobalPolling()
  const authStore = useAuthStore()
  
  onMounted(async () => {
    if (!authStore.isInitialized) {
      await authStore.fetchUser()
    }
    
    if (authStore.user?.id) {
      connect(authStore.user.id)
      // Start global polling as a fallback (Silent)
      startGlobalPolling(30000)
    }
  })

  onUnmounted(() => {
    disconnect()
    stopGlobalPolling()
  })
}
