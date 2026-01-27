import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import autoTranslateService from '@/services/autoTranslateService'

export interface TranslatableField {
  name: string
  value: string
}

/**
 * Composable for auto-translation in admin forms
 * Supports both Option A (save to DB) and Option B (on-the-fly) translation
 */
export function useAutoTranslate() {
  const { locale } = useI18n()
  const isTranslating = ref(false)
  const translationProgress = ref(0)
  const lastError = ref<string | null>(null)

  /**
   * Check if current locale is the source locale (Vietnamese)
   */
  const isSourceLocale = computed(() => locale.value === 'vi')

  /**
   * Translate a single text on-the-fly (Option B)
   */
  async function translateText(
    text: string,
    targetLocale?: string
  ): Promise<string> {
    if (!text || isSourceLocale.value) return text

    try {
      isTranslating.value = true
      lastError.value = null
      
      const result = await autoTranslateService.translate({
        text,
        targetLocale: targetLocale || locale.value,
        sourceLocale: 'vi',
        save: false,
      })
      
      return result.translated
    } catch (error) {
      lastError.value = 'Translation failed'
      console.error('Translation error:', error)
      return text
    } finally {
      isTranslating.value = false
    }
  }

  /**
   * Translate and save to database (Option A)
   * Call this after creating/updating content
   */
  async function translateAndSave(params: {
    translatableType: string
    translatableId: number
    fields: TranslatableField[]
  }): Promise<void> {
    if (params.fields.length === 0) return

    try {
      isTranslating.value = true
      translationProgress.value = 0
      lastError.value = null

      await autoTranslateService.translateAll({
        translatableType: params.translatableType,
        translatableId: params.translatableId,
        fields: params.fields,
        sourceLocale: 'vi',
      })

      translationProgress.value = 100
    } catch (error) {
      lastError.value = 'Failed to save translations'
      console.error('Translation save error:', error)
    } finally {
      isTranslating.value = false
    }
  }

  /**
   * Get translated value for a field
   * First tries database (Option A), then on-the-fly (Option B)
   */
  async function getTranslatedValue(params: {
    translatableType: string
    translatableId: number
    field: string
    fallbackText: string
  }): Promise<string> {
    if (isSourceLocale.value) return params.fallbackText

    try {
      isTranslating.value = true
      
      const result = await autoTranslateService.getTranslation({
        translatableType: params.translatableType,
        translatableId: params.translatableId,
        field: params.field,
        locale: locale.value,
        fallbackText: params.fallbackText,
      })

      return result.value || params.fallbackText
    } catch (error) {
      console.error('Get translation error:', error)
      return params.fallbackText
    } finally {
      isTranslating.value = false
    }
  }

  /**
   * Auto-translate all fields after creating content
   * Use in form submit handlers
   */
  async function autoTranslateAfterSave(
    modelType: string,
    modelId: number,
    data: Record<string, any>,
    translatableFields: string[] = ['name', 'description', 'content', 'title']
  ): Promise<void> {
    const fields: TranslatableField[] = []

    for (const fieldName of translatableFields) {
      if (data[fieldName] && typeof data[fieldName] === 'string') {
        fields.push({
          name: fieldName,
          value: data[fieldName],
        })
      }
    }

    if (fields.length > 0) {
      await translateAndSave({
        translatableType: modelType,
        translatableId: modelId,
        fields,
      })
    }
  }

  return {
    isTranslating,
    translationProgress,
    lastError,
    isSourceLocale,
    translateText,
    translateAndSave,
    getTranslatedValue,
    autoTranslateAfterSave,
  }
}

export default useAutoTranslate
