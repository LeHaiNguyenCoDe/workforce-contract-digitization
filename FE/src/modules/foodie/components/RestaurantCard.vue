<script setup lang="ts">
interface Restaurant {
  id: number
  name: string
  image: string
  bgColor?: string
}

defineProps<{
  restaurant: Restaurant
}>()
</script>

<template>
  <div class="foodie-restaurant-card">
    <img :src="restaurant.image" :alt="restaurant.name" />
    <div class="foodie-restaurant-card__overlay">
      <span class="foodie-restaurant-card__name">{{ restaurant.name }}</span>
    </div>
  </div>
</template>
