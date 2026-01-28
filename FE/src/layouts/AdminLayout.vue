<script setup lang="ts">
import { RouterView } from 'vue-router'
import MainLayout from '../layouts/admin-panel/main.vue'
import CallWindow from '../modules/admin/chat/components/CallWindow.vue'
import { useAutoConnect } from '../modules/admin/chat/composables/useRealtime'

// Initialize real-time connection for notifications across all admin pages
useAutoConnect()
</script>

<template>
    <div class="admin-wrapper">
        <MainLayout>
            <router-view v-slot="{ Component }">
                <transition name="page-fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </MainLayout>
        <CallWindow />
    </div>
</template>

<style scoped>
.admin-wrapper {
    /* Temporary debug border - REMOVE LATER */
    border: 2px solid red;
    min-height: 100vh;
}
</style>
