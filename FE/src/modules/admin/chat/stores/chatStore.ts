import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { IConversation, IMessage, IUser } from '../models/Chat'
import { ChatService } from '../services/ChatService'

export const useChatStore = defineStore('chat', () => {
  // State
  const conversations = ref<IConversation[]>([])
  const currentConversation = ref<IConversation | null>(null)
  const messages = ref<IMessage[]>([])
  const isLoadingConversations = ref(false)
  const isLoadingMessages = ref(false)
  const isSendingMessage = ref(false)
  const hasMoreMessages = ref(true)
  const typingUsers = ref<Map<number, { userId: number; userName: string }>>(new Map())

  // Getters
  const totalUnreadCount = computed(() => {
    return conversations.value.reduce((sum, conv) => sum + (conv.unread_count || 0), 0)
  })

  const sortedConversations = computed(() => {
    return [...conversations.value].sort((a, b) => {
      // Pinned first
      const aPinned = a.pivot?.is_pinned ? 1 : 0
      const bPinned = b.pivot?.is_pinned ? 1 : 0
      if (aPinned !== bPinned) return bPinned - aPinned

      // Then by latest message
      const aDate = a.latest_message?.created_at || a.updated_at
      const bDate = b.latest_message?.created_at || b.updated_at
      return new Date(bDate).getTime() - new Date(aDate).getTime()
    })
  })

  const currentConversationPartner = computed(() => {
    if (!currentConversation.value || currentConversation.value.type !== 'private') {
      return null
    }
    // Return the other user in a private conversation
    const currentUserId = parseInt(localStorage.getItem('userId') || '0')
    return currentConversation.value.users.find(u => u.id !== currentUserId) || null
  })

  // Actions
  async function fetchConversations(page = 1) {
    isLoadingConversations.value = true
    try {
      const response = await ChatService.getConversations(page)
      if (page === 1) {
        conversations.value = response.data
      } else {
        conversations.value.push(...response.data)
      }
      return response
    } finally {
      isLoadingConversations.value = false
    }
  }

  async function fetchMessages(conversationId: number, beforeId?: number) {
    isLoadingMessages.value = true
    try {
      const newMessages = await ChatService.getMessages(conversationId, 50, beforeId)
      if (beforeId) {
        messages.value = [...newMessages, ...messages.value]
      } else {
        messages.value = newMessages
      }
      hasMoreMessages.value = newMessages.length >= 50
      return newMessages
    } finally {
      isLoadingMessages.value = false
    }
  }

  async function sendMessage(content: string, attachments?: File[], replyToId?: number) {
    if (!currentConversation.value) return null

    isSendingMessage.value = true
    try {
      const message = await ChatService.sendMessage(currentConversation.value.id, {
        content,
        attachments,
        reply_to_id: replyToId
      })
      // Message will be added via WebSocket, but add optimistically
      messages.value.push(message)
      return message
    } finally {
      isSendingMessage.value = false
    }
  }

  async function startPrivateChat(userId: number) {
    const conversation = await ChatService.startPrivateChat(userId)
    // Check if already in list
    const existing = conversations.value.find(c => c.id === conversation.id)
    if (!existing) {
      conversations.value.unshift(conversation)
    }
    selectConversation(conversation)
    return conversation
  }

  async function createGroup(name: string, memberIds: number[], avatar?: File) {
    const conversation = await ChatService.createGroup({ name, member_ids: memberIds, avatar })
    conversations.value.unshift(conversation)
    selectConversation(conversation)
    return conversation
  }

  async function deleteMessage(messageId: number) {
    await ChatService.deleteMessage(messageId)
    messages.value = messages.value.filter(m => m.id !== messageId)
  }

  async function deleteConversation(conversationId: number) {
    await ChatService.deleteConversation(conversationId)
    conversations.value = conversations.value.filter(c => c.id !== conversationId)
    if (currentConversation.value?.id === conversationId) {
      currentConversation.value = null
      messages.value = []
    }
  }

  async function leaveConversation(conversationId: number) {
    await ChatService.leaveConversation(conversationId)
    conversations.value = conversations.value.filter(c => c.id !== conversationId)
    if (currentConversation.value?.id === conversationId) {
      currentConversation.value = null
      messages.value = []
    }
  }

  function selectConversation(conversation: IConversation) {
    currentConversation.value = conversation
    messages.value = []
    hasMoreMessages.value = true
    fetchMessages(conversation.id)
    ChatService.markAsRead(conversation.id)
    // Reset unread count
    const conv = conversations.value.find(c => c.id === conversation.id)
    if (conv) {
      conv.unread_count = 0
    }
  }

  // WebSocket handlers
  function handleNewMessage(message: IMessage) {
    // Add message if it's for current conversation
    if (currentConversation.value?.id === message.conversation_id) {
      // Avoid duplicates
      if (!messages.value.find(m => m.id === message.id)) {
        messages.value.push(message)
      }
      // Mark as read since we're viewing
      ChatService.markAsRead(message.conversation_id)
    } else {
      // Increment unread count for the conversation
      const conv = conversations.value.find(c => c.id === message.conversation_id)
      if (conv) {
        conv.unread_count = (conv.unread_count || 0) + 1
        conv.latest_message = message
      }
    }

    // Move conversation to top
    const index = conversations.value.findIndex(c => c.id === message.conversation_id)
    if (index > 0) {
      const [conv] = conversations.value.splice(index, 1)
      conv.latest_message = message
      conversations.value.unshift(conv)
    }
  }

  function handleUserTyping(conversationId: number, userId: number, userName: string, isTyping: boolean) {
    if (isTyping) {
      typingUsers.value.set(userId, { userId, userName })
    } else {
      typingUsers.value.delete(userId)
    }
  }

  function clearTypingUsers() {
    typingUsers.value.clear()
  }

  function $reset() {
    conversations.value = []
    currentConversation.value = null
    messages.value = []
    isLoadingConversations.value = false
    isLoadingMessages.value = false
    isSendingMessage.value = false
    hasMoreMessages.value = true
    typingUsers.value.clear()
  }

  return {
    // State
    conversations,
    currentConversation,
    messages,
    isLoadingConversations,
    isLoadingMessages,
    isSendingMessage,
    hasMoreMessages,
    typingUsers,

    // Getters
    totalUnreadCount,
    sortedConversations,
    currentConversationPartner,

    // Actions
    fetchConversations,
    fetchMessages,
    sendMessage,
    startPrivateChat,
    createGroup,
    deleteMessage,
    deleteConversation,
    leaveConversation,
    selectConversation,
    handleNewMessage,
    handleUserTyping,
    clearTypingUsers,
    $reset
  }
})
