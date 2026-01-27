/**
 * HTML Sanitization Utility
 * Prevents XSS attacks by sanitizing HTML content before rendering with v-html
 */
import DOMPurify from 'dompurify'

/**
 * Sanitize HTML content to prevent XSS attacks
 * @param dirty - Untrusted HTML string
 * @returns Sanitized HTML string safe for v-html rendering
 */
export function sanitizeHtml(dirty: string | undefined | null): string {
  if (!dirty) return ''
  
  return DOMPurify.sanitize(dirty, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'br', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre', 'span', 'div'],
    ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'id'],
    ALLOW_DATA_ATTR: false,
    ADD_ATTR: ['target'],
    FORBID_TAGS: ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input'],
    FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur']
  })
}

/**
 * Sanitize simple text with basic formatting (for notifications, comments)
 * More restrictive - only allows basic inline formatting
 */
export function sanitizeSimpleHtml(dirty: string | undefined | null): string {
  if (!dirty) return ''
  
  return DOMPurify.sanitize(dirty, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'br', 'span'],
    ALLOWED_ATTR: ['class'],
    ALLOW_DATA_ATTR: false
  })
}
