<template>
  <div class="gif-picker" @click.stop>
    <div class="gif-picker__search">
      <div class="search-input-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Tìm kiếm GIF..." />
      </div>
    </div>
    
    <div class="gif-picker__content">
      <div v-if="loading && gifs.length === 0" class="gif-status">Đang tải...</div>
      <div v-else-if="errorMsg" class="gif-status error">{{ errorMsg }}</div>
      <div v-else-if="gifs.length === 0" class="gif-status">Không tìm thấy GIF nào</div>
      <div v-else class="gif-grid">
        <button v-for="gif in gifs" :key="gif.id" @click="$emit('select', gif.images.fixed_height.url)" class="gif-item">
          <img :src="gif.images.fixed_height_small.url" :alt="gif.title" loading="lazy" />
        </button>
      </div>
    </div>
    
    <div class="gif-picker__footer">
      <span>Powered by GIPHY</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const emit = defineEmits<{
  (e: 'select', url: string): void
  (e: 'close'): void
}>()

const searchQuery = ref('')
const gifs = ref<any[]>([])
const loading = ref(false)
const errorMsg = ref('')
let searchTimeout: any = null

// Use a public beta key for GIPHY (limited rate, but works for demo)
const GIPHY_API_KEY = '0UTLY99t9fXvY6S92oK7N6hNAnzY5N0j'

async function fetchGifs(query = '') {
  loading.value = true
  try {
    const endpoint = query 
      ? `https://api.giphy.com/v1/gifs/search?api_key=${GIPHY_API_KEY}&q=${encodeURIComponent(query)}&limit=20&rating=g`
      : `https://api.giphy.com/v1/gifs/trending?api_key=${GIPHY_API_KEY}&limit=20&rating=g`
    
    const response = await fetch(endpoint)
    const data = await response.json()
    if (response.status === 401 || (data.meta && data.meta.status === 401)) {
      errorMsg.value = 'Giphy API Key không hợp lệ hoặc đã hết hạn (401 Unauthorized). Vui lòng cấu hình key mới tại developers.giphy.com.'
      gifs.value = []
    } else if (data.meta && data.meta.status !== 200) {
      errorMsg.value = `Giphy Error: ${data.meta.msg}`
      gifs.value = []
    } else {
      gifs.value = data.data || []
      errorMsg.value = ''
    }
  } catch (error: any) {
    console.error('Error fetching GIFs:', error)
    if (error.message.includes('401')) {
       errorMsg.value = 'Lỗi xác thực Giphy (401 Unauthorized). Cần sử dụng API key hợp lệ.'
    } else {
       errorMsg.value = `Không thể kết nối đến Giphy: ${error.message}`
    }
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchGifs(searchQuery.value)
  }, 500)
}

onMounted(() => {
  fetchGifs()
})
</script>

<style scoped>
.gif-picker {
  width: 320px;
  height: 400px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.gif-picker__search {
  padding: 12px;
  border-bottom: 1px solid #f3f4f6;
}

.search-input-wrapper {
  display: flex;
  align-items: center;
  background: #f3f4f6;
  border-radius: 20px;
  padding: 0 12px;
  gap: 8px;
  height: 36px;
}

.search-input-wrapper input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14px;
  outline: none;
  color: #374151;
}

.search-input-wrapper svg {
  color: #9ca3af;
}

.gif-picker__content {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.gif-status {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 14px;
}

.gif-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.gif-item {
  border: none;
  background: #f3f4f6;
  padding: 0;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 1;
  transition: transform 0.2s;
}

.gif-item:hover {
  transform: scale(1.02);
}

.gif-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gif-picker__footer {
  padding: 8px;
  text-align: center;
  font-size: 10px;
  color: #9ca3af;
  border-top: 1px solid #f3f4f6;
  font-weight: bold;
}
</style>
