import { createI18n } from 'vue-i18n'
import { nextTick } from 'vue'

// All 20 supported locales matching backend
export const SUPPORTED_LOCALES = [
  'vi', 'en', 'ar', 'cs', 'de', 'es', 'fr', 'hi', 'id', 'it',
  'ja', 'ko', 'nl', 'pl', 'pt', 'ru', 'sv', 'th', 'tr', 'zh'
] as const

export type Locale = typeof SUPPORTED_LOCALES[number]

// Get saved locale or default to 'vi'
const getInitialLocale = (): Locale => {
  const saved = localStorage.getItem('locale') as Locale
  if (saved && SUPPORTED_LOCALES.includes(saved)) {
    return saved
  }
  
  // Check browser language
  const browserLang = navigator.language.split('-')[0] as Locale
  if (SUPPORTED_LOCALES.includes(browserLang)) {
    return browserLang
  }
  
  return 'vi'
}

export const i18n = createI18n({
  legacy: false, // Use Composition API
  locale: getInitialLocale(),
  fallbackLocale: 'vi',
  // Start with empty messages, will be loaded on demand
  messages: {}
})

export const i18nInstance = i18n

/**
 * Categories for locale files
 */
const LOCALE_CATEGORIES = ['common', 'nav', 'auth', 'product', 'cart', 'order', 'admin', 'validation', 'home', 'admin-panel'] as const

/**
 * Load locale messages dynamically from folder structure
 */
export async function loadLocaleMessages(locale: Locale): Promise<void> {
  // If messages for this locale are already loaded, skip
  if (Object.keys(i18n.global.getLocaleMessage(locale)).length > 0) {
    return
  }

  // Load all category files for this locale
  const messages: Record<string, any> = {}
  
  await Promise.all(
    LOCALE_CATEGORIES.map(async (category) => {
      try {
        const module = await import(`./locales/${locale}/${category}.json`)
        if (category === 'admin-panel') {
          Object.assign(messages, module.default)
        } else {
          messages[category] = module.default
        }
      } catch (e) {
        console.warn(`Failed to load ${locale}/${category}.json`)
      }
    })
  )

  // Set locale messages
  i18n.global.setLocaleMessage(locale, messages)

  return nextTick()
}

/**
 * Set current locale (Async)
 */
export async function setLocale(locale: Locale): Promise<void> {
  // Load messages if not already loaded
  await loadLocaleMessages(locale)

  // Update locale
  i18n.global.locale.value = locale
  localStorage.setItem('locale', locale)
  document.documentElement.lang = locale
}

/**
 * Get current locale
 */
export function getLocale(): Locale {
  return i18n.global.locale.value as Locale
}

export default i18n
