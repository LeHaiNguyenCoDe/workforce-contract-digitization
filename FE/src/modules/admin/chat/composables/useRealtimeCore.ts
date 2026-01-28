/**
 * Core Real-time Connection Composable
 * Handles Echo instance creation and WebSocket connection management
 */
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { ref } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { EchoConfig, EchoChannel, AuthCallback, ConnectionState } from '../types/events'

// Make Pusher available globally for Echo
declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo<unknown>
  }
}

// Initialize Pusher globally for Laravel Echo
window.Pusher = Pusher

// ============================================
// Singleton State
// ============================================
let echoInstance: Echo<unknown> | null = null
const isConnected = ref(false)
const connectionStatus = ref<string>('disconnected')

// ============================================
// Helper Functions
// ============================================

function getCsrfToken(): string {
  const token = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
  return token ? decodeURIComponent(token) : ''
}

function createEchoConfig(): EchoConfig {
  return {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'workforce-chat-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
    wssPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/v1/broadcasting/auth',
    withCredentials: true,
    authorizer: (channel: EchoChannel) => ({
      authorize: (socketId: string, callback: AuthCallback) => {
        httpClient.post('/broadcasting/auth', {
          socket_id: socketId,
          channel_name: channel.name
        }, {
          headers: {
            'X-XSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
          }
        })
        .then((response) => {
          const data = response.data as { auth?: string } | Record<string, unknown>
          if (data && ('auth' in data || typeof data === 'object')) {
            callback(false, data as { auth: string })
          } else {
            callback(true, new Error('Invalid response format'))
          }
        })
        .catch((error: Error & { response?: { status?: number; data?: unknown } }) => {
          callback(true, error)
        })
      }
    })
  }
}

// ============================================
// Core Functions
// ============================================

/**
 * Get or create Echo instance
 */
export function getEcho(): Echo<unknown> {
  if (!echoInstance) {
    const config = createEchoConfig()
    echoInstance = new Echo(config)

    // Bind connection state events
    const pusher = (echoInstance.connector as { pusher?: { connection?: { bind: (event: string, callback: (data: unknown) => void) => void } } }).pusher
    const connection = pusher?.connection

    if (connection) {
      connection.bind('state_change', (states: ConnectionState) => {
        connectionStatus.value = states.current
        isConnected.value = states.current === 'connected'
      })

      connection.bind('error', () => {
        isConnected.value = false
      })
    }
  }
  return echoInstance
}

/**
 * Disconnect and cleanup Echo instance
 */
export function disconnectEcho(): void {
  if (!echoInstance) return

  // Unbind connection events
  const pusher = (echoInstance.connector as { pusher?: { connection?: { unbind: (event: string) => void } } }).pusher
  const connection = pusher?.connection

  if (connection) {
    connection.unbind('state_change')
    connection.unbind('error')
  }

  echoInstance.disconnect()
  echoInstance = null
  isConnected.value = false
  connectionStatus.value = 'disconnected'
}

/**
 * Check if Echo is connected
 */
export function useRealtimeCore() {
  return {
    isConnected,
    connectionStatus,
    getEcho,
    disconnectEcho
  }
}
