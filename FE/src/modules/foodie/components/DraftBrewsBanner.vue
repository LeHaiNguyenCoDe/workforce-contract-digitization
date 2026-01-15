<script lang="ts">
import { defineComponent } from 'vue'
import { useI18n } from 'vue-i18n'

export default defineComponent({
  name: 'DraftBrewsBanner',
  setup() {
    const { t } = useI18n()
    return { t }
  }
})
</script>

<template>
  <section class="foodie-brews-banner">
    <div class="foodie-container">
      <div class="foodie-brews-banner__inner">
        <!-- Large Image Left -->
        <div class="foodie-brews-banner__image-wrapper">
          <img 
            src="https://gw.alicdn.com/imgextra/i3/3028640392/O1CN01L8jxZz1ElaDKtfFKu_!!0-item_pic.jpg_400x400.jpg_.webp" 
            alt="Kiln Fired Sets"
            class="foodie-brews-banner__image"
          />
        </div>
        
        <!-- Text Content Right -->
        <div class="foodie-brews-banner__content text-center">
          <h2 class="foodie-brews-banner__title">{{ t('foodie.brewsBannerTitle') }}</h2>
          <p class="foodie-brews-banner__desc">
            {{ t('foodie.brewsBannerDesc') }}
          </p>
          <a href="#" class="foodie-brews-banner__link">
            {{ t('foodie.brewsBannerLink') }}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
