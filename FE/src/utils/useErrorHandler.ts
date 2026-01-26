/**
 * Error Handler Utility
 * Centralized error handling with user-friendly notifications
 */
import { useSwal } from '@/utils'

// ============================================
// Types
// ============================================

export interface ApiError {
  response?: {
    status?: number
    data?: {
      message?: string
      errors?: Record<string, string[]>
    }
  }
  message?: string
}

export interface ErrorHandlerOptions {
  /** Default error message if none can be extracted */
  defaultMessage?: string
  /** Whether to show the error to the user (default: true) */
  showNotification?: boolean
  /** Whether to log the error to console (default: true) */
  logToConsole?: boolean
  /** Custom error messages by status code */
  statusMessages?: Record<number, string>
}

// ============================================
// Default Messages
// ============================================

const DEFAULT_STATUS_MESSAGES: Record<number, string> = {
  400: 'Yêu cầu không hợp lệ',
  401: 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại',
  403: 'Bạn không có quyền thực hiện thao tác này',
  404: 'Không tìm thấy dữ liệu',
  422: 'Dữ liệu không hợp lệ',
  429: 'Quá nhiều yêu cầu. Vui lòng thử lại sau',
  500: 'Lỗi hệ thống. Vui lòng thử lại sau',
  502: 'Máy chủ tạm thời không khả dụng',
  503: 'Dịch vụ đang bảo trì'
}

const DEFAULT_ERROR_MESSAGE = 'Có lỗi xảy ra. Vui lòng thử lại!'

// ============================================
// Helper Functions
// ============================================

/**
 * Extract error message from various error formats
 */
export function extractErrorMessage(error: unknown, options: ErrorHandlerOptions = {}): string {
  const {
    defaultMessage = DEFAULT_ERROR_MESSAGE,
    statusMessages = {}
  } = options

  // Handle null/undefined
  if (!error) {
    return defaultMessage
  }

  // Handle string errors
  if (typeof error === 'string') {
    return error
  }

  // Handle API errors with response
  const apiError = error as ApiError

  // Check for status-specific message
  const status = apiError.response?.status
  if (status) {
    const customMessage = statusMessages[status] || DEFAULT_STATUS_MESSAGES[status]
    if (customMessage) {
      return customMessage
    }
  }

  // Check for server-provided message
  const serverMessage = apiError.response?.data?.message
  if (serverMessage && typeof serverMessage === 'string') {
    return serverMessage
  }

  // Check for validation errors
  const validationErrors = apiError.response?.data?.errors
  if (validationErrors && typeof validationErrors === 'object') {
    const firstError = Object.values(validationErrors)[0]
    if (Array.isArray(firstError) && firstError[0]) {
      return firstError[0]
    }
  }

  // Check for standard Error message
  if (apiError.message && typeof apiError.message === 'string') {
    // Don't show generic axios/fetch error messages
    if (!apiError.message.includes('Network Error') &&
        !apiError.message.includes('Request failed')) {
      return apiError.message
    }
  }

  return defaultMessage
}

/**
 * Check if error is a network error
 */
export function isNetworkError(error: unknown): boolean {
  const apiError = error as ApiError
  return !apiError.response &&
         (apiError.message?.includes('Network Error') ||
          apiError.message?.includes('Failed to fetch') ||
          false)
}

/**
 * Check if error is an authentication error
 */
export function isAuthError(error: unknown): boolean {
  const apiError = error as ApiError
  return apiError.response?.status === 401
}

/**
 * Check if error is a permission error
 */
export function isPermissionError(error: unknown): boolean {
  const apiError = error as ApiError
  return apiError.response?.status === 403
}

/**
 * Check if error is a validation error
 */
export function isValidationError(error: unknown): boolean {
  const apiError = error as ApiError
  return apiError.response?.status === 422
}

// ============================================
// Main Composable
// ============================================

/**
 * Error handler composable
 * Provides unified error handling across the application
 *
 * @example
 * ```ts
 * const { handleError, handleApiError } = useErrorHandler()
 *
 * try {
 *   await saveData()
 * } catch (error) {
 *   await handleError(error, 'Không thể lưu dữ liệu')
 * }
 * ```
 */
export function useErrorHandler() {
  const swal = useSwal()

  /**
   * Handle any error with optional notification
   */
  async function handleError(
    error: unknown,
    defaultMessage?: string,
    options: ErrorHandlerOptions = {}
  ): Promise<string> {
    const {
      showNotification = true,
      logToConsole = true
    } = options

    const message = extractErrorMessage(error, {
      ...options,
      defaultMessage: defaultMessage || DEFAULT_ERROR_MESSAGE
    })

    if (logToConsole) {
      console.error('Error:', error)
    }

    if (showNotification) {
      await swal.error(message)
    }

    return message
  }

  /**
   * Handle API error with status-aware messaging
   */
  async function handleApiError(
    error: unknown,
    options: ErrorHandlerOptions = {}
  ): Promise<string> {
    const apiError = error as ApiError

    // Handle network errors specially
    if (isNetworkError(error)) {
      const message = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng'
      if (options.showNotification !== false) {
        await swal.error(message)
      }
      return message
    }

    // Handle auth errors - might need redirect
    if (isAuthError(error)) {
      const message = options.statusMessages?.[401] || DEFAULT_STATUS_MESSAGES[401]
      if (options.showNotification !== false) {
        await swal.warning(message)
      }
      return message
    }

    return handleError(error, options.defaultMessage, options)
  }

  /**
   * Handle validation error and return field errors
   */
  function extractValidationErrors(error: unknown): Record<string, string> {
    const apiError = error as ApiError
    const errors: Record<string, string> = {}

    const validationErrors = apiError.response?.data?.errors
    if (validationErrors && typeof validationErrors === 'object') {
      for (const [field, messages] of Object.entries(validationErrors)) {
        if (Array.isArray(messages) && messages[0]) {
          errors[field] = messages[0]
        }
      }
    }

    return errors
  }

  /**
   * Silent error handler (logs but doesn't show notification)
   */
  function handleSilentError(error: unknown, context?: string): void {
    const message = extractErrorMessage(error)
    console.error(context ? `[${context}] ${message}` : message, error)
  }

  /**
   * Create a try-catch wrapper with error handling
   */
  function withErrorHandling<T>(
    fn: () => Promise<T>,
    options: ErrorHandlerOptions = {}
  ): Promise<T | null> {
    return fn().catch(async (error) => {
      await handleError(error, options.defaultMessage, options)
      return null
    })
  }

  return {
    handleError,
    handleApiError,
    extractErrorMessage,
    extractValidationErrors,
    handleSilentError,
    withErrorHandling,
    isNetworkError,
    isAuthError,
    isPermissionError,
    isValidationError
  }
}

// Export standalone functions for use outside Vue components
export {
  DEFAULT_ERROR_MESSAGE,
  DEFAULT_STATUS_MESSAGES
}
