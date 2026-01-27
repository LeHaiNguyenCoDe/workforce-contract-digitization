/**
 * Helper for browser notifications
 */
export const NotificationHelper = {
  /**
   * Request permission for notifications
   */
  async requestPermission(): Promise<boolean> {
    if (!('Notification' in window)) {
      return false
    }

    if (Notification.permission === 'granted') {
      return true
    }

    if (Notification.permission !== 'denied') {
      const permission = await Notification.requestPermission()
      return permission === 'granted'
    }

    return false
  },

  /**
   * Show a browser notification
   */
  showNotification(title: string, options: NotificationOptions & { onClick?: () => void }) {
    if (!('Notification' in window)) {
        console.error('NotificationHelper: Notifications not supported')
        return
    }
    
    if (Notification.permission !== 'granted') {
      console.warn('NotificationHelper: Permission not granted', Notification.permission)
      return
    }

    const notification = new Notification(title, {
      icon: '/favicon.ico', // Adjust icon path as needed
      badge: '/favicon.ico',
      ...options
    })

    if (options.onClick) {
      notification.onclick = () => {
        window.focus()
        options.onClick?.()
        notification.close()
      }
    }
  }
}
