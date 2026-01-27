/**
 * Global Polling Composable
 * Fallback polling mechanism when WebSocket is unreliable
 * Implements visibility API to stop polling when tab is hidden
 */
import { ref } from 'vue'
import { useChatStore } from '../stores/chatStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useAuthStore } from '@/stores/auth'
import { ChatService } from '../services/ChatService'

// ============================================
// State
// ============================================
let globalMessagePollingInterval: ReturnType<typeof setInterval> | null = null
const isPolling = ref(false)
const lastPollTime = ref<Date | null>(null)

// ============================================
// Visibility Handler
// ============================================
let visibilityHandler: (() => void) | null = null

function handleVisibilityChange(intervalMs: number, pollFn: () => Promise<void>): void {
  if (document.hidden) {
    // Stop polling when tab is hidden
    if (globalMessagePollingInterval) {
      clearInterval(globalMessagePollingInterval)
      globalMessagePollingInterval = null
    }
  } else {
    // Resume polling when tab becomes visible
    if (!globalMessagePollingInterval && isPolling.value) {
      pollFn() // Poll immediately when becoming visible
      globalMessagePollingInterval = setInterval(pollFn, intervalMs)
    }
  }
}

// ============================================
// Polling Functions
// ============================================

export function useGlobalPolling() {
  const chatStore = useChatStore()
  const notificationStore = useNotificationStore()
  const authStore = useAuthStore()

  /**
   * Poll for new messages and notifications
   */
  async function pollForNewMessages(): Promise<void> {
    // Only poll if window is visible and user is authenticated
    if (document.hidden || !authStore.user?.id) return

    try {
      // 1. Refresh conversation list to get unread counts/latest messages
      await chatStore.fetchConversations(1, true)

      // 2. If there's a current conversation, fetch latest messages for it
      if (chatStore.currentConversation) {
        const messages = await ChatService.getMessages(chatStore.currentConversation.id, 50)
        for (const msg of messages) {
          // handleNewMessage checks for duplicates internally
          await chatStore.handleNewMessage(msg)
        }
      }

      // 3. Refresh unread notification count
      await notificationStore.fetchUnreadCount()

      lastPollTime.value = new Date()
    } catch (error) {
      console.warn('Realtime: Global polling failed', error)
    }
  }

  /**
   * Start global polling with visibility-aware behavior
   * @param intervalMs Polling interval in milliseconds (default: 30000ms / 30s)
   */
  function startGlobalPolling(intervalMs = 30000): void {
    if (globalMessagePollingInterval) return

    isPolling.value = true

    // Initial poll
    pollForNewMessages()

    // Setup interval only if tab is visible
    if (!document.hidden) {
      globalMessagePollingInterval = setInterval(pollForNewMessages, intervalMs)
    }

    // Setup visibility change handler
    if (!visibilityHandler) {
      visibilityHandler = () => handleVisibilityChange(intervalMs, pollForNewMessages)
      document.addEventListener('visibilitychange', visibilityHandler)
    }
  }

  /**
   * Stop global polling and cleanup
   */
  function stopGlobalPolling(): void {
    if (globalMessagePollingInterval) {
      clearInterval(globalMessagePollingInterval)
      globalMessagePollingInterval = null
    }

    if (visibilityHandler) {
      document.removeEventListener('visibilitychange', visibilityHandler)
      visibilityHandler = null
    }

    isPolling.value = false
  }

  /**
   * Force an immediate poll
   */
  async function pollNow(): Promise<void> {
    await pollForNewMessages()
  }

  return {
    isPolling,
    lastPollTime,
    startGlobalPolling,
    stopGlobalPolling,
    pollNow
  }
}
