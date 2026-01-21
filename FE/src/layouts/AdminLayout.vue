<script setup lang="ts">
import { RouterView } from 'vue-router'
import MainLayout from '@/layouts/admin-panel/main.vue'
import { useAutoConnect } from '@/modules/admin/chat/composables/useRealtime'

// Initialize real-time connection for notifications across all admin pages
useAutoConnect()
</script>

<template>
    <MainLayout>
        <RouterView />
    </MainLayout>
</template>

<style scoped>
/* Any custom styles for AdminLayout if needed */
</style>
