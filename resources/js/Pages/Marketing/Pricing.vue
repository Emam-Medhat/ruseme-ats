<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const plans = computed(() => Object.entries(page.props.cvGenius?.plans || {}));

// Helper to get styling attributes based on plan key
const getPlanStyles = (key) => {
    if (key === 'pro') {
        return {
            badge: 'bg-gradient-to-r from-indigo-500 to-violet-600 text-white ring-indigo-500/20',
            card: 'border-violet-500 dark:border-violet-400 bg-white dark:bg-zinc-900/60 shadow-xl shadow-violet-500/5 dark:shadow-violet-950/20 scale-105 md:scale-105 z-10 ring-4 ring-violet-500/10',
            button: 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white shadow-lg shadow-indigo-600/20 hover:shadow-indigo-500/35 hover:scale-[1.02]',
            iconColor: 'text-violet-500'
        };
    }
    if (key === 'premium' || key === 'enterprise') {
        return {
            badge: 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 ring-zinc-500/10',
            card: 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/40 shadow-sm hover:shadow-md hover:border-zinc-350 dark:hover:border-zinc-700',
            button: 'bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 hover:scale-[1.02]',
            iconColor: 'text-zinc-650 dark:text-zinc-400'
        };
    }
    // Free / Starter Plan
    return {
        badge: 'bg-zinc-100 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-400 ring-zinc-500/5',
        card: 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/40 shadow-sm hover:shadow-md hover:border-zinc-350 dark:hover:border-zinc-700',
        button: 'bg-white dark:bg-zinc-850 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-350 border border-zinc-200 dark:border-zinc-700 hover:scale-[1.02]',
        iconColor: 'text-zinc-400 dark:text-zinc-500'
    };
};
</script>

<template>
    <Head title="Pricing Plans" />
    <AppLayout>
        <div class="relative min-h-screen py-24 overflow-hidden">
            <!-- Background Glow Effects -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden -z-10">
                <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] rounded-full bg-indigo-600/5 blur-[120px] mix-blend-screen" />
                <div class="absolute bottom-20 right-10 w-[500px] h-[500px] rounded-full bg-violet-600/5 blur-[130px] mix-blend-screen" />
            </div>

            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <!-- Header -->
                <div class="text-center max-w-3xl mx-auto space-y-4 mb-20">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/5 dark:bg-indigo-500/10 text-indigo-500 text-[10px] font-black rounded-full uppercase tracking-wider border border-indigo-500/10 dark:border-indigo-400/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        Pricing Plans
                    </span>
                    <h1 class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight sm:text-5xl">
                        Simple, transparent pricing
                    </h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto font-semibold leading-relaxed">
                        Start scanning for free. Upgrade when you need unlimited analytics, deeper optimization, and real-time job matcher comparisons.
                    </p>
                </div>

                <!-- Pricing Cards Grid -->
                <div class="grid gap-8 md:grid-cols-3 items-stretch mt-12 px-2 md:px-0">
                    <div 
                        v-for="[key, plan] in plans" 
                        :key="key"
                        :class="[
                            'relative rounded-3xl border p-8 flex flex-col justify-between transition-all duration-300',
                            getPlanStyles(key).card
                        ]"
                    >
                        <!-- Most Popular Tag -->
                        <div v-if="key === 'pro'" class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="text-[9px] font-black tracking-widest uppercase px-3.5 py-1.5 rounded-full bg-gradient-to-r from-indigo-500 via-violet-600 to-indigo-600 text-white shadow-md shadow-indigo-500/20">
                                Most Popular
                            </span>
                        </div>

                        <!-- Top Content -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-zinc-900 dark:text-white capitalize">
                                    {{ plan.name }}
                                </h3>
                                <span :class="['text-[8.5px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-transparent ring-1', getPlanStyles(key).badge]">
                                    {{ key }}
                                </span>
                            </div>

                            <!-- Pricing Display -->
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight">
                                    <template v-if="plan.price_monthly">${{ plan.price_monthly }}</template>
                                    <template v-else>$0</template>
                                </span>
                                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500">
                                    /month
                                </span>
                            </div>

                            <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                Includes {{ plan.credits }} scan credits
                            </p>

                            <hr class="border-zinc-150 dark:border-zinc-800/80" />

                            <!-- Features List -->
                            <ul class="space-y-3.5 text-xs text-zinc-600 dark:text-zinc-300 font-semibold">
                                <li 
                                    v-for="f in plan.features" 
                                    :key="f"
                                    class="flex items-start gap-2.5"
                                >
                                    <span :class="['shrink-0 font-bold', getPlanStyles(key).iconColor]">✓</span>
                                    <span>{{ f.replace(/_/g, ' ') }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Bottom CTA -->
                        <div class="pt-8">
                            <Link 
                                :href="route('register')" 
                                :class="[
                                    'block w-full py-3.5 rounded-2xl text-center text-xs font-black uppercase tracking-wider transition-all duration-300 active:scale-[0.98]',
                                    getPlanStyles(key).button
                                ]"
                            >
                                Get Started
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Simple FAQ/Trust section -->
                <div class="mt-32 border-t border-zinc-200/60 dark:border-zinc-800/60 pt-20 max-w-4xl mx-auto space-y-12">
                    <div class="text-center space-y-2">
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Frequently Asked Questions</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">Everything you need to know about CV Genius plans.</p>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <h4 class="text-sm font-extrabold text-zinc-900 dark:text-white">How do scan credits work?</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-semibold">
                                Every time you scan a CV or match against a job description, 1 credit is used. Credits are refilled monthly on paid plans.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-sm font-extrabold text-zinc-900 dark:text-white">Can I cancel my subscription?</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-semibold">
                                Yes, you can cancel your subscription at any time from your account settings. You will retain access until the end of your billing cycle.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-sm font-extrabold text-zinc-900 dark:text-white">Is CV Genius ATS-friendly?</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-semibold">
                                Yes, all our resume templates and downloads are optimized to match the guidelines of popular parser engines such as Taleo, Workday, and Greenhouse.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-sm font-extrabold text-zinc-900 dark:text-white">Do you offer support?</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-semibold">
                                Paid users get priority email support for CV formatting, parser troubleshooting, and optimization advice.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
