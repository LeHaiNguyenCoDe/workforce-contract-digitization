/**
 * Landing Chat Store
 * Manages guest chat state and session persistence
 */
import { GuestChatService, type IGuestMessage, type IGuestInfo } from '../services/GuestChatService'

const SESSION_KEY = 'guest_chat_session'

export const useLandingChatStore = defineStore('landingChat', () => {
  // State
  const isOpen = ref(false)
  const isLoading = ref(false)
  const isSending = ref(false)
  const isTyping = ref(false)
  const sessionToken = ref<string | null>(null)
  const guestInfo = ref<IGuestInfo | null>(null)
  const assignedStaff = ref<{ name: string; avatar?: string } | null>(null)
  const messages = ref<IGuestMessage[]>([])
  const unreadCount = ref(0)

  // Computed
  const hasSession = computed(() => !!sessionToken.value && !!guestInfo.value)

  // Initialize from localStorage
  function init() {
    const stored = localStorage.getItem(SESSION_KEY)
    if (stored) {
      try {
        const data = JSON.parse(stored)
        sessionToken.value = data.sessionToken
        guestInfo.value = data.guestInfo
        assignedStaff.value = data.assignedStaff || null
      } catch {
        localStorage.removeItem(SESSION_KEY)
      }
    }
  }

  // Save session to localStorage
  function saveSession() {
    if (sessionToken.value && guestInfo.value) {
      localStorage.setItem(SESSION_KEY, JSON.stringify({
        sessionToken: sessionToken.value,
        guestInfo: guestInfo.value,
        assignedStaff: assignedStaff.value
      }))
    }
  }

  // Actions
  function toggleChat() {
    isOpen.value = !isOpen.value
    if (isOpen.value && hasSession.value) {
      unreadCount.value = 0
    }
  }

  async function startSession(info: { name: string; contact: string; contactType: 'email' | 'phone' }) {
    isLoading.value = true
    try {
      const response = await GuestChatService.startSession(info.name, info.contact, info.contactType)
      sessionToken.value = response.session_token
      guestInfo.value = { name: info.name, contact: info.contact }
      
      // Load initial staff info
      await loadSessionInfo()
      saveSession()
    } finally {
      isLoading.value = false
    }
  }

  async function loadSessionInfo() {
    if (!sessionToken.value) return
    try {
      const info = await GuestChatService.getSessionInfo(sessionToken.value)
      if (info.staff) {
        assignedStaff.value = {
          name: info.staff.name,
          avatar: info.staff.avatar
        }
      }
    } catch {
      // Ignore info errors
    }
  }

  async function loadMessages(silent = false) {
    if (!sessionToken.value) return
    
    // Only show loading indicator on initial load, not on polling
    if (!silent) {
      isLoading.value = true
    }
    try {
      const data = await GuestChatService.getMessages(sessionToken.value)
      
      // Update session info from response
      if (data.session?.assigned_staff) {
        assignedStaff.value = {
          name: data.session.assigned_staff.name,
          avatar: data.session.assigned_staff.avatar
        }
      } else {
        assignedStaff.value = null
      }

      // Smart update messages
      const newMessages = data.messages || []
      const currentLastId = messages.value.length > 0 ? messages.value[messages.value.length - 1].id : null
      const newLastId = newMessages.length > 0 ? newMessages[newMessages.length - 1].id : null
      
      if (currentLastId !== newLastId || messages.value.length !== newMessages.length) {
        messages.value = newMessages
      }
    } catch {
      // Session might be expired
      clearSession()
    } finally {
      if (!silent) {
        isLoading.value = false
      }
    }
  }

  async function sendMessage(content: string, files?: File[]) {
    if (!sessionToken.value) return
    
    isSending.value = true
    try {
      const savedMessage = await GuestChatService.sendMessage(sessionToken.value, content, files)
      messages.value.push(savedMessage)
      
      // Update activity
      saveSession()
    } finally {
      isSending.value = false
    }
  }

  // Handle incoming message from staff (via WebSocket/polling)
  function handleNewMessage(message: IGuestMessage) {
    // Avoid duplicates
    if (!messages.value.find(m => m.id === message.id)) {
      messages.value.push(message)
      if (!isOpen.value) {
        unreadCount.value++
      }
    }
  }

  function setTyping(typing: boolean) {
    isTyping.value = typing
  }

  function clearSession() {
    sessionToken.value = null
    guestInfo.value = null
    assignedStaff.value = null
    messages.value = []
    localStorage.removeItem(SESSION_KEY)
  }

  function checkProductNavigation(content: string): number | null {
    if (messages.value.length === 0) return null
    
    const lastMsg = messages.value[messages.value.length - 1]
    
    // Check if last message is from staff/bot and has product_id
    // Note: guest messages have is_guest=true. Staff/Bot have is_guest=false.
    if (!lastMsg.is_guest && lastMsg.metadata?.product_id) {
       const lower = content.toLowerCase().trim()
       const keywords = ['có', 'ok', 'yes', 'yep', 'ừ', 'uh', 'được', 'muốn', 'quan tâm', 'chi tiết', 'xem', 'hãy']
       if (keywords.some(k => lower.includes(k))) {
         return Number(lastMsg.metadata.product_id)
       }
    }
    return null
  }

  // Initialize on store creation
  init()

  return {
    // State
    isOpen,
    isLoading,
    isSending,
    isTyping,
    sessionToken,
    guestInfo,
    assignedStaff,
    messages,
    unreadCount,
    
    // Computed
    hasSession,
    
    // Actions
    toggleChat,
    startSession,
    loadSessionInfo,
    loadMessages,
    sendMessage,
    handleNewMessage,
    setTyping,
    clearSession,
    checkProductNavigation
  }
})
