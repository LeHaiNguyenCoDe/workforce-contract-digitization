/**
 * Landing Products Column Configs
 */
import { useI18n } from 'vue-i18n'

export const productColumns = [
    { key: 'name', label: 'product.title' },
    { key: 'category', label: 'common.categories' },
    { key: 'price', label: 'product.price' },
    { key: 'rating', label: 'product.reviews' }
]

// Sort options with translation keys - use getSortOptions() to get translated labels
export const sortOptions = [
    { value: 'newest', labelKey: 'common.newest' },
    { value: 'price_asc', labelKey: 'common.priceLowHigh' },
    { value: 'price_desc', labelKey: 'common.priceHighLow' },
    { value: 'popular', labelKey: 'common.mostPopular' }
]

// Helper function to get translated sort options
export function useSortOptions() {
    const { t } = useI18n()
    return sortOptions.map(opt => ({
        value: opt.value,
        label: t(opt.labelKey)
    }))
}

export const priceRanges = [
    { min: 0, max: 100000, labelKey: 'common.under100k' },
    { min: 100000, max: 500000, labelKey: 'common.range100k500k' },
    { min: 500000, max: 1000000, labelKey: 'common.range500k1m' },
    { min: 1000000, max: 5000000, labelKey: 'common.range1m5m' },
    { min: 5000000, max: 0, labelKey: 'common.above5m' }
]

