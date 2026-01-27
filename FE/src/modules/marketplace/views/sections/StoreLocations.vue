<template>
  <section class="mkp-section border-t border-slate-50">
    <div class="container-mkp">
      <!-- SEO / Info Text -->
      <div class="max-w-6xl mb-16">
        <h2 class="text-2xl font-black text-slate-800 mb-6 !text-slate-800">Online store of household appliances and electronics</h2>
        <div class="space-y-4 text-xs text-slate-400 leading-relaxed italic">
          <p>Then the question arises: where's the content? Not there yet? That's not so bad, there's dummy copy to the rescue. But worse, what if the fish doesn't fit in the can, the foot's too big for the boot? Or to small? To short sentences, to many headings, images too large for the proposed design, or too small, or they fit in but it looks iffy for reasons.</p>
          <p>A client that's unhappy for a reason is a problem, a client that's unhappy though he or her can't quite put a finger on it is worse. Chances are there wasn't collaboration, communication, and</p>
        </div>
        <button class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-800 border bg-white px-4 py-2 mt-6 rounded hover:bg-slate-50 transition-colors">
          Read More <i class="fas fa-chevron-down opacity-40"></i>
        </button>
      </div>

      <!-- Store Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div v-for="store in stores" :key="store.name" class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-50 transition-all group cursor-pointer">
           <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-primary text-sm group-hover:bg-primary group-hover:text-white transition-all">
              <i class="fas fa-map-marker-alt"></i>
           </div>
           <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black text-slate-800">{{ store.name }}</span>
                <i class="fas fa-arrow-circle-right text-primary text-xs"></i>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ store.address }}</p>
           </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { STORES } from './../../configs'

const stores = STORES
</script>
