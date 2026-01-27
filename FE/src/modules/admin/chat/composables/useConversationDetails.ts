/**
 * Composable for Conversation Details Sidebar functionality
 * Handles mute/unmute, search messages, media gallery, user actions etc.
 */
import { ref, computed, type Ref, type ComputedRef, watch } from 'vue'
import type { IConversation, IMessage, IMessageAttachment } from '../models/Chat'
import { ChatService } from '../services/ChatService'

// ============================================
// Types
// ============================================

export interface SharedMedia {
  id: number
  messageId: number
  type: 'image' | 'video' | 'document' | 'other'
  url: string
  thumbnail?: string | null
  name: string
  size: number
  createdAt: string
}

export interface ConversationDetailsState {
  // UI State
  isMuted: ComputedRef<boolean>
  isPinned: ComputedRef<boolean>
  readReceipts: ComputedRef<boolean>
  messagingPermissions: ComputedRef<'all' | 'admin_only'>
  disappearingMessages: ComputedRef<number | null>
  friendshipStatus: ComputedRef<'none' | 'pending' | 'sent' | 'accepted' | 'blocked' | 'blocked_by_me' | 'blocked_by_them' | 'blocked_mutually' | undefined>
  isSearchOpen: Ref<boolean>
  isMediaGalleryOpen: Ref<boolean>
  isFilesListOpen: Ref<boolean>
  isProfileModalOpen: Ref<boolean>
  isBlockModalOpen: Ref<boolean>
  isReportModalOpen: Ref<boolean>

  // Data
  searchQuery: Ref<string>
  searchResults: Ref<IMessage[]>
  isSearching: Ref<boolean>
  sharedMedia: Ref<SharedMedia[]>
  sharedFiles: Ref<SharedMedia[]>
  isLoadingMedia: Ref<boolean>

  // Actions
  toggleMute: () => Promise<void>
  togglePin: () => Promise<void>
  toggleReadReceipts: () => Promise<void>
  updateMessagingPermissions: (value: 'all' | 'admin_only') => Promise<void>
  updateDisappearingMessages: (ttl: number | null) => Promise<void>
  searchMessages: (query: string) => Promise<void>
  loadSharedMedia: () => Promise<void>
  loadSharedFiles: () => Promise<void>
  blockUser: () => Promise<void>
  unblockUser: () => Promise<void>
  reportConversation: (reason: string) => Promise<void>
  openSearch: () => void
  closeSearch: () => void
  openMediaGallery: () => void
  closeMediaGallery: () => void
  openFilesList: () => void
  closeFilesList: () => void
}

// ============================================
// Main Composable
// ============================================

