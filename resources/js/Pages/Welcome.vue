<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';
import { useAppStore } from '@/stores/app';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const { t } = useI18n();
const appStore = useAppStore();

const features = [
    { icon: '🎯', title: '20-section deep scan', desc: 'Contact, experience, ATS, grammar, gaps, overlaps & more.' },
    { icon: '📊', title: 'Recruiter + ATS scores', desc: 'Overall, impact, brevity, style, keyword match.' },
    { icon: '✍️', title: 'Line-by-line rewrites', desc: 'Weak bullets highlighted with stronger alternatives.' },
    { icon: '🔗', title: 'Job description match', desc: 'Tailor your resume to any role with keyword gaps.' },
];
</script>

<template>
    <Head :title="t('brand')" />

    <div class="min-h-screen bg-[#0b0f1a] text-white overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 left-1/2 h-[520px] w-[720px] -translate-x-1/2 rounded-full bg-violet-600/30 blur-[120px]" />
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-cyan-500/20 blur-[100px]" />
        </div>

        <nav class="relative z-20 mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <div class="flex items-center gap-2 font-black text-lg">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600">✦</span>
                {{ $page.props.cvGenius?.name || t('brand') }}
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <button type="button" class="rounded-lg px-2 py-1 hover:bg-white/10" @click="appStore.setLocale(appStore.locale === 'ar' ? 'en' : 'ar')">
                    {{ appStore.locale === 'ar' ? 'EN' : 'عربي' }}
                </button>
                <button type="button" class="rounded-lg px-2 py-1 hover:bg-white/10" @click="appStore.toggleTheme()">◐</button>
                <Link v-if="canLogin && !$page.props.auth?.user" :href="route('login')" class="hover:text-violet-300">{{ t('nav.login') }}</Link>
                <Link v-if="canRegister && !$page.props.auth?.user" :href="route('register')" class="rounded-full bg-white px-4 py-2 font-bold text-[#0b0f1a] hover:bg-violet-100">
                    {{ t('nav.register') }}
                </Link>
                <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="rounded-full bg-gradient-to-r from-violet-500 to-indigo-600 px-4 py-2">
                    {{ t('nav.dashboard') }}
                </Link>
            </div>
        </nav>

        <main class="relative z-10 mx-auto max-w-6xl px-6 pb-24 pt-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="mb-4 inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-bold uppercase tracking-widest text-violet-200">
                        {{ $page.props.cvGenius?.tagline || t('tagline') }}
                    </p>
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                        {{ t('hero.title') }}
                    </h1>
                    <p class="mt-4 text-lg text-zinc-300 leading-relaxed">
                        {{ t('hero.subtitle') }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link :href="$page.props.auth?.user ? route('upload') : route('register')" class="rounded-xl bg-gradient-to-r from-violet-500 to-indigo-600 px-6 py-3 text-sm font-bold shadow-lg shadow-violet-500/30 hover:opacity-95">
                            {{ t('hero.cta') }}
                        </Link>
                        <Link :href="route('pricing')" class="rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-sm font-bold backdrop-blur hover:bg-white/10">
                            {{ t('nav.pricing') }}
                        </Link>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl shadow-2xl">
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div v-for="s in [{l:'Overall',v:84},{l:'ATS',v:78},{l:'Recruiter',v:81}]" :key="s.l" class="rounded-xl bg-[#0b0f1a]/60 p-3 text-center">
                            <p class="text-[10px] uppercase text-zinc-400">{{ s.l }}</p>
                            <p class="text-2xl font-extrabold text-violet-300">{{ s.v }}</p>
                        </div>
                    </div>
                    <div class="rounded-xl bg-[#0b0f1a]/80 p-4 text-xs leading-relaxed text-zinc-300 font-mono">
                        <p class="text-red-400 bg-red-400/10 px-2 py-1 rounded mb-2">← Weak: passive verb "worked on"</p>
                        <p>Engineered scalable APIs serving 50k+ daily users, cutting latency 35%.</p>
                    </div>
                </div>
            </div>

            <div class="mt-20 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="f in features" :key="f.title" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <div class="text-2xl mb-2">{{ f.icon }}</div>
                    <h3 class="font-bold">{{ f.title }}</h3>
                    <p class="mt-1 text-sm text-zinc-400">{{ f.desc }}</p>
                </div>
            </div>
        </main>
    </div>
</template>
