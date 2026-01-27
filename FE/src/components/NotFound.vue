<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <div class="min-h-[60vh] flex items-center justify-center py-12">
    <div class="text-center">
      <!-- 404 Illustration -->
      <div class="relative mb-8">
        <span class="text-[150px] md:text-[200px] font-bold gradient-text opacity-20">404</span>
        <div class="absolute inset-0 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-primary" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10" />
            <path d="M16 16s-1.5-2-4-2-4 2-4 2" />
            <line x1="9" x2="9.01" y1="9" y2="9" />
            <line x1="15" x2="15.01" y1="9" y2="9" />
          </svg>
        </div>
      </div>

      <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ t('common.pageNotFound') }}</h1>
      <p class="text-slate-400 mb-8 max-w-md mx-auto">
        Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
      </p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink to="/" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          </svg>
          {{ t('nav.home') }}
        </RouterLink>
        <button @click="$router.back()" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
          </svg>
          Quay lại
        </button>
      </div>
    </div>
  </div>
</template>
