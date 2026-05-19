<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white">{{ title }}</h1>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                This section is coming soon. Explore the dashboard to analyze your resume.
            </p>
        </div>
    </AppLayout>
</template>
