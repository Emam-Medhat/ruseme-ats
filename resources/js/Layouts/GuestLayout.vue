<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-50 text-zinc-900 font-sans p-6 overflow-hidden relative selection:bg-indigo-500/10"
    >
        <!-- Ambient Radial Background Glows -->
        <div class="absolute inset-0 pointer-events-none z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[500px] rounded-full bg-indigo-600/5 blur-[130px] mix-blend-multiply" />
            <div class="absolute -bottom-40 left-1/3 -translate-x-1/2 w-[700px] h-[400px] rounded-full bg-violet-600/4 blur-[110px] mix-blend-multiply" />
            
            <!-- Grid Background Overlay -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30'%3E%3Cpath d='M0 0h30v30H0z' fill='none' stroke='black' stroke-width='0.5'/%3E%3C/svg%3E&quot;);"></div>
        </div>

        <!-- Glowing Decorative Orb -->
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-indigo-500/5 blur-3xl pointer-events-none z-0" />

        <div class="relative z-10 w-full max-w-md px-2 flex flex-col items-center">
            <!-- Brand Logo & Header -->
            <div class="mb-8 text-center flex flex-col items-center">
                <Link href="/" class="group flex flex-col items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-600 to-indigo-600 shadow-xl shadow-indigo-500/20 group-hover:scale-105 active:scale-95 transition-all duration-300">
                        <span class="text-2xl font-black text-white">✦</span>
                    </div>
                    <span class="text-2xl font-black tracking-tight text-zinc-900 mt-1">
                        {{ page.props.cvGenius?.name || t('brand') }}
                    </span>
                </Link>
                <p class="text-zinc-400 text-[10px] uppercase font-bold tracking-widest mt-2.5">
                    {{ page.props.cvGenius?.tagline || t('tagline') }}
                </p>
            </div>

            <!-- Glassmorphic Auth Card -->
            <div
                class="w-full relative overflow-hidden rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl shadow-zinc-200/40 z-10"
            >
                <!-- Interactive top border highlight -->
                <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent" />
                
                <slot />
            </div>
            
            <!-- Simple Footer Credits -->
            <div class="mt-10 text-center text-zinc-400 text-xs font-semibold tracking-wide">
                &copy; {{ new Date().getFullYear() }} {{ page.props.cvGenius?.name || t('brand') }}. All rights reserved.
            </div>
        </div>
    </div>
</template>
