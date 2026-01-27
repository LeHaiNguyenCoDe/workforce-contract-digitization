<template>
  <section class="mkp-section !pt-0">
    <div class="container-mkp">
      <div class="section-header">
        <h2>Popular Categories</h2>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-6">
        <div v-for="cat in categories" :key="cat.name" 
             class="flex flex-col items-center group cursor-pointer">
          <div class="w-full aspect-square bg-slate-50 rounded-xl flex items-center justify-center p-6 mb-4 group-hover:bg-white group-hover:shadow-lg transition-all border border-transparent group-hover:border-slate-100">
            <img :src="cat.image" :alt="cat.name" class="w-full h-full object-contain group-hover:scale-110 transition-transform" />
          </div>
          <span class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors text-center">{{ cat.name }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import { POPULAR_CATEGORIES } from './../../configs'

const categories = POPULAR_CATEGORIES
</script>
