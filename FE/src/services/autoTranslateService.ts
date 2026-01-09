import httpClient from '@/plugins/api/httpClient'

export interface TranslateResponse {
  original: string
  translated: string
  source_locale: string
  target_locale: string
  saved: boolean
}

export interface BatchTranslateItem {
  text: string
  field?: string
}

export interface BatchTranslateResponse {
  translations: Array<{
    field: string
    original: string
    translated: string
  }>
  target_locale: string
  saved: boolean
}

export interface GetTranslationResponse {
  value: string | null
  is_auto_translated?: boolean
  source: 'database' | 'on_the_fly' | 'not_found'
}

export interface TranslateAllResponse {
  translations: Record<string, Record<string, string>>
  locales_count: number
  fields_count: number
}

/**
 * Auto Translation Service
 * Provides both Option A (save to DB) and Option B (on-the-fly) translation
 */
export const autoTranslateService = {
  /**
   * Translate single text
   * Option A: Set save=true to persist to database
   * Option B: Set save=false for on-the-fly translation
   */
  async translate(params: {
    text: string
    targetLocale: string
    sourceLocale?: string
    save?: boolean
    translatableType?: string
    translatableId?: number
    field?: string
  }): Promise<TranslateResponse> {
    const response = await httpClient.post<any>('admin/translate', {
      text: params.text,
      target_locale: params.targetLocale,
      source_locale: params.sourceLocale || 'vi',
      save: params.save || false,
      translatable_type: params.translatableType,
      translatable_id: params.translatableId,
      field: params.field,
    })
    return response.data.data
  },

  /**
   * Batch translate multiple texts
   */
  async translateBatch(params: {
    items: BatchTranslateItem[]
    targetLocale: string
    sourceLocale?: string
    save?: boolean
    translatableType?: string
    translatableId?: number
  }): Promise<BatchTranslateResponse> {
    const response = await httpClient.post<any>('admin/translate/batch', {
      items: params.items,
      target_locale: params.targetLocale,
      source_locale: params.sourceLocale || 'vi',
      save: params.save || false,
      translatable_type: params.translatableType,
      translatable_id: params.translatableId,
    })
    return response.data.data
  },

  /**
   * Get translation from database, with on-the-fly fallback
   */
  async getTranslation(params: {
    translatableType: string
    translatableId: number
    field: string
    locale: string
    fallbackText?: string
  }): Promise<GetTranslationResponse> {
    const response = await httpClient.get<any>('admin/translate', {
      params: {
        translatable_type: params.translatableType,
        translatable_id: params.translatableId,
        field: params.field,
        locale: params.locale,
        fallback_text: params.fallbackText,
      },
    })
    return response.data.data
  },

  /**
   * Translate all fields to all supported locales and save to database
   * Use after creating/updating content to pre-translate everything
   */
  async translateAll(params: {
    translatableType: string
    translatableId: number
    fields: Array<{ name: string; value: string }>
    sourceLocale?: string
  }): Promise<TranslateAllResponse> {
    const response = await httpClient.post<any>('admin/translate/all', {
      translatable_type: params.translatableType,
      translatable_id: params.translatableId,
      fields: params.fields,
      source_locale: params.sourceLocale || 'vi',
    })
    return response.data.data
  },
}

export default autoTranslateService
