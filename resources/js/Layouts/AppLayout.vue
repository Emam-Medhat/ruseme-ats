<script setup>
import AnalysisLoadingOverlay from '@/Components/CvGenius/AnalysisLoadingOverlay.vue';
import { useAnalysisLoaderStore } from '@/stores/analysisLoader';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { storeToRefs } from 'pinia';

const analysisLoader = useAnalysisLoaderStore();
const { visible: analysisVisible, progress: analysisProgress } = storeToRefs(analysisLoader);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const scrolled = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 8;
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

const creditsClass = computed(() => {
    const c = user.value?.credits ?? 0;
    if (c > 5) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    }
    if (c >= 2) {
        return 'bg-amber-50 text-amber-800 border-amber-200';
    }
    return 'bg-rose-50 text-rose-700 border-rose-200';
});

const initials = computed(() => {
    const name = user.value?.name || '';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '?';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const mobileOpen = ref(false);
</script>

<template>
    <AnalysisLoadingOverlay
        :visible="analysisVisible"
        :progress="analysisProgress"
        :steps="analysisLoader.steps"
        :step-state="analysisLoader.stepState"
    />

    <div class="flex min-h-screen flex-col bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
        <nav
            :class="[
                'fixed inset-x-0 top-0 z-50 border-b transition-all duration-300',
                scrolled
                    ? 'border-primary-light/80 bg-white/90 backdrop-blur-md dark:border-zinc-800/80 dark:bg-zinc-950/90'
                    : 'border-primary-light bg-white/95 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-950/95',
            ]"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-8">
                    <Link href="/" class="flex items-center gap-2 text-lg font-black text-primary">
                        <span aria-hidden="true">⚡</span>
                        <span>{{ page.props.cvGenius?.name || 'CV Genius AI' }}</span>
                    </Link>
                    <div class="hidden items-center gap-6 text-sm font-semibold text-zinc-600 dark:text-zinc-300 md:flex">
                        <Link
                            :href="route('dashboard')"
                            class="border-b-2 border-transparent pb-0.5 transition hover:text-primary"
                            :class="{ '!border-primary text-primary': route().current('dashboard') }"
                        >
                            Dashboard
                        </Link>
                        <Link
                            :href="route('upload')"
                            class="border-b-2 border-transparent pb-0.5 transition hover:text-primary"
                            :class="{ '!border-primary text-primary': route().current('upload') }"
                        >
                            Upload
                        </Link>
                        <Link
                            :href="route('history')"
                            class="border-b-2 border-transparent pb-0.5 transition hover:text-primary"
                            :class="{ '!border-primary text-primary': route().current('history') }"
                        >
                            History
                        </Link>
                        <Link
                            :href="route('pricing')"
                            class="border-b-2 border-transparent pb-0.5 transition hover:text-primary"
                            :class="{ '!border-primary text-primary': route().current('pricing') }"
                        >
                            Pricing
                        </Link>
                        <Link
                            :href="route('how-it-works')"
                            class="border-b-2 border-transparent pb-0.5 transition hover:text-primary"
                            :class="{ '!border-primary text-primary': route().current('how-it-works') }"
                        >
                            How It Works
                        </Link>
                    </div>
                </div>

                <div class="hidden items-center gap-4 md:flex" v-if="user">
                    <div
                        :class="[
                            'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold',
                            creditsClass,
                        ]"
                    >
                        <span aria-hidden="true">🪙</span>
                        <span>{{ user.credits }} credits</span>
                    </div>

                    <div class="relative group">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-full border border-primary-light px-2 py-1 text-left transition hover:border-primary/40 dark:border-zinc-700"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-light text-xs font-black text-primary dark:bg-primary/20"
                            >
                                {{ initials }}
                            </span>
                            <span class="max-w-[120px] truncate text-sm font-semibold">{{ user.name }}</span>
                            <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                        <div
                            class="pointer-events-none invisible absolute right-0 mt-2 w-48 origin-top-right scale-95 rounded-xl border border-primary-light bg-white py-1 text-sm opacity-0 shadow-lg transition group-hover:pointer-events-auto group-hover:visible group-hover:scale-100 group-hover:opacity-100 dark:border-zinc-700 dark:bg-zinc-900"
                        >
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 font-medium text-zinc-700 hover:bg-primary-light/60 dark:text-zinc-200 dark:hover:bg-zinc-800"
                            >
                                Profile
                            </Link>
                            <Link
                                href="#"
                                class="block px-4 py-2 font-medium text-zinc-500 hover:bg-primary-light/40 dark:text-zinc-400 dark:hover:bg-zinc-800"
                            >
                                Settings
                            </Link>
                            <div class="my-1 border-t border-primary-light dark:border-zinc-700" />
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full px-4 py-2 text-left font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-zinc-800"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-200 p-2 md:hidden dark:border-zinc-700"
                    @click="mobileOpen = !mobileOpen"
                    aria-label="Menu"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>

            <div
                v-show="mobileOpen"
                class="border-t border-primary-light bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 md:hidden"
            >
                <div class="flex flex-col gap-3 text-sm font-semibold">
                    <Link :href="route('dashboard')" @click="mobileOpen = false">Dashboard</Link>
                    <Link :href="route('history')" @click="mobileOpen = false">History</Link>
                    <Link :href="route('pricing')" @click="mobileOpen = false">Pricing</Link>
                    <Link :href="route('how-it-works')" @click="mobileOpen = false">How It Works</Link>
                    <Link v-if="user" :href="route('profile.edit')" @click="mobileOpen = false">Profile</Link>
                </div>
            </div>
        </nav>

        <div class="flex-1 pt-[72px]">
            <div v-if="$slots.pageHeader" class="border-b border-primary-light/80 bg-white/80 dark:border-zinc-800 dark:bg-zinc-950/80">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <slot name="pageHeader" />
                </div>
            </div>
            <main>
                <slot />
            </main>
        </div>

        <footer class="bg-cvgenius-footer text-white/70">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div class="space-y-4">
                    <div class="text-lg font-black text-white">
                        <span class="text-primary-light">⚡</span> CVGenius
                    </div>
                    <p class="text-sm leading-relaxed">
                        AI-powered resume optimization for Arab job seekers
                    </p>
                    <div class="flex gap-3 text-white/80">
                        <a href="#" class="hover:text-primary" aria-label="LinkedIn">in</a>
                        <a href="#" class="hover:text-primary" aria-label="Twitter">𝕏</a>
                        <a href="#" class="hover:text-primary" aria-label="Instagram">◎</a>
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold text-white">Product</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="transition hover:text-primary">Features</a></li>
                        <li><Link :href="route('pricing')" class="transition hover:text-primary">Pricing</Link></li>
                        <li><a href="#" class="transition hover:text-primary">Templates</a></li>
                        <li><Link :href="route('how-it-works')" class="transition hover:text-primary">How It Works</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold text-white">Company</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="transition hover:text-primary">About</a></li>
                        <li><a href="#" class="transition hover:text-primary">Blog</a></li>
                        <li><a href="#" class="transition hover:text-primary">Careers</a></li>
                        <li><a href="#" class="transition hover:text-primary">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold text-white">Support</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="transition hover:text-primary">Help Center</a></li>
                        <li><a href="#" class="transition hover:text-primary">Contact</a></li>
                        <li><a href="#" class="transition hover:text-primary">Privacy Policy</a></li>
                        <li><a href="#" class="transition hover:text-primary">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 py-6 text-center text-xs text-white/50">
                © 2025 CVGenius · Built with ❤️ for Arab job seekers
            </div>
        </footer>
    </div>
</template>
