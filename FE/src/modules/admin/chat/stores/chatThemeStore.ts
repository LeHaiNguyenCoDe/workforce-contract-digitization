/**
 * Chat Theme Store
 * Manages chat bubble colors and theme customization per conversation
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// Available theme presets
export interface ChatTheme {
  id: string
  name: string
  sentBg: string      // Tailwind class for sent message background
  sentText: string    // Tailwind class for sent message text
  sentShadow: string  // Tailwind class for sent message shadow
  receivedBg: string  // Tailwind class for received message background
  receivedText: string // Tailwind class for received message text
  accentColor: string // Tailwind class for accent elements
}

export const CHAT_THEMES: ChatTheme[] = [
  {
    id: 'teal',
    name: 'Xanh ngọc',
    sentBg: 'bg-teal-500',
    sentText: 'text-white',
    sentShadow: 'shadow-teal-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-teal-500'
  },
  {
    id: 'blue',
    name: 'Xanh dương',
    sentBg: 'bg-blue-500',
    sentText: 'text-white',
    sentShadow: 'shadow-blue-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-blue-500'
  },
  {
    id: 'purple',
    name: 'Tím',
    sentBg: 'bg-purple-500',
    sentText: 'text-white',
    sentShadow: 'shadow-purple-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-purple-500'
  },
  {
    id: 'pink',
    name: 'Hồng',
    sentBg: 'bg-pink-500',
    sentText: 'text-white',
    sentShadow: 'shadow-pink-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-pink-500'
  },
  {
    id: 'orange',
    name: 'Cam',
    sentBg: 'bg-orange-500',
    sentText: 'text-white',
    sentShadow: 'shadow-orange-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-orange-500'
  },
  {
    id: 'red',
    name: 'Đỏ',
    sentBg: 'bg-red-500',
    sentText: 'text-white',
    sentShadow: 'shadow-red-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-red-500'
  },
  {
    id: 'green',
    name: 'Xanh lá',
    sentBg: 'bg-green-500',
    sentText: 'text-white',
    sentShadow: 'shadow-green-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-green-500'
  },
  {
    id: 'indigo',
    name: 'Chàm',
    sentBg: 'bg-indigo-500',
    sentText: 'text-white',
    sentShadow: 'shadow-indigo-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-indigo-500'
  },
  {
    id: 'rose',
    name: 'Hồng đậm',
    sentBg: 'bg-rose-500',
    sentText: 'text-white',
    sentShadow: 'shadow-rose-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-rose-500'
  },
  {
    id: 'amber',
    name: 'Vàng hổ phách',
    sentBg: 'bg-amber-500',
    sentText: 'text-white',
    sentShadow: 'shadow-amber-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-amber-500'
  },
  {
    id: 'gradient-purple',
    name: 'Tím gradient',
    sentBg: 'bg-gradient-to-br from-purple-500 to-pink-500',
    sentText: 'text-white',
    sentShadow: 'shadow-purple-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-purple-500'
  },
  {
    id: 'gradient-blue',
    name: 'Xanh gradient',
    sentBg: 'bg-gradient-to-br from-blue-500 to-teal-400',
    sentText: 'text-white',
    sentShadow: 'shadow-blue-500/20',
    receivedBg: 'bg-white',
    receivedText: 'text-gray-800',
    accentColor: 'text-blue-500'
  },
  {
    id: 'dark',
    name: 'Tối',
    sentBg: 'bg-gray-800',
    sentText: 'text-white',
    sentShadow: 'shadow-gray-800/20',
    receivedBg: 'bg-gray-100',
    receivedText: 'text-gray-800',
    accentColor: 'text-gray-700'
  }
]

const STORAGE_KEY = 'chat_themes'

export const useChatThemeStore = defineStore('chatTheme', () => {
  // Map of conversationId -> themeId
  const conversationThemes = ref<Record<number, string>>({})

  // Default theme id
  const defaultThemeId = ref('teal')

  // Load from localStorage
  function loadThemes() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY)
      if (stored) {
        const data = JSON.parse(stored)
        conversationThemes.value = data.themes || {}
        defaultThemeId.value = data.defaultTheme || 'teal'
      }
    } catch (e) {
      console.error('Failed to load chat themes:', e)
    }
  }

  // Save to localStorage
  function saveThemes() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        themes: conversationThemes.value,
        defaultTheme: defaultThemeId.value
      }))
    } catch (e) {
      console.error('Failed to save chat themes:', e)
    }
  }

  // Get theme for a conversation
  function getTheme(conversationId: number): ChatTheme {
    const themeId = conversationThemes.value[conversationId] || defaultThemeId.value
    return CHAT_THEMES.find(t => t.id === themeId) || CHAT_THEMES[0]
  }

  // Set theme for a conversation
  function setTheme(conversationId: number, themeId: string) {
    conversationThemes.value[conversationId] = themeId
    saveThemes()
  }

  // Set default theme
  function setDefaultTheme(themeId: string) {
    defaultThemeId.value = themeId
    saveThemes()
  }

  // Get all available themes
  const availableThemes = computed(() => CHAT_THEMES)

  // Initialize on store creation
  loadThemes()

  return {
    conversationThemes,
    defaultThemeId,
    availableThemes,
    getTheme,
    setTheme,
    setDefaultTheme,
    loadThemes,
    saveThemes
  }
})
