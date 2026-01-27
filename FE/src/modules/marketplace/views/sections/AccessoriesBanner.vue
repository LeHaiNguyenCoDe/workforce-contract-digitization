<template>
  <section class="mkp-section">
    <div class="container-mkp">
      <div class="bg-[#fafafa] rounded-2xl overflow-hidden grid grid-cols-12">
        <!-- Text & Filters (Left) -->
        <div class="col-span-12 lg:col-span-6 p-12 md:p-20 flex flex-col justify-center items-center text-center">
          <h2 class="text-4xl md:text-5xl font-black text-slate-800 mb-6 !text-slate-800">Microsoft Accessories</h2>
          <p class="text-slate-500 mb-10 max-w-md">Personalize your Surface Pro with Microsoft branded accessories. In the presence of many colors for every taste.</p>
          
          <div class="flex flex-wrap justify-center gap-4">
            <button v-for="tag in tags" :key="tag.label" class="px-5 py-2 rounded-full border border-slate-200 text-xs font-bold uppercase tracking-widest text-slate-600 hover:border-primary hover:text-primary transition-all flex items-center gap-2 bg-white">
              <i :class="tag.icon" class="text-[10px] opacity-60"></i>
              {{ tag.label }}
            </button>
          </div>
        </div>

        <!-- Featured Product (Right) -->
        <div class="col-span-12 lg:col-span-6 bg-white flex items-center justify-center p-12 relative group">
          <img src="https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-microsoft-accessories.png" alt="Microsoft" 
               class="w-[80%] object-contain group-hover:scale-105 transition-transform duration-700" />
          
          <div class="absolute bottom-12 right-12 z-20">
             <ProductCard :product="featuredProduct" class="!shadow-2xl scale-90 md:scale-100" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import CountdownTimer from '../../components/common/CountdownTimer.vue'
import ProductCard from '../../components/common/ProductCard.vue'
import { ACCESSORIES_TAGS, ACCESSORIES_FEATURED_PRODUCT } from './../../configs'

const tags = ACCESSORIES_TAGS
const featuredProduct = ACCESSORIES_FEATURED_PRODUCT
</script>
