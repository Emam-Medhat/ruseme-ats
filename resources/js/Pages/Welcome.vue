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
    <Head :title="t('brand')" />

    <div class="min-h-screen bg-gradient-to-b from-[#FAF8F5] via-[#F4F1EC] to-[#FFFFFF] text-zinc-900 font-sans overflow-x-hidden selection:bg-indigo-500/10 relative">
        <!-- Ambient Luxurious Background Glows -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-48 left-1/2 -translate-x-1/2 w-[1200px] h-[650px] rounded-full bg-amber-500/[0.03] blur-[150px] mix-blend-multiply" />
            <div class="absolute top-[350px] -right-40 w-[600px] h-[600px] rounded-full bg-indigo-500/[0.04] blur-[140px] mix-blend-multiply" />
            <div class="absolute bottom-20 -left-40 w-[550px] h-[550px] rounded-full bg-violet-500/[0.03] blur-[120px] mix-blend-multiply" />
            
            <!-- Grid Background Overlay -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M0 0h40v40H0z' fill='none' stroke='black' stroke-width='0.5'/%3E%3C/svg%3E&quot;);"></div>
        </div>

        <!-- Premium Header -->
        <header class="relative z-20 mx-auto max-w-6xl px-6 py-4 flex items-center justify-between border-b border-zinc-200/40 bg-[#FAF8F5]/75 backdrop-blur-md sticky top-0 shadow-sm shadow-zinc-150/10">
            <div class="flex items-center gap-2.5 font-extrabold text-base tracking-tight text-zinc-950">
                <span class="flex h-8.5 w-8.5 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-650 via-violet-650 to-amber-600 shadow-lg shadow-indigo-600/15 font-black text-white text-base">✦</span>
                {{ page.props.cvGenius?.name || t('brand') }}
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <!-- Locale Toggle -->
                <button 
                    type="button" 
                    class="rounded-xl px-3.5 py-1.5 border border-zinc-200 hover:border-zinc-300 bg-white hover:bg-zinc-50 text-zinc-700 transition-all font-bold tracking-wide shadow-sm" 
                    @click="appStore.setLocale(appStore.locale === 'ar' ? 'en' : 'ar')"
                >
                    {{ appStore.locale === 'ar' ? 'English' : 'العربية' }}
                </button>
                
                <Link v-if="canLogin && !page.props.auth?.user" :href="route('login')" class="text-zinc-550 hover:text-zinc-950 transition-colors py-2 px-3">
                    {{ t('nav.login') }}
                </Link>
                <Link v-if="canRegister && !page.props.auth?.user" :href="route('register')" class="relative group overflow-hidden rounded-xl bg-gradient-to-r from-indigo-650 to-violet-650 px-6 py-2.5 font-extrabold text-white transition-all hover:shadow-lg hover:shadow-indigo-600/20 hover:scale-[1.01] active:scale-[0.98]">
                    <span class="relative z-10">{{ t('nav.register') }}</span>
                </Link>
                <Link v-if="page.props.auth?.user" :href="route('dashboard')" class="rounded-xl bg-gradient-to-r from-indigo-650 to-violet-650 px-6 py-2.5 font-extrabold text-white hover:shadow-lg hover:shadow-indigo-600/20 transition-all hover:scale-[1.01] active:scale-[0.98]">
                    {{ t('nav.dashboard') }}
                </Link>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10 mx-auto max-w-6xl px-6 pb-28 pt-16 sm:pt-24">
            <div class="grid items-center gap-16 lg:grid-cols-12">
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/5 px-4 py-1.5 text-[10px] font-black uppercase tracking-wider text-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ page.props.cvGenius?.tagline || t('tagline') }}
                    </div>
                    <h1 class="text-4xl font-black leading-tight tracking-tight sm:text-6xl text-zinc-950">
                        {{ t('hero.title') }}
                    </h1>
                    <p class="text-base text-zinc-650 leading-relaxed max-w-xl mx-auto lg:mx-0 font-semibold">
                        {{ t('hero.subtitle') }}
                    </p>
                    
                    <!-- Action buttons -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-4">
                        <Link :href="page.props.auth?.user ? route('upload') : route('register')" class="rounded-2xl bg-gradient-to-r from-indigo-650 via-violet-650 to-indigo-650 px-8 py-4 text-sm font-black text-white shadow-xl shadow-indigo-600/15 hover:shadow-indigo-600/35 hover:-translate-y-0.5 hover:scale-[1.01] active:scale-[0.98] transition-all tracking-wider uppercase">
                            {{ t('hero.cta') }}
                        </Link>
                        <Link :href="route('pricing')" class="rounded-2xl border border-zinc-300 bg-white px-8 py-4 text-sm font-black text-zinc-800 hover:bg-zinc-50 hover:-translate-y-0.5 transition-all tracking-wider uppercase shadow-sm">
                            {{ t('nav.pricing') }}
                        </Link>
                    </div>

                    <!-- Trust indicators / Monochrome Social Proof -->
                    <div class="pt-8 border-t border-zinc-200/60 max-w-lg mx-auto lg:mx-0">
                        <p class="text-[9px] font-black uppercase tracking-widest text-zinc-400">Trusted by top professionals at</p>
                        <div class="flex items-center gap-6 mt-3 text-[11px] font-black text-zinc-400/80 justify-center lg:justify-start tracking-wide">
                            <span>GOOGLE</span>
                            <span>MCKINSEY</span>
                            <span>STRIPE</span>
                            <span>AMAZON</span>
                            <span>GOLDMAN SACHS</span>
                        </div>
                    </div>
                </div>

                <!-- Showcase Component -->
                <div class="lg:col-span-5 relative">
                    <div class="absolute -inset-1.5 rounded-3xl bg-gradient-to-r from-indigo-500 via-violet-650 to-amber-500 opacity-10 blur-2xl"></div>
                    <div class="relative rounded-3xl border border-zinc-200/80 bg-white p-7 shadow-2xl shadow-zinc-300/40 space-y-6">
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-600 to-amber-500"></div>

                        <!-- Score Display Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div v-for="s in [{l:'Overall',v:84,c:'text-emerald-600',bg:'bg-emerald-500/5 border-emerald-500/10'},{l:'ATS Check',v:78,c:'text-indigo-650',bg:'bg-indigo-500/5 border-indigo-500/10'},{l:'Recruiter',v:81,c:'text-violet-650',bg:'bg-violet-500/5 border-violet-500/10'}]" :key="s.l" class="rounded-2xl border p-4 text-center" :class="s.bg">
                                <p class="text-[9px] uppercase font-bold tracking-widest text-zinc-400 mb-1.5 leading-none">{{ s.l }}</p>
                                <p class="text-3xl font-black" :class="s.c">{{ s.v }}</p>
                            </div>
                        </div>

                        <!-- Mini Code Audit Box -->
                        <div class="rounded-2xl bg-[#FAF9F7] border border-zinc-200 p-5 space-y-4 font-mono text-[11px] text-zinc-700 leading-relaxed shadow-sm">
                            <div class="flex items-center justify-between border-b border-zinc-200/80 pb-2.5">
                                <span class="text-rose-600 font-bold bg-rose-500/10 px-2 py-0.5 rounded text-[10px]">← Weak Bullet Point</span>
                                <span class="text-zinc-450 text-[10px] font-bold">Action Verb Gap</span>
                            </div>
                            <p class="italic text-zinc-500 font-medium">"Worked on building a backend API and fixed user login issues..."</p>
                            
                            <div class="flex items-center justify-between border-b border-zinc-200/80 pb-2.5 pt-1">
                                <span class="text-emerald-700 font-bold bg-emerald-500/10 px-2 py-0.5 rounded text-[10px]">✦ AI Exec Rewrite</span>
                                <span class="text-indigo-650 text-[10px] font-bold">+12 Pts Impact</span>
                            </div>
                            <p class="text-zinc-950 font-extrabold">"Engineered high-concurrency microservice APIs serving 50k+ daily active users, reducing response latencies by 35%."</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-36">
                <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/5 text-amber-800 text-[10px] font-black rounded-full uppercase tracking-wider border border-amber-500/10">
                        ✦ Advanced Features
                    </span>
                    <h2 class="text-3xl font-black text-zinc-950 tracking-tight sm:text-4xl">Full-Suite Career Acceleration</h2>
                    <p class="text-zinc-500 text-sm font-semibold">Everything you need to beat automated screening algorithms and impress human recruiters.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div 
                        v-for="f in features" 
                        :key="f.title" 
                        class="rounded-3xl border border-zinc-200/80 bg-white/60 p-8 backdrop-blur-sm hover:bg-white hover:border-amber-500/30 hover:shadow-xl hover:shadow-zinc-300/10 hover:-translate-y-1 transition-all duration-300 group"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/5 text-amber-700 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300 border border-amber-500/10">
                            {{ f.icon }}
                        </div>
                        <h3 class="font-black text-base text-zinc-900 mb-2 tracking-tight">{{ f.title }}</h3>
                        <p class="text-xs text-zinc-500 leading-relaxed font-semibold">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
