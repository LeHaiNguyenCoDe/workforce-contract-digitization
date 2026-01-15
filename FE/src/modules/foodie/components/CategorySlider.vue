<script setup lang="ts">
import { ref } from 'vue'
import type { CategoryItem } from '../types'

// Props
interface Props {
  categories: CategoryItem[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false
})

// Emit events
const emit = defineEmits<{
  (e: 'select', category: CategoryItem): void
}>()

// Local refs for scroll
const scrollContainer = ref<HTMLElement | null>(null)

const scrollLeft = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: -200, behavior: 'smooth' })
  }
}

const scrollRight = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: 200, behavior: 'smooth' })
  }
}

const handleSelect = (category: CategoryItem) => {
  emit('select', category)
}
</script>

<template>
  <section class="foodie-categories">
    <div class="foodie-categories__container">
      <!-- Left Arrow -->
      <button class="foodie-categories__arrow foodie-categories__arrow--left" @click="scrollLeft">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>

      <!-- Categories List -->
      <div ref="scrollContainer" class="foodie-categories__list">
        <!-- Loading State -->
        <template v-if="loading">
          <div v-for="n in 6" :key="n" class="foodie-categories__item foodie-categories__item--loading">
            <div class="foodie-categories__image skeleton"></div>
            <span class="foodie-categories__label skeleton"></span>
          </div>
        </template>

        <!-- Data State -->
        <template v-else>
          <div 
            v-for="category in categories"
            :key="category.id"
            class="foodie-categories__item"
            @click="handleSelect(category)"
          >
            <div class="foodie-categories__image">
              <img :src="category.image" :alt="category.name" />
            </div>
            <span class="foodie-categories__label">{{ category.name }}</span>
          </div>
        </template>
      </div>

      <!-- Right Arrow -->
      <button class="foodie-categories__arrow foodie-categories__arrow--right" @click="scrollRight">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>
    </div>
  </section>
</template>
