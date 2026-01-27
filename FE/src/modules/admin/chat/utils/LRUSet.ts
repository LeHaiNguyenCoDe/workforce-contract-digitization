/**
 * LRU (Least Recently Used) Set with time-based expiration
 * Used for tracking processed message IDs to prevent duplicates while avoiding memory leaks
 */
export class LRUSet<T> {
  private map = new Map<T, number>()
  private maxSize: number
  private maxAge: number // milliseconds

  /**
   * Create a new LRU Set
   * @param maxSize Maximum number of items to store (default: 1000)
   * @param maxAge Maximum age of items in milliseconds (default: 5 minutes)
   */
  constructor(maxSize = 1000, maxAge = 300000) {
    this.maxSize = maxSize
    this.maxAge = maxAge
  }

  /**
   * Add an item to the set
   */
  add(item: T): void {
    // Clean up expired items periodically
    if (this.map.size > this.maxSize / 2) {
      this.cleanup()
    }

    this.map.set(item, Date.now())

    // Remove oldest item if we exceed max size
    if (this.map.size > this.maxSize) {
      const oldest = this.map.keys().next().value
      if (oldest !== undefined) {
        this.map.delete(oldest)
      }
    }
  }

  /**
   * Check if an item exists in the set (and is not expired)
   */
  has(item: T): boolean {
    const timestamp = this.map.get(item)
    if (timestamp === undefined) return false

    // Check if item has expired
    if (Date.now() - timestamp > this.maxAge) {
      this.map.delete(item)
      return false
    }

    return true
  }

  /**
   * Delete an item from the set
   */
  delete(item: T): boolean {
    return this.map.delete(item)
  }

  /**
   * Clear all items from the set
   */
  clear(): void {
    this.map.clear()
  }

  /**
   * Get the current size of the set
   */
  get size(): number {
    return this.map.size
  }

  /**
   * Clean up expired items
   */
  private cleanup(): void {
    const now = Date.now()
    for (const [item, timestamp] of this.map) {
      if (now - timestamp > this.maxAge) {
        this.map.delete(item)
      }
    }
  }

  /**
   * Force cleanup of all expired items
   */
  forceCleanup(): void {
    this.cleanup()
  }
}

/**
 * Create a singleton instance for processed message IDs
 * Max 1000 items, expire after 5 minutes
 */
export const processedMessageIds = new LRUSet<number>(1000, 300000)
