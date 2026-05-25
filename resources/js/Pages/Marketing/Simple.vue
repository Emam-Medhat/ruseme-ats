<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <div class="relative min-h-screen py-24 overflow-hidden">
            <!-- Background Glow Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden -z-10">
                <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] rounded-full bg-indigo-600/5 blur-[120px] mix-blend-screen" />
                <div class="absolute bottom-20 right-10 w-[500px] h-[500px] rounded-full bg-violet-600/5 blur-[130px] mix-blend-screen" />
            </div>

            <div class="mx-auto max-w-3xl px-6 sm:px-8">
                <!-- Inner Container -->
                <div class="bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 md:p-12 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500"></div>

                    <!-- Header -->
                    <div class="space-y-4 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/5 dark:bg-indigo-500/10 text-indigo-500 text-[10px] font-black rounded-full uppercase tracking-wider border border-indigo-500/10 dark:border-indigo-400/20">
                            Info Page
                        </span>
                        <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight sm:text-4xl">
                            {{ title }}
                        </h1>
                    </div>

                    <hr class="border-zinc-150 dark:border-zinc-800/80 mb-8" />

                    <!-- Main Text Body -->
                    <div class="prose dark:prose-invert max-w-none text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium space-y-6">
                        <slot>
                            <p>
                                This page is currently being prepared. We are bringing high-fidelity updates and premium resume optimization tools to your dashboard.
                            </p>
                            <p>
                                In the meantime, you can explore the dashboard to upload, audit, and upgrade your resume bullet points.
                            </p>
                        </slot>
                    </div>

                    <!-- Back CTA -->
                    <div class="mt-12 pt-6 border-t border-zinc-150 dark:border-zinc-800/65 flex justify-end">
                        <Link 
                            :href="route('dashboard')" 
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all hover:scale-[1.01] active:scale-[0.99]"
                        >
                            ← Back to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
