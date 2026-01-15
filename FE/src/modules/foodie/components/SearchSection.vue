<script lang="ts">
import { defineComponent, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFoodieSearch } from '../composables/useFoodieSearch'

export default defineComponent({
  name: 'SearchSection',
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
  <section class="foodie-search-section">
    <div class="foodie-container">
      <div class="foodie-search-section__content">
        <h2 class="foodie-search-section__title">{{ t('foodie.searchSectionTitle') }}</h2>
        <p class="foodie-search-section__subtitle">{{ t('foodie.searchSectionDesc') }}</p>
        
        <div class="foodie-search-section__input" @click="handleSearch">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
          <input 
            v-model="searchQuery"
            type="text" 
            :placeholder="t('foodie.searchSectionDesc')"
            @focus="handleSearch"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
$foodie-yellow: #FFC107;
$foodie-yellow-dark: #FFB300;
$foodie-dark: #2D2D2D;
$foodie-gray-light: #999999;
$foodie-border: #E5E5E5;
$foodie-radius-xl: 24px;
$foodie-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
$foodie-transition: all 0.3s ease;

.foodie-search-section {
  padding: 60px 0;
  background: #fff;
  text-align: center;
  
  &__content {
    max-width: 600px;
    margin: 0 auto;
  }
  
  &__title {
    font-size: 32px;
    font-weight: 700;
    color: $foodie-yellow-dark;
    font-style: italic;
    margin-bottom: 8px;
  }
  
  &__subtitle {
    font-size: 16px;
    color: $foodie-dark;
    margin-bottom: 24px;
  }
  
  &__input {
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid $foodie-border;
    border-radius: $foodie-radius-xl;
    padding: 12px 24px;
    box-shadow: $foodie-shadow-sm;
    cursor: pointer;
    transition: $foodie-transition;
    
    &:hover {
      border-color: $foodie-yellow;
    }
    
    svg {
      flex-shrink: 0;
      color: $foodie-gray-light;
      margin-right: 12px;
    }
    
    input {
      flex: 1;
      border: none;
      outline: none;
      font-size: 15px;
      color: $foodie-dark;
      background: transparent;
      
      &::placeholder {
        color: $foodie-gray-light;
      }
    }
  }
}
</style>
