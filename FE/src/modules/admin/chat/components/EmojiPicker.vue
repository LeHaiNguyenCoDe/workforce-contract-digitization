<template>
    <div class="emoji-picker">
        <!-- Search -->
        <div class="emoji-picker__search">
            <input v-model="searchQuery" type="text" :placeholder="t('common.search') || 'Tìm kiếm biểu tượng cảm xúc'"
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
        <div class="emoji-picker__grid" ref="gridRef">
            <div v-if="filteredEmojis.length === 0" class="emoji-picker__empty">
                Không tìm thấy biểu tượng
            </div>
            <button v-for="emoji in filteredEmojis" :key="emoji" @click="$emit('select', emoji)"
                class="emoji-picker__emoji" :title="emoji">
                {{ emoji }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const emit = defineEmits<{
    (e: 'select', emoji: string): void
    (e: 'close'): void
}>()

const { t } = useI18n()

const searchQuery = ref('')
const activeCategory = ref('smileys')
const gridRef = ref<HTMLElement>()

// Emoji categories with common emojis
const categories = [
    { id: 'recent', name: 'Gần đây', icon: '🕐', emojis: [] as string[] },
    {
        id: 'smileys', name: 'Mặt cười', icon: '😊',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐']
    },
    {
        id: 'gestures', name: 'Cử chỉ', icon: '👋',
        emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄']
    },
    {
        id: 'people', name: 'Người', icon: '👤',
        emojis: ['👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👩', '🧓', '👴', '👵', '🙍', '🙎', '🙅', '🙆', '💁', '🙋', '🧏', '🙇', '🤦', '🤷', '👮', '🕵️', '💂', '🥷', '👷', '🤴', '👸', '👳', '👲', '🧕', '🤵', '👰', '🤰', '🤱', '👼', '🎅', '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜', '🧝', '🧞', '🧟', '💆', '💇', '🚶', '🧍', '🧎', '🏃', '💃', '🕺', '🕴️', '👯', '🧖', '🧗', '🤸', '🏌️']
    },
    {
        id: 'animals', name: 'Động vật', icon: '🐶',
        emojis: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🦟', '🦗', '🕷️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆']
    },
    {
        id: 'food', name: 'Đồ ăn', icon: '🍔',
        emojis: ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🫑', '🌽', '🥕', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🌭', '🍔', '🍟', '🍕', '🫓', '🥪', '🥙', '🧆', '🌮', '🌯', '🫔', '🥗', '🥘', '🫕', '🍝', '🍜', '🍲', '🍛', '🍣']
    },
    {
        id: 'activities', name: 'Hoạt động', icon: '⚽',
        emojis: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛼', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️', '🤼', '🤸', '⛹️', '🤺', '🤾', '🏌️', '🏇', '⛵', '🚣', '🏊', '🚴', '🚵', '🎯', '🎮', '🕹️', '🎰', '🧩', '♟️', '🎲', '🎭', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🎷']
    },
    {
        id: 'objects', name: 'Đối tượng', icon: '💡',
        emojis: ['⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💽', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥', '📽️', '🎞️', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️', '🎛️', '🧭', '⏱️', '⏲️', '⏰', '🕰️', '⌛', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯️', '🪔', '🧯', '🛢️', '💸', '💵', '💴', '💶', '💷', '🪙', '💰', '💳', '💎', '⚖️', '🪜', '🧰', '🔧', '🔨', '⚒️', '🛠️', '⛏️', '🪚']
    },
    {
        id: 'symbols', name: 'Biểu tượng', icon: '❤️',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹']
    },
    {
        id: 'flags', name: 'Cờ', icon: '🏳️',
        emojis: ['🏳️', '🏴', '🏁', '🚩', '🏳️‍🌈', '🏳️‍⚧️', '🇺🇳', '🇻🇳', '🇺🇸', '🇬🇧', '🇫🇷', '🇩🇪', '🇯🇵', '🇰🇷', '🇨🇳', '🇷🇺', '🇧🇷', '🇮🇳', '🇦🇺', '🇨🇦', '🇮🇹', '🇪🇸', '🇲🇽', '🇸🇬', '🇹🇭', '🇲🇾', '🇮🇩', '🇵🇭']
    }
]

// Recently used emojis from localStorage
const recentEmojis = ref<string[]>(loadRecentEmojis())

function loadRecentEmojis(): string[] {
    try {
        return JSON.parse(localStorage.getItem('recentEmojis') || '[]')
    } catch {
        return []
    }
}

function saveRecentEmoji(emoji: string) {
    const recent = recentEmojis.value.filter(e => e !== emoji)
    recent.unshift(emoji)
    recentEmojis.value = recent.slice(0, 24)
    localStorage.setItem('recentEmojis', JSON.stringify(recentEmojis.value))
}

// Update recent category emojis
categories[0].emojis = recentEmojis.value

const filteredEmojis = computed(() => {
    const query = searchQuery.value.toLowerCase()

    if (query) {
        // Search across all categories
        return categories
            .flatMap(cat => cat.emojis)
            .filter((emoji, index, self) => self.indexOf(emoji) === index)
            .slice(0, 60)
    }

    const category = categories.find(c => c.id === activeCategory.value)
    if (category?.id === 'recent') {
        return recentEmojis.value
    }
    return category?.emojis || []
})
</script>

<style scoped>
.emoji-picker {
    width: 320px;
    max-height: 350px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.emoji-picker__search {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
}

.emoji-picker__input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

.emoji-picker__input:focus {
    border-color: #14b8a6;
}

.emoji-picker__categories {
    display: flex;
    padding: 6px 8px;
    gap: 2px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
    overflow-x: auto;
}

.emoji-picker__category-btn {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.emoji-picker__category-btn:hover {
    background: #e5e7eb;
}

.emoji-picker__category-btn.active {
    background: #14b8a6;
    color: white;
}

.emoji-picker__grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 2px;
    padding: 8px;
    overflow-y: auto;
    max-height: 240px;
}

.emoji-picker__emoji {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
}

.emoji-picker__emoji:hover {
    background: #e5e7eb;
    transform: scale(1.15);
}

.emoji-picker__empty {
    grid-column: span 8;
    text-align: center;
    padding: 20px;
    color: #9ca3af;
    font-size: 13px;
}

/* Custom scrollbar */
.emoji-picker__grid::-webkit-scrollbar {
    width: 6px;
}

.emoji-picker__grid::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.emoji-picker__grid::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.emoji-picker__grid::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
