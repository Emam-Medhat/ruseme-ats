<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="LinkedIn Profile Optimization" />
    <AppLayout>
        <div class="relative min-h-screen py-24 overflow-hidden flex items-center justify-center">
            <!-- Background Glow Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden -z-10">
                <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] rounded-full bg-indigo-600/5 blur-[120px] mix-blend-screen" />
                <div class="absolute bottom-20 right-10 w-[500px] h-[500px] rounded-full bg-violet-600/5 blur-[130px] mix-blend-screen" />
            </div>

            <div class="mx-auto max-w-4xl px-6 sm:px-8 text-center flex flex-col items-center">
                <!-- Glowing Link Icon -->
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-600 to-indigo-600 flex items-center justify-center text-white text-3xl shadow-xl shadow-indigo-600/20 mb-8 animate-pulse">
                    🔗
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/5 dark:bg-indigo-500/10 text-indigo-500 text-[10px] font-black rounded-full uppercase tracking-wider border border-indigo-500/10 dark:border-indigo-400/20 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Coming Soon Feature
                </span>

                <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight sm:text-5xl max-w-2xl leading-tight">
                    LinkedIn Profile Audit & Optimization
                </h1>

                <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto font-semibold leading-relaxed">
                    Overhaul your professional social presence. Write click-worthy headlines, summarize your career trajectory in a premium 'About' section, and structure experience sections powered by CV Genius AI.
                </p>

                <!-- Mockup LinkedIn Card -->
                <div class="mt-12 w-full max-w-xl bg-white dark:bg-zinc-900/60 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-xl relative overflow-hidden text-left">
                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500"></div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-zinc-200 dark:bg-zinc-850 flex items-center justify-center font-bold text-zinc-500">
                            👤
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="h-4 w-32 bg-zinc-200 dark:bg-zinc-800 rounded-md"></div>
                            <div class="space-y-1.5">
                                <div class="h-3.5 w-full bg-gradient-to-r from-indigo-500/15 via-violet-500/15 to-transparent rounded-md border border-indigo-500/10 p-2 text-[10px] font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                    ✦ AI Suggestion: Senior Full-Stack Engineer | Building Scalable Laravel & React Microservices
                                </div>
                                <div class="h-3 w-4/5 bg-zinc-200 dark:bg-zinc-800 rounded-md"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <Link 
                        :href="route('dashboard')" 
                        class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Back to Dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
