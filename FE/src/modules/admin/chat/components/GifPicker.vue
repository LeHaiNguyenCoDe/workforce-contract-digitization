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
