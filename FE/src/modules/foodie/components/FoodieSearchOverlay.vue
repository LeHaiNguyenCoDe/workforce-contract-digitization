<script lang="ts">
import { defineComponent, ref, watch, onUnmounted } from 'vue'

export default defineComponent({
  name: 'FoodieSearchOverlay',
  props: {
    isOpen: {
      type: Boolean,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const searchQuery = ref('')
    const categories = ['All', 'Wine', 'Beer', 'Liquor', 'Mixers', 'RTDs', 'Specials', 'Restaurants']
    const activeCategory = ref('All')

    const topSearches = [
      { name: 'Wine', type: 'search' },
      { name: 'Thai Food', type: 'search' },
      { name: 'Beer', type: 'search' },
      { name: 'Vodka', type: 'search' },
      { name: 'Fries', type: 'search' }
    ]

    const handleClose = () => {
      emit('close')
    }

    // Lock scroll when open
    watch(() => props.isOpen, (val) => {
      if (val) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    })

    onUnmounted(() => {
      document.body.style.overflow = ''
    })

    return {
      searchQuery,
      categories,
      activeCategory,
      topSearches,
      handleClose
    }
  }
})
</script>

<template>
  <Transition name="search-fade">
    <div v-if="isOpen" class="foodie-search-overlay">
      <div class="foodie-search-overlay__container">
        <!-- Header -->
        <div class="foodie-search-overlay__header">
          <!-- Logo -->
          <div class="foodie-search-overlay__logo">
            <span class="foodie-search-overlay__logo-text">ch<span>ee</span>rs</span>
          </div>

          <!-- Search Input -->
          <div class="foodie-search-overlay__input-wrapper">
            <svg class="foodie-search-overlay__search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="m21 21-4.35-4.35"/>
            </svg>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Beer, Wine, Food, etc"
              autofocus
            />
          </div>

          <!-- Close Button -->
          <button class="foodie-search-overlay__close" @click="handleClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="foodie-search-overlay__content">
          <!-- Categories -->
          <div class="foodie-search-overlay__categories">
            <button 
              v-for="cat in categories" 
              :key="cat"
              class="foodie-search-overlay__cat-btn"
              :class="{ 'active': activeCategory === cat }"
              @click="activeCategory = cat"
            >
              {{ cat }}
            </button>
          </div>

          <!-- Top Searches -->
          <div class="foodie-search-overlay__results">
            <h3 class="foodie-search-overlay__section-title">Top Searches</h3>
            <div class="foodie-search-overlay__list">
              <div 
                v-for="item in topSearches" 
                :key="item.name"
                class="foodie-search-overlay__item"
              >
                <div class="foodie-search-overlay__item-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                  </svg>
                </div>
                <span class="foodie-search-overlay__item-name">{{ item.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style lang="scss" scoped>
.search-fade-enter-active, .search-fade-leave-active {
  transition: opacity 0.3s ease;
}
.search-fade-enter-from, .search-fade-leave-to {
  opacity: 0;
}
</style>
