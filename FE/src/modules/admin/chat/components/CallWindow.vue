<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useCallStore } from '../stores/callStore'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const callStore = useCallStore()

const isMuted = ref(false)
const isCameraOff = ref(false)
const remoteVideoRef = ref<HTMLVideoElement | null>(null)
const localVideoRef = ref<HTMLVideoElement | null>(null)
const hasClickedRetry = ref(false)

// Sound effects
const dialingAudio = ref<HTMLAudioElement | null>(null)
const ringingAudio = ref<HTMLAudioElement | null>(null)
const endCallAudio = ref<HTMLAudioElement | null>(null)

const formattedDuration = computed(() => {
  const m = Math.floor(callStore.callDuration / 60)
  const s = callStore.callDuration % 60
  return `${m}:${s.toString().padStart(2, '0')}`
})

const handleEndCall = () => {
  callStore.endCall()
  playEndCallSound()
}

const handleRetry = async () => {
  try {
    await callStore.checkPermissions()
  } catch (err) {
    // Retry failed
  }
}

const playEndCallSound = () => {
  playAudio(endCallAudio.value)
}

const pendingAudio = ref<HTMLAudioElement | null>(null)

// Sound warmup/retry helper
const handleUserInteraction = () => {
  if (pendingAudio.value) {
    pendingAudio.value.play().catch(() => {})
    pendingAudio.value = null
  }
  document.removeEventListener('click', handleUserInteraction)
  document.removeEventListener('keydown', handleUserInteraction)
}

const playAudio = async (audioEl: HTMLAudioElement | null) => {
  if (!audioEl) {
    return
  }
  
  const displaySrc = audioEl.src || 'Unknown'
  
  try {
    // Reset state
    audioEl.muted = false 
    audioEl.volume = 0.8
    audioEl.currentTime = 0
    
    if (!audioEl.readyState) {
       audioEl.load()
    }

    await audioEl.play()
    // If we successfully played, we can clear pending
    if (pendingAudio.value === audioEl) pendingAudio.value = null
  } catch (e: any) {
    if (e.name === 'NotAllowedError') {
      pendingAudio.value = audioEl
      document.addEventListener('click', handleUserInteraction, { once: true })
      document.addEventListener('keydown', handleUserInteraction, { once: true })
      return;
    }
    
    if (e.name === 'NotSupportedError') {
      audioEl.load()
    }
  }
}

// Global debug helper for sound
const handleTestSound = (type: 'dialing' | 'ringing' | 'end') => {
  if (type === 'dialing') playAudio(dialingAudio.value)
  if (type === 'ringing') playAudio(ringingAudio.value)
  if (type === 'end') playAudio(endCallAudio.value)
}

if (typeof window !== 'undefined') {
  (window as any).testCallSound = handleTestSound
}

const toggleMute = () => {
  if (callStore.localStream) {
    const audioTrack = callStore.localStream.getAudioTracks()[0]
    if (audioTrack) {
      audioTrack.enabled = !audioTrack.enabled
      isMuted.value = !audioTrack.enabled
    }
  }
}

const toggleCamera = () => {
  if (callStore.localStream && callStore.callType === 'video') {
    const videoTrack = callStore.localStream.getVideoTracks()[0]
    if (videoTrack) {
      videoTrack.enabled = !videoTrack.enabled
      isCameraOff.value = !videoTrack.enabled
    }
  }
}

watch(() => callStore.remoteStream, (newStream) => {
  if (newStream && remoteVideoRef.value) {
    remoteVideoRef.value.srcObject = newStream
  }
}, { immediate: true })

watch(() => callStore.localStream, (newStream) => {
  if (newStream && localVideoRef.value) {
    localVideoRef.value.srcObject = newStream
  }
}, { immediate: true })

// Watch call status to handle sounds and initiation
// Watch specifically for permission changes while ringing
watch(() => callStore.hasPermissions, async (hasPerms) => {
  if (hasPerms === true && callStore.callStatus === 'ringing' && !callStore.isIncoming) {
    try {
      await callStore.startLocalStream()
      await callStore.makeOffer()
    } catch (err) {
      callStore.resetCall()
    }
  }
})

