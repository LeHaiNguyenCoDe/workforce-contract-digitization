import httpClient from '@/plugins/api/httpClient'

export interface SupportedLocale {
  code: string
  name: string
  native: string
  flag: string
  is_current: boolean
}

export interface LanguageInfoResponse {
  locale: string
  info: {
    name: string
    native: string
    flag: string
  }
  supported_count: number
}

export interface SupportedLocalesResponse {
  locales: SupportedLocale[]
  current: string
}

export const languageService = {
  /**
   * Get current locale info from server
   */
  async getCurrentLocale() {
    const response = await httpClient.get<any>('frontend/language')
    return response.data.data as LanguageInfoResponse
  },

  /**
   * Get list of supported locales from server
   */
  async getSupportedLocales() {
    const response = await httpClient.get<any>('frontend/language/supported')
    return response.data.data as SupportedLocalesResponse
  },

  /**
   * Set user locale on server
   */
  async setLocale(locale: string) {
    const response = await httpClient.post<any>('frontend/language', { locale })
    return response.data
  }
}

export default languageService
