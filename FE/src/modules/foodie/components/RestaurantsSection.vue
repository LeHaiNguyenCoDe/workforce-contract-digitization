<script setup lang="ts">
import type { Restaurant, SectionConfig } from '../types'

// Props
interface Props {
  restaurants: Restaurant[]
  config?: SectionConfig
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  config: () => ({
    title: 'Restaurants',
    seeAllLink: '#',
    showArrows: true
  })
})

const emit = defineEmits<{
  (e: 'select', restaurant: Restaurant): void
  (e: 'see-all'): void
}>()

const handleSelect = (restaurant: Restaurant) => {
  emit('select', restaurant)
}
</script>

<template>
  <section class="foodie-restaurants-section">
    <div class="foodie-container">
      <!-- Section Header -->
      <div class="foodie-products-section__header">
        <h2 class="foodie-products-section__title">{{ config.title }}</h2>
        <div class="foodie-products-section__actions">
          <a 
            v-if="config.seeAllLink"
            :href="config.seeAllLink" 
            class="foodie-products-section__see-all"
            @click.prevent="emit('see-all')"
          >
            See All
          </a>
          <div v-if="config.showArrows" class="foodie-products-section__arrows">
            <button class="foodie-products-section__arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
              </svg>
            </button>
            <button class="foodie-products-section__arrow foodie-products-section__arrow--active">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Restaurants Grid -->
      <div class="foodie-restaurants-section__grid">
        <!-- Loading State -->
        <template v-if="loading">
          <div v-for="n in 3" :key="n" class="foodie-restaurant-item foodie-restaurant-item--loading">
            <div class="skeleton"></div>
          </div>
        </template>

        <!-- Data State -->
        <template v-else>
          <div 
            v-for="restaurant in restaurants"
            :key="restaurant.id"
            class="foodie-restaurant-item"
            @click="handleSelect(restaurant)"
          >
            <img :src="restaurant.image" :alt="restaurant.name" />
            <div class="foodie-restaurant-item__overlay">
              <span class="foodie-restaurant-item__name">{{ restaurant.name }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </section>
</template>
