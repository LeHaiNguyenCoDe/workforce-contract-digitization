<template>
  <div v-if="product" class="product-detail-page marketplace-wrapper pt-10 pb-20">
    <div class="container-mkp">
      <!-- Breadcrumbs -->
      <nav class="flex mb-8 text-[11px] uppercase tracking-widest font-bold text-slate-400">
        <router-link to="/marketplace" class="hover:text-primary">Home</router-link>
        <span class="mx-2">/</span>
        <router-link to="/marketplace/shop" class="hover:text-primary">Shop</router-link>
        <span class="mx-2">/</span>
        <span class="text-slate-800">{{ product.category }}</span>
      </nav>

      <div class="flex flex-col lg:flex-row gap-16 mb-20">
        <!-- Gallery -->
        <div class="w-full lg:w-1/2">
          <div class="bg-slate-50 rounded-3xl p-10 aspect-square flex items-center justify-center mb-6 overflow-hidden relative group">
            <img :src="product.image" :alt="product.name" class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
            <div class="absolute top-6 right-6 flex flex-col gap-3">
               <button class="w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center text-slate-400 hover:text-primary transition-all">
                 <i class="far fa-heart"></i>
               </button>
               <button class="w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center text-slate-400 hover:text-primary transition-all">
                 <i class="fas fa-expand"></i>
               </button>
            </div>
          </div>
          <div class="grid grid-cols-4 gap-4">
             <div v-for="i in 4" :key="i" class="aspect-square bg-slate-50 rounded-xl p-3 border-2 transition-all cursor-pointer"
                  :class="i === 1 ? 'border-primary' : 'border-transparent hover:border-slate-200'">
                <img :src="product.image" class="w-full h-full object-contain" />
             </div>
          </div>
        </div>

        <!-- Content -->
        <div class="w-full lg:w-1/2 flex flex-col pt-4">
          <div class="flex items-center gap-4 mb-4">
            <div v-if="product.isHot" class="px-3 py-1 bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded">Hot</div>
            <div v-if="product.isNew" class="px-3 py-1 bg-blue-500 text-white text-[10px] font-black uppercase tracking-widest rounded">New</div>
            <span class="text-[12px] text-slate-400 font-bold uppercase tracking-widest">SKU: {{ product.sku }}</span>
          </div>

          <h1 class="text-4xl font-black text-slate-900 mb-6 leading-tight">{{ product.name }}</h1>

          <div class="flex items-center gap-4 mb-8">
            <div class="flex text-amber-400 gap-1">
              <i v-for="i in 5" :key="i" class="fas fa-star" :class="i <= product.rating ? '' : 'text-slate-200'"></i>
            </div>
            <span class="text-sm text-slate-400 font-medium">(2 customer reviews)</span>
          </div>

          <div class="flex items-center gap-4 mb-10">
            <span class="text-3xl font-black text-primary">{{ formatPrice(product.price) }}</span>
            <span v-if="product.originalPrice" class="text-xl text-slate-300 line-through">{{ formatPrice(product.originalPrice) }}</span>
          </div>

          <p class="text-slate-500 leading-relaxed mb-10 text-[15px]">
            Parturient elit et adipiscing eu elit ac ante arcu parturient scelerisque non elementum habitant 
            parturient phasellus nisl litora a pretium vestibulum scelerisque id vestibulum non eget a a. 
          </p>

          <div class="flex items-center gap-6 mb-12">
            <div class="flex items-center border-2 border-slate-100 rounded-full h-14 px-4 bg-white">
               <button @click="quantity > 1 && quantity--" class="w-10 text-slate-400 hover:text-primary transition-colors">
                 <i class="fas fa-minus text-xs"></i>
               </button>
               <input type="number" v-model.number="quantity" class="w-12 text-center text-lg font-black border-none focus:ring-0 px-0" />
               <button @click="quantity++" class="w-10 text-slate-400 hover:text-primary transition-colors">
                 <i class="fas fa-plus text-xs"></i>
               </button>
            </div>
            <button 
              @click="cartStore.addToCart(product, quantity)"
              class="flex-grow btn-mkp btn-mkp-primary h-14 rounded-full text-lg shadow-xl shadow-primary/20"
            >
              Add to cart
            </button>
          </div>

          <div class="border-t border-slate-100 pt-8 mt-auto space-y-4">
             <div class="flex items-center gap-2">
               <span class="text-[11px] text-slate-400 font-black uppercase tracking-widest w-24">Categories:</span>
               <span class="text-[13px] text-slate-800 font-bold hover:text-primary cursor-pointer transition-colors">{{ product.category }}</span>
             </div>
             <div class="flex items-center gap-2">
               <span class="text-[11px] text-slate-400 font-black uppercase tracking-widest w-24">Share:</span>
               <div class="flex items-center gap-4 text-slate-400">
                  <i class="fab fa-facebook-f hover:text-primary cursor-pointer transition-colors"></i>
                  <i class="fab fa-twitter hover:text-primary cursor-pointer transition-colors"></i>
                  <i class="fab fa-pinterest-p hover:text-primary cursor-pointer transition-colors"></i>
                  <i class="fab fa-linkedin-in hover:text-primary cursor-pointer transition-colors"></i>
               </div>
             </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs-container mb-20">
        <div class="flex justify-center border-b border-slate-100 mb-10 gap-12">
          <button v-for="tab in ['Description', 'Additional Information', 'Reviews (2)']" :key="tab"
                  class="pb-5 text-[15px] font-black uppercase tracking-widest transition-all relative"
                  :class="activeTab === tab ? 'text-slate-900' : 'text-slate-300 hover:text-slate-600'"
                  @click="activeTab = tab">
            {{ tab }}
            <div v-if="activeTab === tab" class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
          </button>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <div v-if="activeTab === 'Description'" class="prose prose-slate max-w-none prose-sm leading-relaxed text-slate-500">
             <p>Vestibulum parturient pretium scelerisque sem a purus montes a pretium amet sem a purus montes a pretium amet scelerisque non elementum habitant parturient phasellus nisl litora a pretium vestibulum scelerisque id vestibulum non eget a a.</p>
             <p>Scelerisque id vestibulum non eget a a. Parturient elit et adipiscing eu elit ac ante arcu parturient scelerisque non elementum habitant parturient phasellus nisl litora a pretium vestibulum scelerisque id vestibulum non eget a a.</p>
          </div>
          <div v-else class="text-center py-10 text-slate-400">Content for {{ activeTab }} coming soon...</div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="py-40 text-center">
    <div class="animate-spin text-primary text-4xl mb-4"><i class="fas fa-spinner"></i></div>
    <span class="text-slate-400 font-bold uppercase tracking-widest">Loading Product...</span>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProductStore } from '../stores/products'
import { useCartStore } from '../stores/cart'
import { formatPrice } from '../helpers/format'
import type { Product } from '../types'

const route = useRoute()
const productStore = useProductStore()
const cartStore = useCartStore()

const product = computed(() => productStore.currentProduct)
const quantity = ref(1)
const activeTab = ref('Description')

onMounted(async () => {
  const id = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (id) {
    await productStore.fetchProduct(id)
  }
})
</script>
