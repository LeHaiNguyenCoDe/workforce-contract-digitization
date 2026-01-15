<script lang="ts">
import { defineComponent, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFoodieSearch } from '../composables/useFoodieSearch'

export default defineComponent({
  name: 'HeroSection',
  setup() {
    const { t } = useI18n()
    const { openSearch } = useFoodieSearch()
    const searchQuery = ref('')

    const handleSearch = () => {
      openSearch()
    }

    return {
      t,
      searchQuery,
      handleSearch
    }
  }
})
</script>

<template>
  <section class="foodie-hero">
    <div class="foodie-hero__container">
      <!-- Left: Content -->
      <div class="foodie-hero__content">
        <h1 class="foodie-hero__title text-center" v-html="t('foodie.heroTitle')"></h1>
        
        <div class="foodie-hero__search" @click="handleSearch">
          <input 
            v-model="searchQuery"
            type="text" 
            :placeholder="t('foodie.enterAddress')"
            @focus="handleSearch"
          />
          <button>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="m21 21-4.35-4.35"/>
            </svg>
          </button>
        </div>
        
        <p class="foodie-hero__note" v-html="t('foodie.loginToSeeAddresses')"></p>
      </div>
      
      <!-- Right: Image -->
      <div class="foodie-hero__image">
        <img src="/images/foodie/Delivery Man Group.png" alt="Pottery Workshop" />
      </div>
    </div>
    
    <!-- Wave decoration -->
    <div class="foodie-hero__wave">
      <svg viewBox="0 0 1200 100" preserveAspectRatio="none">
        <path d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1100,80 1150,60 1200,50 L1200,100 L0,100 Z" fill="#ffffff"/>
      </svg>
    </div>
  </section>
</template>
