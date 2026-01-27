<template>
  <section class="mkp-section !pt-0">
    <div class="container-mkp">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="card in cards" :key="card.title" 
             class="relative rounded-2xl overflow-hidden p-8 h-[180px] group cursor-pointer"
             :style="{ backgroundColor: card.bg }">
          <div class="relative z-10">
            <h4 class="text-xl font-black text-slate-800 mb-1">{{ card.title }}</h4>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-6">{{ card.subtitle }}</p>
            <button class="text-xs font-bold underline decoration-primary underline-offset-4 decoration-2">View Details</button>
          </div>
          <img :src="card.image" :alt="card.title" 
               class="absolute bottom-4 right-4 w-[50%] object-contain group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const cards = [
  { 
    title: 'Xiaomi Mi 11', 
    subtitle: 'Discount up to 30%', 
    bg: '#eef2ff', 
    image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-promo-banner-mobile.png' 
  },
  { 
    title: 'HP Laser Jet', 
    subtitle: 'Personal printer', 
    bg: '#fff5f5', 
    image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-promo-banner-printer.png' 
  },
  { 
    title: 'White Joy Cons', 
    subtitle: 'Long-awaited novelty', 
    bg: '#fff8eb', 
    image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-promo-banner-joycons.png' 
  }
]
</script>
