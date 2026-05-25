<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';
import { useAppStore } from '@/stores/app';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const { t } = useI18n();
const appStore = useAppStore();
const page = usePage();

const features = [
    { icon: '🎯', title: '20-Section Deep Scan', desc: 'Contact, experience, ATS compliance, grammar, gaps, overlaps & more.' },
    { icon: '📊', title: 'Recruiter + ATS Scores', desc: 'Comprehensive diagnostics across overall, impact, brevity, style, and keywords.' },
    { icon: '✍️', title: 'Line-by-Line AI Rewrites', desc: 'Highlights weak bullet points and delivers high-performance executive alternatives.' },
    { icon: '🔗', title: 'Job Description Matcher', desc: 'Instantly tailor your CV to any target job description or LinkedIn listing.' },
];
</script>

<template>
    <Head :title="t('brand')">
        <meta name="description" :content="t('hero.subtitle')" />
        <meta property="og:title" :content="t('brand')" />
        <meta property="og:description" :content="t('hero.subtitle')" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="t('brand')" />
        <meta name="twitter:description" :content="t('hero.subtitle')" />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-primary-50 via-secondary-50 to-white text-neutral-900 font-sans overflow-x-hidden selection:bg-primary-500/10 relative">
        <!-- Ambient Luxurious Background Glows -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-48 left-1/2 -translate-x-1/2 w-[1200px] h-[650px] rounded-full bg-primary-500/[0.08] blur-[150px] mix-blend-multiply" />
            <div class="absolute top-[350px] -right-40 w-[600px] h-[600px] rounded-full bg-secondary-500/[0.12] blur-[140px] mix-blend-multiply" />
            <div class="absolute bottom-20 -left-40 w-[550px] h-[550px] rounded-full bg-accent-500/[0.08] blur-[120px] mix-blend-multiply" />

            <!-- Grid Background Overlay -->
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M0 0h40v40H0z' fill='none' stroke='%236366F1' stroke-width='0.5'/%3E%3C/svg%3E&quot;);"></div>
        </div>

        <!-- Premium Header -->
        <header class="relative z-20 mx-auto max-w-6xl px-6 py-4 flex items-center justify-between border-b border-neutral-200/50 bg-white/80 backdrop-blur-md sticky top-0 shadow-sm shadow-neutral-200/20">
            <div class="flex items-center gap-2.5 font-extrabold text-base tracking-tight text-neutral-950">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-600 via-secondary-600 to-accent-600 shadow-lg shadow-primary-500/30 font-black text-white text-base">✦</span>
                {{ page.props.cvGenius?.name || t('brand') }}
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <!-- Locale Toggle -->
                <button
                    type="button"
                    class="rounded-xl px-3.5 py-1.5 border border-neutral-200 hover:border-primary-300 bg-white hover:bg-primary-50 text-neutral-700 transition-all font-bold tracking-wide shadow-sm"
                    @click="appStore.setLocale(appStore.locale === 'ar' ? 'en' : 'ar')"
                >
                    {{ appStore.locale === 'ar' ? 'English' : 'العربية' }}
                </button>

                <Link v-if="canLogin && !page.props.auth?.user" :href="route('login')" class="text-neutral-600 hover:text-primary-600 transition-colors py-2 px-3">
                    {{ t('nav.login') }}
                </Link>
                <Link v-if="canRegister && !page.props.auth?.user" :href="route('register')" class="relative group overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 via-secondary-600 to-primary-600 px-6 py-2.5 font-extrabold text-white transition-all hover:shadow-xl hover:shadow-primary-500/30 hover:scale-[1.01] active:scale-[0.98]">
                    <span class="relative z-10">{{ t('nav.register') }}</span>
                </Link>
                <Link v-if="page.props.auth?.user" :href="route('dashboard')" class="rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 px-6 py-2.5 font-extrabold text-white hover:shadow-xl hover:shadow-primary-500/30 transition-all hover:scale-[1.01] active:scale-[0.98]">
                    {{ t('nav.dashboard') }}
                </Link>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10 mx-auto max-w-6xl px-6 pb-28 pt-16 sm:pt-24">
            <div class="grid items-center gap-16 lg:grid-cols-12">
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full border border-secondary-500/20 bg-secondary-500/10 px-4 py-1.5 text-[10px] font-black uppercase tracking-wider text-secondary-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary-500 animate-pulse"></span>
                        {{ page.props.cvGenius?.tagline || t('tagline') }}
                    </div>
                    <h1 class="text-4xl font-black leading-tight tracking-tight sm:text-6xl text-neutral-950">
                        {{ t('hero.title') }}
                    </h1>
                    <p class="text-base text-neutral-600 leading-relaxed max-w-xl mx-auto lg:mx-0 font-semibold">
                        {{ t('hero.subtitle') }}
                    </p>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-4">
                        <Link :href="page.props.auth?.user ? route('upload') : route('register')" class="rounded-2xl bg-gradient-to-r from-primary-600 via-secondary-600 to-primary-600 px-8 py-4 text-sm font-black text-white shadow-xl shadow-primary-500/35 hover:shadow-primary-500/50 hover:-translate-y-0.5 hover:scale-[1.01] active:scale-[0.98] transition-all tracking-wider uppercase">
                            {{ t('hero.cta') }}
                        </Link>
                        <Link :href="route('pricing')" class="rounded-2xl border border-neutral-300 bg-white px-8 py-4 text-sm font-black text-neutral-800 hover:bg-primary-50 hover:-translate-y-0.5 transition-all tracking-wider uppercase shadow-sm hover:border-primary-300">
                            {{ t('nav.pricing') }}
                        </Link>
                    </div>

                    <!-- Trust indicators / Monochrome Social Proof -->
                    <div class="pt-8 border-t border-neutral-200/60 max-w-lg mx-auto lg:mx-0">
                        <p class="text-[9px] font-black uppercase tracking-widest text-neutral-400">Trusted by top professionals at</p>
                        <div class="flex items-center gap-6 mt-3 text-[11px] font-black text-neutral-500/80 justify-center lg:justify-start tracking-wide">
                            <span class="text-neutral-600">GOOGLE</span>
                            <span class="text-neutral-600">MCKINSEY</span>
                            <span class="text-neutral-600">STRIPE</span>
                            <span class="text-neutral-600">AMAZON</span>
                            <span class="text-neutral-600">GOLDMAN SACHS</span>
                        </div>
                    </div>
                </div>

                <!-- Showcase Component -->
                <div class="lg:col-span-5 relative">
                    <div class="absolute -inset-1.5 rounded-3xl bg-gradient-to-r from-primary-500 via-secondary-500 to-accent-500 opacity-15 blur-2xl"></div>
                    <div class="relative rounded-3xl border border-neutral-200/80 bg-white p-7 shadow-2xl shadow-neutral-300/40 space-y-6">
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-primary-500 via-secondary-500 to-accent-500"></div>

                        <!-- Score Display Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div v-for="s in [{l:'Overall',v:84,c:'text-success-600',bg:'bg-success-500/5 border-success-500/10'},{l:'ATS Check',v:78,c:'text-primary-600',bg:'bg-primary-500/5 border-primary-500/10'},{l:'Recruiter',v:81,c:'text-secondary-600',bg:'bg-secondary-500/5 border-secondary-500/10'}]" :key="s.l" class="rounded-2xl border p-4 text-center" :class="s.bg">
                                <p class="text-[9px] uppercase font-bold tracking-widest text-neutral-500 mb-1.5 leading-none">{{ s.l }}</p>
                                <p class="text-3xl font-black" :class="s.c">{{ s.v }}</p>
                            </div>
                        </div>

                        <!-- Mini Code Audit Box -->
                        <div class="rounded-2xl bg-gradient-to-br from-neutral-50 to-primary-50 border border-neutral-200 p-5 space-y-4 font-mono text-[11px] text-neutral-700 leading-relaxed shadow-sm">
                            <div class="flex items-center justify-between border-b border-neutral-200/80 pb-2.5">
                                <span class="text-danger-600 font-bold bg-danger-500/10 px-2 py-0.5 rounded text-[10px]">← Weak Bullet Point</span>
                                <span class="text-neutral-500 text-[10px] font-bold">Action Verb Gap</span>
                            </div>
                            <p class="italic text-neutral-500 font-medium">"Worked on building a backend API and fixed user login issues..."</p>

                            <div class="flex items-center justify-between border-b border-neutral-200/80 pb-2.5 pt-1">
                                <span class="text-accent-700 font-bold bg-accent-500/10 px-2 py-0.5 rounded text-[10px]">✦ AI Exec Rewrite</span>
                                <span class="text-primary-600 text-[10px] font-bold">+12 Pts Impact</span>
                            </div>
                            <p class="text-neutral-950 font-extrabold">"Engineered high-concurrency microservice APIs serving 50k+ daily active users, reducing response latencies by 35%."</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-36">
                <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-secondary-500/10 text-secondary-700 text-[10px] font-black rounded-full uppercase tracking-wider border border-secondary-500/20">
                        ✦ Advanced Features
                    </span>
                    <h2 class="text-3xl font-black text-neutral-950 tracking-tight sm:text-4xl">Full-Suite Career Acceleration</h2>
                    <p class="text-neutral-500 text-sm font-semibold">Everything you need to beat automated screening algorithms and impress human recruiters.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="rounded-3xl border border-neutral-200/80 bg-white/70 p-8 backdrop-blur-sm hover:bg-white hover:border-primary-300 hover:shadow-xl hover:shadow-primary-500/20 hover:-translate-y-1 transition-all duration-300 group"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500/10 to-secondary-500/10 text-primary-700 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300 border border-primary-500/20">
                            {{ f.icon }}
                        </div>
                        <h3 class="font-black text-base text-neutral-900 mb-2 tracking-tight">{{ f.title }}</h3>
                        <p class="text-xs text-neutral-500 leading-relaxed font-semibold">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
