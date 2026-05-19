<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const plans = computed(() => Object.entries(page.props.cvGenius?.plans || {}));
</script>

<template>
    <Head title="Pricing" />
    <AppLayout>
        <div class="mx-auto max-w-5xl px-4 py-12">
            <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white">Simple, transparent pricing</h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">Start free. Upgrade when you need unlimited scans and job matching.</p>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <div v-for="[key, plan] in plans" :key="key"
                     :class="['rounded-2xl border p-6', key === 'pro' ? 'border-violet-500 bg-violet-50/50 dark:bg-violet-950/30 shadow-lg' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900']">
                    <h2 class="text-lg font-bold">{{ plan.name }}</h2>
                    <p class="mt-2 text-3xl font-extrabold">
                        <template v-if="plan.price_monthly">${{ plan.price_monthly }}<span class="text-sm font-normal text-zinc-500">/mo</span></template>
                        <template v-else>Free</template>
                    </p>
                    <p class="mt-1 text-sm text-zinc-500">{{ plan.credits }} credits</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <li v-for="f in plan.features" :key="f">✓ {{ f.replace(/_/g, ' ') }}</li>
                    </ul>
                    <Link :href="route('register')" class="mt-6 block w-full rounded-xl bg-violet-600 py-2.5 text-center text-sm font-bold text-white hover:bg-violet-700">
                        Get started
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
