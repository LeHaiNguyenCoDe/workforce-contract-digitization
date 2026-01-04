<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale, getLocale } from '@/plugins/i18n'
import { languageService, type SupportedLocale } from '@/shared/services/languageService'
import { useAuthStore } from '@/stores'

const { locale } = useI18n()
const authStore = useAuthStore()

const isOpen = ref(false)
const languages = ref<SupportedLocale[]>([
    { code: 'vi', name: 'Tiếng Việt', native: 'Tiếng Việt', flag: '🇻🇳', is_current: true },
    { code: 'en', name: 'English', native: 'English', flag: '🇬🇧', is_current: false }
])

const currentLanguage = computed(() => {
    return languages.value.find(lang => lang.code === locale.value) || languages.value[0]
})

const selectLanguage = async (code: string) => {
    await setLocale(code as any)
    isOpen.value = false

    // Sync with BE if authenticated
    if (authStore.isAuthenticated) {
        try {
            await languageService.setLocale(code)
        } catch (error) {
            console.error('Failed to sync locale with BE:', error)
        }
    }
}

const fetchLanguages = async () => {
    try {
        const response = await languageService.getSupportedLocales()
        if (response.locales && response.locales.length > 0) {
            languages.value = response.locales
        }
    } catch (error) {
        console.error('Failed to fetch supported languages:', error)
    }
}

const closeDropdown = (e: MouseEvent) => {
    const target = e.target as HTMLElement
    if (!target.closest('.language-switcher')) {
        isOpen.value = false
    }
}

onMounted(async () => {
    document.addEventListener('click', closeDropdown)
    const saved = getLocale()
    if (saved) await setLocale(saved)

    await fetchLanguages()
})

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown)
})
</script>

<template>
    <div class="language-switcher relative">
        <button @click="isOpen = !isOpen"
            class="flex items-center gap-2 px-3 py-2 text-sm bg-[#9F7A5F] border border-white/10 rounded-lg hover:text-white transition-all">
            <span>{{ currentLanguage.flag }}</span>
            <span class="hidden sm:inline">{{ currentLanguage.name }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" class="transition-transform" :class="{ 'rotate-180': isOpen }">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <transition enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 -translate-y-2">
            <div v-if="isOpen"
                class="absolute top-full right-0 mt-2 min-w-[160px] bg-[#9F7A5F] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden">
                <button v-for="lang in languages" :key="lang.code" @click="selectLanguage(lang.code)"
                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left transition-colors"
                    :class="locale === lang.code ? 'bg-primary/10 text-black' : 'text-white hover:bg-white/5 hover:text-white'">
                    <span class="text-lg">{{ lang.flag }}</span>
                    <span>{{ lang.name }}</span>
                    <svg v-if="locale === lang.code" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ml-auto">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </button>
            </div>
        </transition>
    </div>
</template>
