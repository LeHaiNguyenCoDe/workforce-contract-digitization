import { createI18n } from 'vue-i18n'
import vi from './locales/vi.json'
import en from './locales/en.json'

export type Locale = 'vi' | 'en'

// Get saved locale or default to 'vi'
const getInitialLocale = (): Locale => {
  const saved = localStorage.getItem('locale') as Locale
  if (saved && ['vi', 'en'].includes(saved)) {
    return saved
  }
  
  // Check browser language
  const browserLang = navigator.language.split('-')[0]
  if (browserLang === 'en') return 'en'
  
  return 'vi'
}

export const i18n = createI18n({
  legacy: false, // Use Composition API
  locale: getInitialLocale(),
  fallbackLocale: 'vi',
  messages: {
    vi,
    en
  }
})

/**
 * Set current locale
 */
export function setLocale(locale: Locale): void {
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
