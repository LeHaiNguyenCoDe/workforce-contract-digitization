/**
 * Shared formatters for Admin views
 */

/**
 * Format number as Vietnamese currency
 */
export function formatPrice(value: number | undefined | null): string {
    if (value === undefined || value === null) return '-'
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(value)
}

/**
 * Format number with locale separators
 */
export function formatNumber(value: number | undefined | null): string {
    if (value === undefined || value === null) return '-'
    return new Intl.NumberFormat('vi-VN').format(value)
}

/**
 * Format date to Vietnamese locale
 */
export function formatDate(
    value: string | Date | undefined | null,
    options?: Intl.DateTimeFormatOptions
): string {
    if (!value) return '-'

    const date = typeof value === 'string' ? new Date(value) : value

    const defaultOptions: Intl.DateTimeFormatOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }

    return date.toLocaleDateString('vi-VN', options || defaultOptions)
}

/**
 * Format datetime to Vietnamese locale
 */
export function formatDateTime(
    value: string | Date | undefined | null
): string {
    if (!value) return '-'

    const date = typeof value === 'string' ? new Date(value) : value

    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

/**
 * Format relative time (e.g., "2 giờ trước")
 */
export function formatRelativeTime(value: string | Date | undefined | null): string {
    if (!value) return '-'

    const date = typeof value === 'string' ? new Date(value) : value
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffMins = Math.floor(diffMs / (1000 * 60))
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

    if (diffMins < 1) return 'Vừa xong'
    if (diffMins < 60) return `${diffMins} phút trước`
    if (diffHours < 24) return `${diffHours} giờ trước`
    if (diffDays < 7) return `${diffDays} ngày trước`

    return formatDate(date)
}

/**
 * Truncate text with ellipsis
 */
export function truncate(text: string | undefined | null, length: number = 50): string {
    if (!text) return ''
    if (text.length <= length) return text
    return text.substring(0, length) + '...'
}