watch(() => callStore.callStatus, async (newStatus, oldStatus) => {
  
  await nextTick() // Ensure DOM is updated if v-if changed

  // Stop all sounds first when status changes
  if (dialingAudio.value) dialingAudio.value.pause()
  if (ringingAudio.value) ringingAudio.value.pause()

  if (newStatus === 'ringing') {
    if (callStore.isIncoming) {
      playAudio(ringingAudio.value)
    } else {
      playAudio(dialingAudio.value)
      
      // If we just entered ringing state and we are the caller and ALREADY have permissions
      if (callStore.hasPermissions === true && (oldStatus === 'idle' || oldStatus === null || oldStatus === undefined)) {
        try {
          await callStore.startLocalStream()
          await callStore.makeOffer()
        } catch (err) {
          callStore.resetCall()
        }
      }
    }
  } else if (newStatus === 'active') {
    // Call active
  } else if (newStatus === 'idle' && oldStatus !== 'idle') {
    playEndCallSound()
  }
}, { immediate: true })

onMounted(() => {
  // Global component mounted
})

onUnmounted(() => {
  if (callStore.isActive) {
    callStore.resetCall()
  }
  if (dialingAudio.value) dialingAudio.value.pause()
  if (ringingAudio.value) ringingAudio.value.pause()
})
</script>

