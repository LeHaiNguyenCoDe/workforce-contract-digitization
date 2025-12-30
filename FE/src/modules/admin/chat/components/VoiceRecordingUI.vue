<template>
    <div class="voice-recording">
        <!-- Cancel Button -->
        <button @click="$emit('cancel')" class="voice-recording__cancel" title="Hủy">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

        <!-- Waveform Visualization -->
        <div class="voice-recording__waveform">
            <div v-for="(level, i) in displayLevels" :key="i" class="voice-recording__bar"
                :style="{ height: `${Math.max(4, level * 100)}%` }" />
        </div>

        <!-- Timer -->
        <div class="voice-recording__timer">
            {{ formattedDuration }}
        </div>

        <!-- Send Button -->
        <button @click="$emit('send')" class="voice-recording__send" title="Gửi">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
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

// Show last 40 levels for waveform display
const displayLevels = computed(() => {
    const levels = props.audioLevels.slice(-40)
    // Pad with zeros if not enough data
    while (levels.length < 40) {
        levels.unshift(0)
    }
    return levels
})
</script>

<style scoped>
.voice-recording {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #14b8a6 0%, #0ea5e9 100%);
    border-radius: 24px;
    width: 100%;
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.voice-recording__cancel {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
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
    height: 36px;
}

.voice-recording__bar {
    width: 3px;
    min-height: 4px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 2px;
    transition: height 0.1s ease;
}

.voice-recording__timer {
    flex-shrink: 0;
    padding: 4px 10px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    font-family: monospace;
}

.voice-recording__send {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: none;
    border-radius: 50%;
    color: #14b8a6;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
}

.voice-recording__send:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.voice-recording__send:active {
    transform: scale(0.95);
}
</style>
