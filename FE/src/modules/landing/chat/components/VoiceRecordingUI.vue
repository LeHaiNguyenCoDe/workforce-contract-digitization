<template>
    <div class="voice-recording">
        <button @click="$emit('cancel')" class="voice-recording__cancel" title="Hủy">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

        <div class="voice-recording__waveform">
            <div v-for="(level, i) in displayLevels" :key="i" class="voice-recording__bar"
                :style="{ height: `${Math.max(4, level * 100)}%` }" />
        </div>

        <div class="voice-recording__timer">{{ formattedDuration }}</div>

        <button @click="$emit('send')" class="voice-recording__send" title="Gửi">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    formattedDuration: string
    audioLevels: number[]
}>()

defineEmits<{
    (e: 'cancel'): void
    (e: 'send'): void
}>()

const displayLevels = computed(() => {
    const levels = props.audioLevels.slice(-30)
    while (levels.length < 30) levels.unshift(0)
    return levels
})
</script>

<style scoped>
.voice-recording {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    background: linear-gradient(135deg, #14b8a6 0%, #0ea5e9 100%);
    border-radius: 20px;
    width: 100%;
}

.voice-recording__cancel {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
}

.voice-recording__cancel:hover {
    background: rgba(255, 255, 255, 0.3);
}

.voice-recording__waveform {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    height: 28px;
}

.voice-recording__bar {
    width: 2px;
    min-height: 3px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 1px;
    transition: height 0.1s;
}

.voice-recording__timer {
    padding: 3px 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    color: white;
    font-size: 11px;
    font-weight: 600;
    font-family: monospace;
}

.voice-recording__send {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: none;
    border-radius: 50%;
    color: #14b8a6;
    cursor: pointer;
}

.voice-recording__send:hover {
    transform: scale(1.05);
}
</style>
