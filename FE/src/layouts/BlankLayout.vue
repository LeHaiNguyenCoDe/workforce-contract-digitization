<template>
  <div class="blank-layout">
    <RouterView />
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<style scoped>
.blank-layout {
  width: 100%;
  min-height: 100vh;
}
</style>
