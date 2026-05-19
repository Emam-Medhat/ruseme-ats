<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    jobMatches: Array,
});
</script>

<template>
    <Head title="History" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-black text-zinc-900 dark:text-white">Job match history</h1>
            <div v-if="!jobMatches?.length" class="rounded-2xl border border-primary-light bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
                No job matches yet. Run the
                <Link :href="route('dashboard')" class="font-semibold text-primary hover:underline">target matcher</Link>
                from a resume report.
            </div>
            <div v-else class="overflow-hidden rounded-2xl border border-primary-light bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-primary-light text-sm dark:divide-zinc-800">
                    <thead class="bg-primary-light/40 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold text-zinc-700 dark:text-zinc-200">Resume</th>
                            <th class="px-4 py-3 text-left font-bold text-zinc-700 dark:text-zinc-200">Job title</th>
                            <th class="px-4 py-3 text-left font-bold text-zinc-700 dark:text-zinc-200">Score</th>
                            <th class="px-4 py-3 text-left font-bold text-zinc-700 dark:text-zinc-200">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary-light dark:divide-zinc-800">
                        <tr v-for="m in jobMatches" :key="m.id">
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-200">
                                {{ m.resume?.filename || m.resume?.title || 'Resume' }}
                            </td>
                            <td class="px-4 py-3">{{ m.job_title }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ m.match_score }}%</td>
                            <td class="px-4 py-3 text-zinc-500">{{ new Date(m.created_at).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
