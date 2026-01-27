<script setup lang="ts">
import { RouterView } from 'vue-router'
import LandingHeader from '@/components/layouts/LandingHeader.vue'
import LandingFooter from '@/components/layouts/LandingFooter.vue'
import ChatBubble from '@/modules/landing/chat/components/ChatBubble.vue'
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <LandingHeader />
        <main class="flex-1 pb-8">
            <RouterView v-slot="{ Component }">
                <transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4"
                    mode="out-in">
                    <component :is="Component" />
                </transition>
            </RouterView>
        </main>
        <LandingFooter />
        
        <!-- Guest Chat Bubble -->
        <ChatBubble />
    </div>
</template>

