<script setup lang="ts">
import type { Product } from '../types'

// Props
interface Props {
  product: Product
}

defineProps<Props>()

// Emit events
const emit = defineEmits<{
  (e: 'add-to-cart', product: Product): void
  (e: 'toggle-favorite', product: Product): void
}>()
</script>

<template>
  <div class="product-card">
    <!-- Image Section -->
    <div class="product-card__image">
      <img :src="product.image" :alt="product.name" />
      <button class="product-card__favorite" @click.stop="emit('toggle-favorite', product)">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </button>
    </div>
    
    <!-- Info Section -->
    <div class="product-card__info">
      <h3 class="product-card__name">{{ product.name }}</h3>
      <p v-if="product.brand" class="product-card__brand">{{ product.brand }}</p>
      
      <div class="product-card__footer">
        <span class="product-card__price">${{ product.price.toFixed(2) }}</span>
        <button class="product-card__add-btn" @click.stop="emit('add-to-cart', product)">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
$yellow: #FFC107;
$yellow-dark: #FFB300;
$dark: #2D2D2D;
$gray: #666;
$gray-light: #999;
$bg-light: #F5F5F5;
$white: #FFFFFF;
$radius: 12px;
$shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
$transition: all 0.25s ease;

.product-card {
  background: $white;
  border-radius: $radius;
  box-shadow: $shadow;
  overflow: hidden;
  transition: $transition;
  cursor: pointer;
  
  &:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
  }
  
  &__image {
    position: relative;
    background: #f5f5f5;
    height: 200px;
    overflow: hidden;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      transition: $transition;
    }
  }
  
  &:hover &__image img {
    transform: scale(1.08);
  }
  
  &__favorite {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    background: $white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: $transition;
    z-index: 2;
    
    &:hover {
      background: #FFF8E1;
      transform: scale(1.1);
    }
    
    svg {
      color: $gray;
    }
    
    &:hover svg {
      color: #E91E63;
    }
  }
  
  &__info {
    padding: 16px;
  }
  
  &__name {
    font-size: 15px;
    font-weight: 600;
    color: $dark;
    margin: 0 0 4px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  &__brand {
    font-size: 13px;
    color: $gray-light;
    margin: 0 0 12px 0;
  }
  
  &__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  &__price {
    font-size: 18px;
    font-weight: 700;
    color: $yellow-dark;
  }
  
  &__add-btn {
    width: 40px;
    height: 40px;
    background: $yellow;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: $transition;
    
    &:hover {
      background: $yellow-dark;
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba($yellow, 0.4);
    }
    
    svg {
      color: $dark;
    }
  }
}
</style>
