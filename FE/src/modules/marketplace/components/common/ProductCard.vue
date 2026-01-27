<template>
  <div class="product-card-mkp group/card">
    <!-- Badges -->
    <div v-if="product.isHot" class="badge-mkp badge-mkp-hot">Hot</div>
    <div v-if="product.isNew" class="badge-mkp badge-mkp-new">New</div>

    <!-- Product Actions (Floating Side Bar) -->
    <div class="product-actions-v">
      <div class="action-btn" title="Compare"><i class="fas fa-random"></i></div>
      <div class="action-btn" title="Quick View"><i class="fas fa-search"></i></div>
      <div class="action-btn" title="Add to Wishlist"><i class="far fa-heart"></i></div>
    </div>

    <!-- Image -->
    <router-link :to="'/marketplace/product/' + product.id" class="image-wrapper">
      <img :src="product.image" :alt="product.name" />
    </router-link>

    <!-- Info -->
    <div class="product-info">
      <div class="category">{{ product.category }}</div>
      <router-link :to="'/marketplace/product/' + product.id">
        <h3 class="title">{{ product.name }}</h3>
      </router-link>
      
      <div class="rating">
        <i v-for="i in 5" :key="i" class="fas fa-star" 
           :class="i <= Math.floor(product.rating) ? 'text-amber-400' : 'text-slate-200'"></i>
      </div>

      <div class="status" :class="product.inStock ? 'in-stock' : 'out-of-stock'">
        <i class="fas" :class="product.inStock ? 'fa-check' : 'fa-times-circle'"></i>
        {{ product.inStock ? 'In stock' : 'Out of stock' }}
      </div>

      <div class="price-row">
        <span class="price">{{ formatPrice(product.price) }}</span>
        <span v-if="product.originalPrice" class="old-price">{{ formatPrice(product.originalPrice) }}</span>
      </div>

      <div class="mt-auto">
        <button 
          v-if="product.inStock"
          class="w-full btn-mkp btn-mkp-primary group-hover/card:bg-primary-hover transition-colors"
          @click="cartStore.addToCart(product)"
        >
          Add To Cart
        </button>
        <button 
          v-else
          class="w-full btn-mkp btn-mkp-outline opacity-50 cursor-not-allowed"
          disabled
        >
          Out of stock
        </button>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-50 relative">
        <div class="flex items-center justify-between text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-3">
          <span>SKU: {{ product.sku }}</span>
        </div>
        
        <!-- Expandable Details (WoodMart Style) -->
        <div class="expandable-info opacity-0 group-hover/card:opacity-100 transition-opacity space-y-3 pt-4 border-t border-slate-50">
          <div class="detail-group flex flex-col gap-0.5">
            <span class="text-slate-400 text-[10px] uppercase tracking-[1.5px] font-bold">Brand</span>
            <span class="text-slate-800 text-[13px] font-bold">{{ product.brand || 'Phillips' }}</span>
          </div>
          <div class="detail-group flex flex-col gap-0.5">
            <span class="text-slate-400 text-[10px] uppercase tracking-[1.5px] font-bold">Color</span>
            <span class="text-slate-800 text-[13px] font-bold">{{ product.specs?.color || 'White' }}</span>
          </div>
          <div class="detail-group flex flex-col gap-0.5">
            <span class="text-slate-400 text-[10px] uppercase tracking-[1.5px] font-bold">Size</span>
            <span class="text-slate-800 text-[13px] font-bold">{{ product.specs?.size || '360x208x425 mm' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  name: 'ProductCard'
}
</script>

<script setup lang="ts">
import type { Product } from '../../types'
import { formatPrice } from '../../helpers/format'
import { useCartStore } from '../../stores/cart'

defineProps<{
  product: Product
}>()

const cartStore = useCartStore()
</script>
