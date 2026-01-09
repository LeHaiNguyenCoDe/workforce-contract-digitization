<template>
    <div class="emoji-picker">
        <!-- Search -->
        <div class="emoji-picker__search">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm..."
                class="emoji-picker__input" />
        </div>

        <!-- Categories -->
        <div class="emoji-picker__categories">
            <button v-for="cat in categories" :key="cat.id" @click="activeCategory = cat.id"
                :class="['emoji-picker__category-btn', { active: activeCategory === cat.id }]" :title="cat.name">
                {{ cat.icon }}
            </button>
        </div>

        <!-- Emoji Grid -->
        <div class="emoji-picker__grid">
            <div v-if="filteredEmojis.length === 0" class="emoji-picker__empty">
                Không tìm thấy
            </div>
            <button v-for="emoji in filteredEmojis" :key="emoji" @click="$emit('select', emoji)"
                class="emoji-picker__emoji">
                {{ emoji }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

defineEmits<{
    (e: 'select', emoji: string): void
    (e: 'close'): void
}>()

const searchQuery = ref('')
const activeCategory = ref('smileys')

const categories = [
    { id: 'recent', name: 'Gần đây', icon: '🕐', emojis: [] as string[] },
    {
        id: 'smileys', name: 'Mặt cười', icon: '😊',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐']
    },
    {
        id: 'gestures', name: 'Cử chỉ', icon: '👋',
        emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪']
    },
    {
        id: 'symbols', name: 'Biểu tượng', icon: '❤️',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '✨', '⭐', '🌟', '💫', '⚡', '🔥', '💥', '🎉', '🎊', '✅', '❌', '❓', '❗', '💯']
    },
    {
        id: 'objects', name: 'Đối tượng', icon: '💡',
        emojis: ['📱', '💻', '🖥️', '📷', '📹', '🎥', '📞', '☎️', '📺', '📻', '🎙️', '⏰', '⌚', '💡', '🔦', '💰', '💵', '💳', '💎', '🔧', '🔨', '⚙️', '🔌', '🔋', '🎮', '🎲', '🎯', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹']
    }
]

const recentEmojis = ref<string[]>([])
try {
    recentEmojis.value = JSON.parse(localStorage.getItem('recentEmojis') || '[]')
} catch { /* ignore */ }

categories[0].emojis = recentEmojis.value

const filteredEmojis = computed(() => {
    const query = searchQuery.value.toLowerCase()
    if (query) {
        return categories.flatMap(cat => cat.emojis).filter((e, i, self) => self.indexOf(e) === i).slice(0, 48)
    }
    const category = categories.find(c => c.id === activeCategory.value)
    if (category?.id === 'recent') return recentEmojis.value
    return category?.emojis || []
})
</script>