export function useConversationDetails(
  conversation: Ref<IConversation | null>
): ConversationDetailsState {
  // --------------------------------
  // UI State
  // --------------------------------
  const isSearchOpen = ref(false)
  const isMediaGalleryOpen = ref(false)
  const isFilesListOpen = ref(false)
  const isProfileModalOpen = ref(false)
  const isBlockModalOpen = ref(false)
  const isReportModalOpen = ref(false)

  // --------------------------------
  // Data State
  // --------------------------------
  const searchQuery = ref('')
  const searchResults = ref<IMessage[]>([])
  const isSearching = ref(false)
  const sharedMedia = ref<SharedMedia[]>([])
  const sharedFiles = ref<SharedMedia[]>([])
  const isLoadingMedia = ref(false)

  // --------------------------------
  // Computed Properties
  // --------------------------------
  const isMuted = computed(() => {
    return conversation.value?.pivot?.is_muted || false
  })

  const isPinned = computed(() => {
    return conversation.value?.pivot?.is_pinned || false
  })

  const readReceipts = computed(() => {
    return conversation.value?.pivot?.read_receipts_enabled ?? true
  })

  const messagingPermissions = computed(() => {
    return conversation.value?.messaging_permissions || 'all'
  })

  const disappearingMessages = computed(() => {
    return conversation.value?.disappearing_messages_ttl ?? null
  })

  const friendshipStatus = computed(() => {
    return conversation.value?.friendship_status
  })

  // --------------------------------
  // Actions
  // --------------------------------

  /**
   * Toggle mute status for the conversation
   */
  async function toggleMute(): Promise<void> {
    if (!conversation.value) return

    try {
      const newMuteStatus = !isMuted.value
      await ChatService.updateConversationSettings(conversation.value.id, {
        is_muted: newMuteStatus
      })

      // Update local state (the conversation ref should be reactive from the store)
      if (conversation.value.pivot) {
        conversation.value.pivot.is_muted = newMuteStatus
      }
    } catch (error) {
      console.error('Failed to toggle mute:', error)
      throw error
    }
  }

  /**
   * Toggle pin status for the conversation
   */
  async function togglePin(): Promise<void> {
    if (!conversation.value) return

    try {
      const newPinStatus = !isPinned.value
      await ChatService.updateConversationSettings(conversation.value.id, {
        is_pinned: newPinStatus
      })

      // Update local state
      if (conversation.value.pivot) {
        conversation.value.pivot.is_pinned = newPinStatus
      }
    } catch (error) {
      console.error('Failed to toggle pin:', error)
      throw error
    }
  }

  /**
   * Toggle read receipts status
   */
  async function toggleReadReceipts(): Promise<void> {
    if (!conversation.value) return

    try {
      const newStatus = !readReceipts.value
      await ChatService.updateConversationSettings(conversation.value.id, {
        read_receipts_enabled: newStatus
      })

      if (conversation.value.pivot) {
        conversation.value.pivot.read_receipts_enabled = newStatus
      }
    } catch (error) {
      console.error('Failed to toggle read receipts:', error)
      throw error
    }
  }

  /**
   * Update messaging permissions
   */
  async function updateMessagingPermissions(value: 'all' | 'admin_only'): Promise<void> {
    if (!conversation.value) return

    try {
      await ChatService.updateConversationSettings(conversation.value.id, {
        messaging_permissions: value
      })

      conversation.value.messaging_permissions = value
    } catch (error) {
      console.error('Failed to update messaging permissions:', error)
      throw error
    }
  }

  /**
   * Update disappearing messages TTL
   */
  async function updateDisappearingMessages(ttl: number | null): Promise<void> {
    if (!conversation.value) return

    try {
      await ChatService.updateConversationSettings(conversation.value.id, {
        disappearing_messages_ttl: ttl
      })

      conversation.value.disappearing_messages_ttl = ttl
    } catch (error) {
      console.error('Failed to update disappearing messages:', error)
      throw error
    }
  }

  /**
   * Search messages in the conversation
   */
  async function searchMessages(query: string): Promise<void> {
    if (!conversation.value || !query.trim()) {
      searchResults.value = []
      return
    }

    isSearching.value = true
    try {
      const results = await ChatService.searchMessages(conversation.value.id, query)
      searchResults.value = results
    } catch (error) {
      console.error('Failed to search messages:', error)
      searchResults.value = []
    } finally {
      isSearching.value = false
    }
  }

  // --------------------------------
  // Helpers
  // --------------------------------
  function normalizeUrl(url: string | null | undefined): string {
    if (!url) return ''
    
    // If URL is a full URL with /storage/, extract just the /storage/ part
    if (url.includes('/storage/')) {
      const storageIndex = url.indexOf('/storage/')
      return url.substring(storageIndex)
    } 
    // If it starts with storage/ without leading slash
    if (url.startsWith('storage/')) {
      return '/' + url
    }
    // If it doesn't start with http or data: or /storage/, prefix with /storage/
    if (!url.startsWith('http') && !url.startsWith('data:') && !url.startsWith('/storage/') && !url.startsWith('/')) {
      const cleanPath = url.replace(/^\//, '')
      return '/storage/' + encodeURI(cleanPath)
    }
    // If it starts with / but not /storage/, might be a relative path to storage
    if (url.startsWith('/') && !url.startsWith('/storage/')) {
      return '/storage' + url
    }
    
    return url
  }

  /**
   * Load shared media (images and videos) from the conversation
   */
  async function loadSharedMedia(): Promise<void> {
    if (!conversation.value) return

    isLoadingMedia.value = true
    try {
      const attachments = await ChatService.getConversationMedia(conversation.value.id, 'media')
      sharedMedia.value = attachments.map(att => ({
        id: att.id,
        messageId: att.message_id || 0,
        type: att.file_type?.startsWith('image/') ? 'image' : 'video',
        url: normalizeUrl(att.file_path),
        thumbnail: normalizeUrl(att.thumbnail_path),
        name: att.file_name,
        size: att.file_size,
        createdAt: att.created_at || ''
      }))
    } catch (error) {
      console.error('Failed to load shared media:', error)
      sharedMedia.value = []
    } finally {
      isLoadingMedia.value = false
    }
  }

  /**
   * Load shared files (documents, etc.) from the conversation
   */
  async function loadSharedFiles(): Promise<void> {
    if (!conversation.value) return

    isLoadingMedia.value = true
    try {
      const attachments = await ChatService.getConversationMedia(conversation.value.id, 'files')
      sharedFiles.value = attachments.map(att => ({
        id: att.id,
        messageId: att.message_id || 0,
        type: 'document',
        url: normalizeUrl(att.file_path),
        thumbnail: normalizeUrl(att.thumbnail_path),
        name: att.file_name,
        size: att.file_size,
        createdAt: att.created_at || ''
      }))
    } catch (error) {
      console.error('Failed to load shared files:', error)
      sharedFiles.value = []
    } finally {
      isLoadingMedia.value = false
    }
  }

  /**
   * Block the user in the conversation
   */
  async function blockUser(): Promise<void> {
    if (!conversation.value) return

    try {
      await ChatService.blockUser(conversation.value.id)
      // Update local state
      if (conversation.value) {
        // If we block them, we are the blocker
        conversation.value.friendship_status = 'blocked_by_me'
      }
      isBlockModalOpen.value = false
    } catch (error) {
      console.error('Failed to block user:', error)
      throw error
    }
  }

  /**
   * Unblock the user in the conversation
   */
  async function unblockUser(): Promise<void> {
    if (!conversation.value) return

    try {
      await ChatService.unblockUser(conversation.value.id)
      // Update local state
      if (conversation.value) {
        conversation.value.friendship_status = 'none'
      }
    } catch (error) {
      console.error('Failed to unblock user:', error)
      throw error
    }
  }

  /**
   * Report the conversation
   */
  async function reportConversation(reason: string): Promise<void> {
    if (!conversation.value) return

    try {
      await ChatService.reportConversation(conversation.value.id, reason)
      isReportModalOpen.value = false
    } catch (error) {
      console.error('Failed to report conversation:', error)
      throw error
    }
  }

  // --------------------------------
  // UI Actions
  // --------------------------------
  function openSearch() {
    isSearchOpen.value = true
    isMediaGalleryOpen.value = false
    isFilesListOpen.value = false
  }

  function closeSearch() {
    isSearchOpen.value = false
    searchQuery.value = ''
    searchResults.value = []
  }

  function openMediaGallery() {
    isMediaGalleryOpen.value = true
    isSearchOpen.value = false
    isFilesListOpen.value = false
    loadSharedMedia()
  }

  function closeMediaGallery() {
    isMediaGalleryOpen.value = false
  }

  function openFilesList() {
    isFilesListOpen.value = true
    isSearchOpen.value = false
    isMediaGalleryOpen.value = false
    loadSharedFiles()
  }

  function closeFilesList() {
    isFilesListOpen.value = false
  }

  // Reset state when conversation changes
  watch(conversation, () => {
    closeSearch()
    closeMediaGallery()
    closeFilesList()
    sharedMedia.value = []
    sharedFiles.value = []
  })

  return {
    // UI State
    isMuted,
    isPinned,
    readReceipts,
    messagingPermissions,
    disappearingMessages,
    friendshipStatus,
    isSearchOpen,
    isMediaGalleryOpen,
    isFilesListOpen,
    isProfileModalOpen,
    isBlockModalOpen,
    isReportModalOpen,

    // Data
    searchQuery,
    searchResults,
    isSearching,
    sharedMedia,
    sharedFiles,
    isLoadingMedia,

    // Actions
    toggleMute,
    togglePin,
    toggleReadReceipts,
    updateMessagingPermissions,
    updateDisappearingMessages,
    searchMessages,
    loadSharedMedia,
    loadSharedFiles,
    blockUser,
    unblockUser,
    reportConversation,
    openSearch,
    closeSearch,
    openMediaGallery,
    closeMediaGallery,
    openFilesList,
    closeFilesList
  }
}
