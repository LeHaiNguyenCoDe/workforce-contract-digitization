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

<style scoped>
.emoji-picker {
    width: 280px;
    max-height: 300px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.emoji-picker__search {
    padding: 8px 10px;
    border-bottom: 1px solid #f3f4f6;
}

.emoji-picker__input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 12px;
    outline: none;
}

.emoji-picker__input:focus {
    border-color: #14b8a6;
}

.emoji-picker__categories {
    display: flex;
    padding: 4px 6px;
    gap: 2px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.emoji-picker__category-btn {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
}

.emoji-picker__category-btn:hover {
    background: #e5e7eb;
}

.emoji-picker__category-btn.active {
    background: #14b8a6;
}

.emoji-picker__grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 2px;
    padding: 6px;
    overflow-y: auto;
    max-height: 200px;
}

.emoji-picker__emoji {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border: none;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
}

.emoji-picker__emoji:hover {
    background: #e5e7eb;
    transform: scale(1.1);
}

.emoji-picker__empty {
    grid-column: span 8;
    text-align: center;
    padding: 16px;
    color: #9ca3af;
    font-size: 12px;
}
</style>
