<template>
  <section class="mkp-section">
    <div class="container-mkp">
      <div class="section-header">
        <h2>Our Articles</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="article in articles" :key="article.id" class="group/art cursor-pointer">
          <div class="relative rounded-xl overflow-hidden mb-6 aspect-[4/3]">
            <img :src="article.image" :alt="article.title" class="w-full h-full object-cover group-hover/art:scale-110 transition-transform duration-700" />
            <div class="absolute inset-0 bg-black/20 group-hover/art:bg-black/40 transition-colors"></div>
            <div class="absolute bottom-4 left-4 flex items-center gap-2 text-white text-[10px] font-bold">
               <img src="https://secure.gravatar.com/avatar/64e1e0757a3e64f7df375e206f36b69a?s=96&d=mm&r=g" class="w-5 h-5 rounded-full" />
               <span>admin</span>
            </div>
            <div class="absolute bottom-4 right-4 flex items-center gap-3 text-white">
               <div class="flex items-center gap-1 text-[10px]"><i class="far fa-eye"></i> 14k</div>
               <div class="flex items-center gap-1 text-[10px]"><i class="far fa-comment"></i> 0</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">
             <span class="text-primary">{{ article.category }}</span>
             <span class="opacity-30">/</span>
             <span>{{ article.date }}</span>
          </div>
          <h3 class="text-lg font-bold text-slate-800 mb-4 group-hover/art:text-primary transition-colors leading-snug">{{ article.title }}</h3>
          <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">{{ article.excerpt }}</p>
          <button class="text-[10px] font-black uppercase tracking-widest text-primary border-b-2 border-primary/20 hover:border-primary transition-all pb-1">Continue Reading</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ARTICLES } from './../../configs'

const articles = ARTICLES
</script>
