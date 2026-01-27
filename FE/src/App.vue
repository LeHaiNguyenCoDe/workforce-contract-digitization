<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { BApp } from 'bootstrap-vue-next'

const authStore = useAuthStore()

onMounted(async () => {
  await authStore.fetchUser()
})
</script>

<template>
  <BApp>
    <RouterView />
  </BApp>
</template>

<style>
/* Global styles are in assets/scss/main.scss */
</style>