<template>
  <div class="call-system-container">
    <!-- Audio elements (Local files for max stability & bypass CSP/403) -->
    <audio ref="dialingAudio" loop preload="auto" src="/sounds/dialing.mp3"></audio>
    <audio ref="ringingAudio" loop preload="auto" src="/sounds/ringing.mp3"></audio>
    <audio ref="endCallAudio" preload="auto" src="/sounds/end.mp3"></audio>

    <div v-if="callStore.isActive || callStore.isRinging" 
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-md overflow-hidden animate-fade-in">
      
      <!-- Permission Warning -->
      <div v-if="callStore.hasPermissions === false && !callStore.debugBypassPermissions" class="absolute inset-0 z-[10000] bg-black/80 flex flex-col items-center justify-center p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-500/20 flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
            <path d="M19 10v1a7 7 0 0 1-14 0v-1" />
            <line x1="12" y1="19" x2="12" y2="22" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Quyền truy cập bị chặn</h3>
        <p class="text-gray-400 mb-6 max-w-xs">Hệ thống cần quyền sử dụng Micro và Camera để thực hiện cuộc gọi.</p>
        <button @click="handleRetry" 
                class="relative z-[10001] px-8 py-3 bg-teal-500 hover:bg-teal-600 active:scale-95 text-white rounded-full font-bold shadow-2xl transition-all cursor-pointer">
          Cho phép lại
        </button>
      </div>

      <!-- Background Content (Blurred Partner Avatar for Audio) -->
      <div v-if="callStore.callType === 'audio' || isCameraOff" 
           class="absolute inset-0 opacity-20 scale-110">
        <img :src="callStore.partner?.avatar || '/images/default-avatar.png'" 
             class="w-full h-full object-cover blur-3xl" />
      </div>

      <!-- Main Call UI -->
      <div class="relative w-full h-full flex flex-col items-center justify-between py-16 px-6 max-w-lg mx-auto">
        
        <!-- Partner Info (Top) -->
        <div class="flex flex-col items-center text-center animate-slide-down">
          <div class="relative mb-6">
            <div v-if="callStore.callStatus === 'ringing'" 
                 class="absolute -inset-4 rounded-full border border-teal-500/30 animate-ping" />
            <div v-if="callStore.callStatus === 'ringing'" 
                 class="absolute -inset-8 rounded-full border border-teal-500/20 animate-ping delay-700" />
            
            <img :src="callStore.partner?.avatar || '/images/default-avatar.png'" 
                 class="w-32 h-32 rounded-full border-4 border-white/10 shadow-2xl relative z-10 object-cover" />
          </div>
          
          <h2 class="text-2xl font-bold text-white mb-2">{{ callStore.partner?.name || 'Người dùng thử nghiệm' }}</h2>
          <p class="text-teal-400 font-medium">
            {{ callStore.callStatus === 'ringing' ? (callStore.isIncoming ? (t('common.chat.incoming_call') || 'Đang có cuộc gọi đến...') : (t('common.chat.ringing') || 'Đang đổ chuông...')) : formattedDuration }}
          </p>

          <!-- TEST SOUND BUTTON (Only in Debug Mode) -->
          <div v-if="callStore.debugBypassPermissions" class="mt-4 flex gap-2">
            <button @click="handleTestSound('dialing')" class="text-[10px] bg-white/20 px-2 py-1 rounded text-white">Test Dialing</button>
            <button @click="handleTestSound('ringing')" class="text-[10px] bg-white/20 px-2 py-1 rounded text-white">Test Ringing</button>
          </div>
        </div>

        <!-- Video Display (Center) -->
        <div v-if="callStore.callType === 'video'" class="absolute inset-0 z-0">
          <!-- Remote Video -->
          <video ref="remoteVideoRef" autoplay playsinline class="w-full h-full object-cover" />
          
          <!-- Local Video PiP -->
          <div class="absolute top-8 right-8 w-32 h-44 rounded-2xl overflow-hidden border-2 border-white/20 shadow-2xl bg-black">
            <video ref="localVideoRef" autoplay playsinline muted class="w-full h-full object-cover mirror" />
            <div v-if="isCameraOff" class="absolute inset-0 flex items-center justify-center bg-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2m5.66 0H14a2 2 0 0 1 2 2v3.34l1 1L23 7v10" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Call Controls (Bottom) -->
        <div class="relative z-10 flex flex-col gap-8 w-full items-center animate-slide-up">
          
          <!-- Incoming Call Actions -->
          <div v-if="callStore.isIncoming && callStore.isRinging" class="flex gap-16">
            <button @click="callStore.rejectCall" 
                    class="w-16 h-16 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center text-white shadow-lg transition-transform hover:scale-110 active:scale-95">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="m3 21 2.22-2.22m.001 0a12 12 0 0 1 16.44-16.441l.343.345a2.22 2.22 0 0 1 0 3.14l-2.215 2.215a2.22 2.22 0 0 1-3.14 0l-1.033-1.033c-.092-.092-.224-.132-.348-.107a6 6 0 0 0-3.66 3.66c-.025.124.015.256.107.348l1.033 1.033a2.22 2.22 0 0 1 0 3.14l-2.215 2.215a2.22 2.22 0 0 1-3.14 0l-.343-.345Z"/>
              </svg>
            </button>
            
            <button @click="callStore.acceptCall" 
                    class="w-16 h-16 rounded-full bg-green-500 hover:bg-green-600 flex items-center justify-center text-white shadow-lg transition-transform hover:scale-110 active:scale-95 animate-bounce">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.501 19.501 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
            </button>
          </div>

          <!-- Active Call Actions -->
          <div v-else class="flex flex-col items-center gap-10 w-full">
            <div class="flex items-center gap-8 px-8 py-4 bg-white/10 rounded-full backdrop-blur-xl border border-white/10 shadow-2xl">
              <!-- Mute Toggle -->
              <button @click="toggleMute" 
                      :class="['w-12 h-12 rounded-full flex items-center justify-center transition-all', isMuted ? 'bg-amber-500 text-white' : 'text-white hover:bg-white/10']">
                <svg v-if="!isMuted" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                  <path d="M19 10v1a7 7 0 0 1-14 0v-1" />
                  <line x1="12" y1="19" x2="12" y2="22" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="2" y1="2" x2="22" y2="22" />
                  <path d="M18.89 12a10 10 0 0 1-16.89-6" />
                  <path d="M12 2a3 3 0 0 1 3 3v5" />
                  <path d="M19 10v1a7.1 7.1 0 0 1-1.07 3.77" />
                  <path d="M12 19v3" />
                </svg>
              </button>

              <!-- Video/Speaker Toggle -->
              <button v-if="callStore.callType === 'video'" @click="toggleCamera"
                      :class="['w-12 h-12 rounded-full flex items-center justify-center transition-all', isCameraOff ? 'bg-amber-500 text-white' : 'text-white hover:bg-white/10']">
                 <svg v-if="!isCameraOff" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m22 8-6 4 6 4V8Z" />
                  <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2m5.66 0H14a2 2 0 0 1 2 2v3.34l1 1L23 7v10" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
              </button>

              <!-- End Call -->
              <button @click="handleEndCall" 
                      class="w-16 h-16 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center text-white shadow-lg transition-transform hover:scale-110 active:scale-95 mx-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="m3 21 2.22-2.22m.001 0a12 12 0 0 1 16.44-16.441l.343.345a2.22 2.22 0 0 1 0 3.14l-2.215 2.215a2.22 2.22 0 0 1-3.14 0l-1.033-1.033c-.092-.092-.224-.132-.348-.107a6 6 0 0 0-3.66 3.66c-.025.124.015.256.107.348l1.033 1.033a2.22 2.22 0 0 1 0 3.14l-2.215 2.215a2.22 2.22 0 0 1-3.14 0l-.343-.345Z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
.animate-slide-down { animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.animate-slide-up { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.mirror { transform: scaleX(-1); }
</style>
