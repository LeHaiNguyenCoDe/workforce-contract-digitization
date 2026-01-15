<script lang="ts">
import { defineComponent } from 'vue'
import { useI18n } from 'vue-i18n'

export default defineComponent({
  name: 'CocktailBanner',
  setup() {
    const { t } = useI18n()
    return { t }
  }
})
</script>

<template>
  <section class="foodie-cocktail-banner">
    <div class="foodie-container">
      <div class="foodie-cocktail-banner__inner">
        <!-- Text Content -->
        <div class="foodie-cocktail-banner__content text-center">
          <h2 class="foodie-cocktail-banner__title" v-html="t('foodie.cocktailBannerTitle')"></h2>
          <p class="foodie-cocktail-banner__desc">
            {{ t('foodie.cocktailBannerDesc') }}
          </p>
          <a href="#" class="foodie-cocktail-banner__link">
            {{ t('foodie.cocktailBannerLink') }}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </a>
        </div>
        
        <!-- Image -->
        <div class="foodie-cocktail-banner__image-wrapper">
          <img 
            src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=600&h=600&fit=crop" 
            alt="Ceramic Vases"
            class="foodie-cocktail-banner__image"
          />
        </div>
      </div>
    </div>
  </section>
</template>
