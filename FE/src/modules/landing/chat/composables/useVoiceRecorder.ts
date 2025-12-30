import { ref, computed, onUnmounted } from 'vue'

export interface VoiceRecorderState {
  isRecording: boolean
  isPaused: boolean
  duration: number // in seconds
  audioBlob: Blob | null
  audioUrl: string | null
  error: string | null
}

export function useVoiceRecorder() {
  // State
  const isRecording = ref(false)
  const isPaused = ref(false)
  const duration = ref(0)
  const audioBlob = ref<Blob | null>(null)
  const audioUrl = ref<string | null>(null)
  const error = ref<string | null>(null)
  const audioLevels = ref<number[]>([])

  // Internal refs
  let mediaRecorder: MediaRecorder | null = null
  let audioStream: MediaStream | null = null
  let audioContext: AudioContext | null = null
  let analyser: AnalyserNode | null = null
  let durationInterval: ReturnType<typeof setInterval> | null = null
  let levelInterval: ReturnType<typeof setInterval> | null = null
  const audioChunks: Blob[] = []

  // Computed
  const formattedDuration = computed(() => {
    const mins = Math.floor(duration.value / 60)
    const secs = duration.value % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  })

  const isSupported = computed(() => {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)
  })

  // Methods
  async function startRecording(): Promise<boolean> {
    if (!isSupported.value) {
      error.value = 'Voice recording is not supported in this browser'
      return false
    }

    try {
      // Request microphone access
      audioStream = await navigator.mediaDevices.getUserMedia({ 
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          sampleRate: 44100
        }
      })

      // Create audio context for level visualization
      audioContext = new AudioContext()
      analyser = audioContext.createAnalyser()
      analyser.fftSize = 256
      const source = audioContext.createMediaStreamSource(audioStream)
      source.connect(analyser)

      // Create media recorder
      const mimeType = MediaRecorder.isTypeSupported('audio/webm') 
        ? 'audio/webm' 
        : 'audio/mp4'
      
      mediaRecorder = new MediaRecorder(audioStream, { mimeType })
      
      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          audioChunks.push(event.data)
        }
      }

      mediaRecorder.onstop = () => {
        const blob = new Blob(audioChunks, { type: mimeType })
        audioBlob.value = blob
        audioUrl.value = URL.createObjectURL(blob)
      }

      // Start recording
      audioChunks.length = 0
      mediaRecorder.start(100) // Collect data every 100ms
      isRecording.value = true
      isPaused.value = false
      duration.value = 0
      error.value = null

      // Start duration timer
      durationInterval = setInterval(() => {
        if (!isPaused.value) {
          duration.value++
        }
      }, 1000)

      // Start audio level monitoring
      const dataArray = new Uint8Array(analyser.frequencyBinCount)
      levelInterval = setInterval(() => {
        if (analyser && !isPaused.value) {
          analyser.getByteFrequencyData(dataArray)
          // Get average level
          const sum = dataArray.reduce((a, b) => a + b, 0)
          const avg = sum / dataArray.length
          // Keep last 50 levels for waveform
          audioLevels.value = [...audioLevels.value.slice(-49), avg / 255]
        }
      }, 50)

      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to start recording'
      console.error('Voice recording error:', err)
      return false
    }
  }

  function pauseRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.pause()
      isPaused.value = true
    }
  }

  function resumeRecording() {
    if (mediaRecorder && mediaRecorder.state === 'paused') {
      mediaRecorder.resume()
      isPaused.value = false
    }
  }

  function stopRecording(): Blob | null {
    if (durationInterval) {
      clearInterval(durationInterval)
      durationInterval = null
    }
    if (levelInterval) {
      clearInterval(levelInterval)
      levelInterval = null
    }

    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop()
    }

    if (audioStream) {
      audioStream.getTracks().forEach(track => track.stop())
      audioStream = null
    }

    if (audioContext) {
      audioContext.close()
      audioContext = null
      analyser = null
    }

    isRecording.value = false
    isPaused.value = false

    return audioBlob.value
  }

  function cancelRecording() {
    stopRecording()
    audioBlob.value = null
    if (audioUrl.value) {
      URL.revokeObjectURL(audioUrl.value)
      audioUrl.value = null
    }
    duration.value = 0
    audioLevels.value = []
  }

  function getAudioFile(fileName = 'voice-message.webm'): File | null {
    if (!audioBlob.value) return null
    return new File([audioBlob.value], fileName, { type: audioBlob.value.type })
  }

  // Cleanup on unmount
  onUnmounted(() => {
    cancelRecording()
  })

  return {
    // State
    isRecording,
    isPaused,
    duration,
    formattedDuration,
    audioBlob,
    audioUrl,
    audioLevels,
    error,
    isSupported,
    // Methods
    startRecording,
    pauseRecording,
    resumeRecording,
    stopRecording,
    cancelRecording,
    getAudioFile
  }
}
