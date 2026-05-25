<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from '@/composables/useI18n'

const { locale, setLocale } = useI18n()
const isOpen = ref(false)

const languages = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'ar', name: 'العربية', flag: '🇸🇦' },
]

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
}

const closeDropdown = () => {
    isOpen.value = false
}

const handleClickOutside = (e) => {
    if (!e.target.closest('.language-switcher')) {
        closeDropdown()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div class="relative language-switcher">
        <button
            type="button"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                   bg-primary-600 text-white hover:bg-primary-700
                   border border-primary-500 hover:border-primary-400
                   transition-all duration-200 shadow-lg shadow-primary-500/30"
            :aria-label="`Switch language, current: ${languages.find(l => l.code === locale)?.name}`"
            :aria-expanded="isOpen"
            aria-haspopup="true"
            @click="toggleDropdown"
        >
            <span class="text-lg" aria-hidden="true">{{ languages.find(l => l.code === locale)?.flag }}</span>
            <span class="hidden sm:inline">{{ languages.find(l => l.code === locale)?.name }}</span>
            <svg
                class="w-4 h-4 text-primary-100 transition-all duration-200"
                :class="isOpen ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-48 bg-white border border-primary-200 rounded-2xl shadow-2xl shadow-primary-500/20 py-2 z-50"
                role="menu"
                :aria-label="`Select language`"
            >
                <button
                    v-for="lang in languages"
                    :key="lang.code"
                    type="button"
                    class="w-full flex items-center gap-3 px-4 py-3 text-left
                           hover:bg-primary-50 transition-colors duration-150
                           text-sm font-semibold"
                    :class="locale === lang.code ? 'text-primary-700 bg-primary-500/10' : 'text-neutral-700'"
                    role="menuitem"
                    @click="setLocale(lang.code); closeDropdown()"
                >
                    <span class="text-xl" aria-hidden="true">{{ lang.flag }}</span>
                    <span>{{ lang.name }}</span>
                    <span v-if="locale === lang.code" class="ml-auto">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
</style>
