import notificationSound from '@/assets/notification.mp3'

export class AudioHelper {
  private static messageSound = new Audio(notificationSound)

  static async playMessageSound() {
    try {
      this.messageSound.currentTime = 0
      await this.messageSound.play()
    } catch (error) {
      console.warn('AudioHelper: Failed to play sound', error)
    }
  }
}
