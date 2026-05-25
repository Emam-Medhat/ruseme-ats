<script setup>
import AnalysisLoadingOverlay from '@/Components/CvGenius/AnalysisLoadingOverlay.vue';
import { useAnalysisLoaderStore } from '@/stores/analysisLoader';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';

const analysisLoader = useAnalysisLoaderStore();
const { visible: analysisVisible, progress: analysisProgress } = storeToRefs(analysisLoader);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);

const creditsClass = computed(() => {
    const c = user.value?.credits ?? 0;
    if (c > 5) return 'text-success-600 dark:text-success-400';
    if (c >= 2) return 'text-warning-600 dark:text-warning-400';
    return 'text-danger-600 dark:text-danger-400';
});

const initials = computed(() => {
    const name = user.value?.name || '';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const navItems = computed(() => [
    {
        name: 'Dashboard',
        href: route('dashboard'),
        icon: 'home',
        active: route().current('dashboard'),
    },
    {
        name: 'Upload',
        href: route('upload'),
        icon: 'upload',
        active: route().current('upload'),
    },
    {
        name: 'History',
        href: route('history'),
        icon: 'history',
        active: route().current('history'),
    },
    {
        name: 'Pricing',
        href: route('pricing'),
        icon: 'pricing',
        active: route().current('pricing'),
    },
    {
        name: 'How It Works',
        href: route('how-it-works'),
        icon: 'info',
        active: route().current('how-it-works'),
    },
]);
</script>

<template>
    <AnalysisLoadingOverlay
        :visible="analysisVisible"
        :progress="analysisProgress"
        :steps="analysisLoader.steps"
        :step-state="analysisLoader.stepState"
    />

    <div class="flex min-h-screen bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100 font-sans">
        <!-- Mobile Overlay -->
        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm z-40 lg:hidden"
            @click="mobileMenuOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 transition-all duration-300 lg:static',
                sidebarOpen ? 'w-64' : 'w-20',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between px-5 h-16 border-b border-neutral-200 dark:border-neutral-800">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 shadow-md shadow-primary-500/20 font-black text-white text-lg">
                        ✦
                    </div>
                    <span
                        v-if="sidebarOpen"
                        class="text-lg font-extrabold tracking-tight text-neutral-900 dark:text-white"
                    >
                        {{ page.props.cvGenius?.name || 'CV Genius AI' }}
                    </span>
                </Link>
                <button
                    v-if="sidebarOpen"
                    @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:flex p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                >
                    <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <template v-for="item in navItems" :key="item.href">
                    <Link
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group',
                            item.active
                                ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                                : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-neutral-100',
                        ]"
                    >
                        <!-- Home Icon -->
                        <svg v-if="item.icon === 'home'" class="w-5 h-5 flex-shrink-0" :class="[item.active ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-600 dark:group-hover:text-neutral-300']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <!-- Upload Icon -->
                        <svg v-else-if="item.icon === 'upload'" class="w-5 h-5 flex-shrink-0" :class="[item.active ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-600 dark:group-hover:text-neutral-300']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <!-- History Icon -->
                        <svg v-else-if="item.icon === 'history'" class="w-5 h-5 flex-shrink-0" :class="[item.active ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-600 dark:group-hover:text-neutral-300']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <!-- Pricing Icon -->
                        <svg v-else-if="item.icon === 'pricing'" class="w-5 h-5 flex-shrink-0" :class="[item.active ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-600 dark:group-hover:text-neutral-300']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <!-- Info Icon -->
                        <svg v-else class="w-5 h-5 flex-shrink-0" :class="[item.active ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-600 dark:group-hover:text-neutral-300']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span v-if="sidebarOpen">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>

            <!-- User Section -->
            <div v-if="user" class="p-3 border-t border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-3 px-3 py-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500/10 to-secondary-500/15 text-primary-600 dark:text-primary-400 font-bold border border-primary-500/20">
                        {{ initials }}
                    </div>
                    <div v-if="sidebarOpen" class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ user.name }}</p>
                        <p class="text-xs font-medium" :class="creditsClass">
                            🪙 {{ user.credits }} credits
                        </p>
                    </div>
                </div>
                <div v-if="sidebarOpen" class="mt-2 space-y-1">
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Profile Settings
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-danger-600 dark:text-danger-400 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors w-full"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar -->
            <header class="h-16 flex items-center justify-between px-6 bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        <svg class="w-6 h-6 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <button
                        v-if="!sidebarOpen"
                        @click="sidebarOpen = !sidebarOpen"
                        class="hidden lg:flex p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div v-if="$slots.pageHeader">
                        <slot name="pageHeader" />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
