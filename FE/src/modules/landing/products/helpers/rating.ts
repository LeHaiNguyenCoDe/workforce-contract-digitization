/**
 * Product Rating Helpers
 */

/**
 * Get rating stars (for display)
 */
export function getRatingStars(rating: number): { full: number; half: boolean; empty: number } {
  const full = Math.floor(rating)
  const half = rating % 1 >= 0.5
  const empty = 5 - full - (half ? 1 : 0)
  return { full, half, empty }
}

/**
 * Format rating display
 */
export function formatRating(rating: number, reviewsCount?: number): string {
  const ratingText = rating.toFixed(1)
  if (reviewsCount !== undefined) {
    return `${ratingText} (${reviewsCount} đánh giá)`
  }
  return ratingText
}
