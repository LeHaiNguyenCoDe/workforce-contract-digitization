<template>
  <section class="mkp-section">
    <div class="container-mkp">
      <div class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-[#f0f4ff] to-[#fff0f5] p-12 md:p-20 group">
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-16">
          <!-- Text Content -->
          <div class="flex-1 text-center md:text-left">
            <h2 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 !text-slate-800">Apple Shopping Event</h2>
            <p class="text-lg md:text-xl text-slate-600 mb-10">Hurry and get discounts on all Apple devices up to 20%</p>
            
            <div class="flex justify-center md:justify-start mb-10">
              <CountdownTimer targetDate="2026-12-31T23:59:59" />
            </div>

            <div>
              <button class="btn-mkp btn-mkp-primary !px-12 !py-5 rounded-md flex items-center gap-3 font-bold">
                Go Shopping <i class="fas fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Image -->
          <div class="flex-1 relative">
            <img src="https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-apple-shopping-event.png" alt="Apple Event" 
                 class="w-full object-contain group-hover:scale-105 transition-transform duration-700" />
          </div>
        </div>

        <!-- Quick Product Thumbnails (Bottom) -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-5 gap-4">
           <div v-for="p in smallProducts" :key="p.name" class="bg-white/60 backdrop-blur-sm rounded-xl p-4 flex items-center gap-4 hover:bg-white transition-all cursor-pointer">
              <img :src="p.image" :alt="p.name" class="w-12 h-12 object-contain" />
              <div>
                <div class="text-[10px] font-bold text-slate-800 truncate">{{ p.name }}</div>
                <div class="text-[10px] text-primary font-bold">{{ formatPrice(p.price) }}</div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import CountdownTimer from '../../components/common/CountdownTimer.vue'
import { EVENT_SMALL_PRODUCTS } from './../../configs'
import { formatPrice } from '../../helpers/format'

const smallProducts = EVENT_SMALL_PRODUCTS
</script>
