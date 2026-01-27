import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { ChatService } from '../services/ChatService'
import type { IUser } from '../models/Chat'

export type CallStatus = 'idle' | 'ringing' | 'connecting' | 'active' | 'ended' | 'rejected' | 'busy'
export type CallType = 'audio' | 'video'

export const useCallStore = defineStore('call', () => {
  // State
  const callStatus = ref<CallStatus>('idle')
  const callType = ref<CallType>('audio')
  const conversationId = ref<number | null>(null)
  const caller = ref<IUser | null>(null)
  const receiver = ref<IUser | null>(null)
  const isIncoming = ref(false)
  
  const localStream = ref<MediaStream | null>(null)
  const remoteStream = ref<MediaStream | null>(null)
  
  const callDuration = ref(0)
  let timerInterval: any = null
  const hasPermissions = ref<boolean | null>(null)
  const debugBypassPermissions = ref(false)
  const ringingStartTime = ref<number>(0)
  
  // Expose to window for easy console debugging
  if (typeof window !== 'undefined') {
    (window as any).toggleCallDebug = () => {
      debugBypassPermissions.value = !debugBypassPermissions.value
      console.log('CallStore: Debug bypass permissions is now:', debugBypassPermissions.value)
      return debugBypassPermissions.value
    }
  }
  const pendingOffer = ref<RTCSessionDescriptionInit | null>(null)
  const pendingCandidates = ref<RTCIceCandidateInit[]>([])

  // WebRTC Core
  const peerConnection = ref<RTCPeerConnection | null>(null)
  const configuration: RTCConfiguration = {
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
    ],
    iceCandidatePoolSize: 10
  }

  // Getters
  const isActive = computed(() => callStatus.value === 'active' || callStatus.value === 'connecting')
  const isRinging = computed(() => callStatus.value === 'ringing')
  const partner = computed(() => isIncoming.value ? caller.value : receiver.value)

  // Actions
  async function checkPermissions(forceVideo = false) {
    if (debugBypassPermissions.value) {
      console.log('[CallStore] DEBUG MODE: Bypassing real permission check')
      hasPermissions.value = true
      return true
    }
    const videoRequired = forceVideo || callType.value === 'video'
    console.log(`[CallStore] checkPermissions - Target: (audio: true, video: ${videoRequired})`)
    
    // Safety: check if API is available
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      console.error('[CallStore] navigator.mediaDevices.getUserMedia is NOT supported in this browser/context')
      alert('Trình duyệt của bạn không hỗ trợ hoặc chặn quyền truy cập thiết bị (Micro/Camera). Hãy đảm bảo bạn đang dùng HTTPS.')
      hasPermissions.value = false
      return false
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        audio: true,
        video: videoRequired
      })
      console.log('[CallStore] getUserMedia SUCCESS - permissions granted')
      stream.getTracks().forEach(track => track.stop())
      hasPermissions.value = true
      return true
    } catch (error: any) {
      console.error('[CallStore] getUserMedia FAILED:', error.name, error.message)
      hasPermissions.value = false
      
      if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
        console.warn('[CallStore] User explicitly denied permissions')
      } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
        console.warn('[CallStore] Required hardware not found')
        alert('Không tìm thấy thiết bị Micro hoặc Camera yêu cầu.')
      }
      
      return false
    }
  }

  async function initiateCall(convId: number, targetUser: IUser, type: CallType) {
    console.log('CallStore: initiateCall clicked for', targetUser.name, 'type:', type)
    if (callStatus.value !== 'idle') {
      console.warn('CallStore: Cannot initiate call, system is busy with status:', callStatus.value)
      return
    }

    // Reset queues and state
    pendingOffer.value = null
    pendingCandidates.value = []
    
    // Set basic info and enter ringing state so UI shows up immediately
    conversationId.value = convId
    receiver.value = targetUser
    callType.value = type
    isIncoming.value = false
    callStatus.value = 'ringing'
    ringingStartTime.value = Date.now()
    console.log('[CallStore] UI showing (ringing state). Timeout is 60s.')

    // Check permissions...
    if (hasPermissions.value !== true && !debugBypassPermissions.value) {
      console.log('CallStore: Permissions not ready, requesting...')
      const granted = await checkPermissions()
      if (!granted) {
        console.warn('CallStore: Permissions denied. UI will show warning overlay.')
        return // UI stays at ringing, overlay shows
      }
    }

    // Proceed with API call if permissions granted
    try {
      console.log('CallStore: Permissions OK, sending initiation signal to server')
      await ChatService.initiateCall(convId, targetUser.id, type)
      startTimeoutTimer(60000) // Increase to 60s
      console.log('CallStore: Call initiation signal sent successfully')
    } catch (error) {
      console.error('CallStore: Failed to initiate call API:', error)
      resetCall('initiation_error')
    }
  }

  function handleIncomingCall(data: { conversation_id: number, from_user_id: number, call_type: CallType, caller: IUser }) {
    console.log('[CallStore] handleIncomingCall received:', data)
    
    // If already in a call OR ringing, check if it's the SAME call to avoid self-rejection
    if (callStatus.value !== 'idle') {
      const isSameCall = Number(conversationId.value) === Number(data.conversation_id) && 
                         Number(partner.value?.id) === Number(data.from_user_id)
      
      if (isSameCall) {
        console.log('[CallStore] Ignoring duplicate incoming call signal for same session')
        return
      }
      
      console.warn('[CallStore] System busy, rejecting incoming call from:', data.from_user_id)
      ChatService.updateCallStatus(data.conversation_id, 'busy', data.call_type, data.from_user_id)
      return
    }

    conversationId.value = data.conversation_id
    caller.value = data.caller
    callType.value = data.call_type
    isIncoming.value = true
    callStatus.value = 'ringing'
    ringingStartTime.value = Date.now()
    startTimeoutTimer(60000)
  }

  async function acceptCall() {
    console.log('CallStore: Accepting call')
    if (!conversationId.value || !partner.value) {
      console.error('CallStore: Missing conversationId or partner on accept')
      return
    }
    
    // Check permissions before accepting
    if (hasPermissions.value !== true) {
      const granted = await checkPermissions()
      if (!granted) {
        alert('Cần quyền Mic/Camera để nhận cuộc gọi.')
        return
      }
    }

    callStatus.value = 'connecting'
    console.log('CallStore: Setting status to connecting and informing server')
    await ChatService.updateCallStatus(conversationId.value, 'accepted', callType.value, partner.value.id)
    
    // Start local stream for receiver
    try {
      await startLocalStream()
      
      // If we already received an offer, process it now
      if (pendingOffer.value) {
        console.log('CallStore: Processing pending offer after acceptance')
        await processOffer(pendingOffer.value)
      } else {
        console.log('CallStore: No pending offer yet, receiver waiting for signaling from caller...')
      }
    } catch (err) {
      console.error('CallStore: Failed to start local stream after acceptance:', err)
      endCall()
    }
  }

  async function rejectCall() {
    if (!conversationId.value || !partner.value) {
      resetCall('reject_no_context')
      return
    }
    
    await ChatService.updateCallStatus(conversationId.value, 'rejected', callType.value, partner.value.id)
    resetCall('user_rejected')
  }

  async function endCall() {
    if (!conversationId.value || !partner.value) {
      resetCall('end_no_context')
      return
    }
    
    await ChatService.updateCallStatus(conversationId.value, 'ended', callType.value, partner.value.id)
    resetCall('user_ended')
  }

  let isResetting = false
  function resetCall(reason = 'unknown') {
    if (isResetting) return
    
    // CRITICAL: Ignore 'busy' signals for the first 3 seconds of a call to avoid race conditions
    const now = Date.now()
    if (reason === 'partner_busy' && (now - ringingStartTime.value < 3000)) {
      console.log('[CallStore] Ignoring "busy" signal during 3s safe-start period')
      return
    }

    isResetting = true
    console.log(`[CallStore] resetCall called. Reason: ${reason}`)
    
    // Show alert only for unexpected resets (not from user clicking end/reject)
    const silentReasons = ['user_ended', 'user_rejected', 'idle', 'unknown']
    if (!silentReasons.includes(reason) && callStatus.value !== 'idle') {
      console.warn(`[CallStore] AUTO-RESET triggered by: ${reason}`)
      // Temporarily alert to help user debug
      // window.alert(`Cuộc gọi tự ngắt: ${reason}`)
    }

    stopTimer()
    if (timeoutTimer) {
      clearTimeout(timeoutTimer)
      timeoutTimer = null
    }
    
    if (localStream.value) {
      localStream.value.getTracks().forEach(track => {
        track.stop()
        console.log(`CallStore: Stopped track ${track.kind}`)
      })
    }
    
    if (peerConnection.value) {
      peerConnection.value.close()
      peerConnection.value = null
      console.log('CallStore: Closed PeerConnection')
    }
    
    callStatus.value = 'idle'
    isResetting = false
    conversationId.value = null
    caller.value = null
    receiver.value = null
    isIncoming.value = false
    localStream.value = null
    remoteStream.value = null
    callDuration.value = 0
    pendingOffer.value = null
    pendingCandidates.value = []
  }

  // WebRTC Actions
  async function createPeerConnection() {
    console.log('CallStore: Creating PeerConnection')
    if (peerConnection.value) peerConnection.value.close()

    peerConnection.value = new RTCPeerConnection(configuration)

    peerConnection.value.onicecandidate = (event) => {
      if (event.candidate && conversationId.value && partner.value) {
        console.log('CallStore: Sending ICE candidate')
        ChatService.sendCallSignal(conversationId.value, partner.value.id, 'ice-candidate', event.candidate.toJSON())
      }
    }

    peerConnection.value.ontrack = (event) => {
      if (event.streams && event.streams[0]) {
        console.log('CallStore: Received remote stream')
        remoteStream.value = event.streams[0]
      }
    }

    peerConnection.value.oniceconnectionstatechange = () => {
      console.log('CallStore: ICE Connection State:', peerConnection.value?.iceConnectionState)
      if (peerConnection.value?.iceConnectionState === 'connected') {
        callStatus.value = 'active'
        startTimer()
      } else if (peerConnection.value?.iceConnectionState === 'failed' || peerConnection.value?.iceConnectionState === 'disconnected') {
        console.warn('CallStore: ICE connection failed/disconnected')
      }
    }

    if (localStream.value) {
      console.log('CallStore: Adding local tracks to PC')
      localStream.value.getTracks().forEach(track => {
        peerConnection.value?.addTrack(track, localStream.value!)
      })
    }

    return peerConnection.value
  }

  async function startLocalStream() {
    console.log('CallStore: Starting local stream...')
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        audio: true,
        video: callType.value === 'video'
      })
      localStream.value = stream
      hasPermissions.value = true
      console.log('CallStore: Local stream started')
      return stream
    } catch (error) {
      console.error('CallStore: Error accessing media devices:', error)
      hasPermissions.value = false
      throw error
    }
  }

  async function makeOffer() {
    console.log('CallStore: Making offer')
    try {
      const pc = await createPeerConnection()
      const offer = await pc.createOffer()
      await pc.setLocalDescription(offer)

      if (conversationId.value && partner.value) {
        await ChatService.sendCallSignal(conversationId.value, partner.value.id, 'offer', offer)
        console.log('CallStore: Offer sent')
      }
    } catch (err) {
      console.error('CallStore: makeOffer failed', err)
      throw err
    }
  }

  async function handleOffer(offer: RTCSessionDescriptionInit) {
    if (isIncoming.value && (callStatus.value === 'ringing' || callStatus.value === 'idle')) {
      console.log('CallStore: Received offer before acceptance, queueing...')
      pendingOffer.value = offer
      return
    }

    await processOffer(offer)
  }

  async function processOffer(offer: RTCSessionDescriptionInit) {
    console.log('CallStore: Processing offer')
    try {
      const pc = await createPeerConnection()
      await pc.setRemoteDescription(new RTCSessionDescription(offer))
      
      const answer = await pc.createAnswer()
      await pc.setLocalDescription(answer)

      if (conversationId.value && partner.value) {
        await ChatService.sendCallSignal(conversationId.value, partner.value.id, 'answer', answer)
        console.log('CallStore: Answer sent')
      }

      // Process queued candidates
      if (pendingCandidates.value.length > 0) {
        console.log(`CallStore: Processing ${pendingCandidates.value.length} queued candidates`)
        for (const candidate of pendingCandidates.value) {
          try {
            await pc.addIceCandidate(new RTCIceCandidate(candidate))
          } catch (e) {
            console.warn('CallStore: Error adding queued candidate', e)
          }
        }
        pendingCandidates.value = []
      }
    } catch (err) {
      console.error('CallStore: processOffer failed', err)
    }
  }

  async function handleAnswer(answer: RTCSessionDescriptionInit) {
    console.log('CallStore: Received answer')
    if (peerConnection.value) {
      try {
        await peerConnection.value.setRemoteDescription(new RTCSessionDescription(answer))
        console.log('CallStore: Remote description set from answer')
      } catch (err) {
        console.error('CallStore: handleAnswer failed', err)
      }
    }
  }

  async function handleCandidate(candidate: RTCIceCandidateInit) {
    if (!peerConnection.value || !peerConnection.value.remoteDescription) {
      console.log('CallStore: PC or remoteDescription not ready, queueing candidate')
      pendingCandidates.value.push(candidate)
      return
    }

    try {
      await peerConnection.value.addIceCandidate(new RTCIceCandidate(candidate))
      console.log('CallStore: Added ICE candidate')
    } catch (err) {
      console.warn('CallStore: handleCandidate failed', err)
    }
  }

  // Timer logic
  function startTimer() {
    if (timerInterval) clearInterval(timerInterval)
    callDuration.value = 0
    timerInterval = setInterval(() => {
      callDuration.value++
    }, 1000)
  }

  function stopTimer() {
    if (timerInterval) {
      clearInterval(timerInterval)
      timerInterval = null
    }
  }

  let timeoutTimer: any = null
  function startTimeoutTimer(ms: number) {
    if (timeoutTimer) clearTimeout(timeoutTimer)
    timeoutTimer = setTimeout(() => {
      if (callStatus.value === 'ringing') {
        console.log('CallStore: Call timed out')
        endCall()
      }
    }, ms)
  }

  return {
    callStatus,
    callType,
    conversationId,
    caller,
    receiver,
    isIncoming,
    localStream,
    remoteStream,
    callDuration,
    hasPermissions,
    debugBypassPermissions,
    isActive,
    isRinging,
    partner,
    checkPermissions,
    initiateCall,
    handleIncomingCall,
    acceptCall,
    rejectCall,
    endCall,
    resetCall,
    startTimer,
    startLocalStream,
    makeOffer,
    handleOffer,
    handleAnswer,
    handleCandidate
  }
})
