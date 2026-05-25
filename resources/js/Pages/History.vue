<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    jobMatches: Array,
});

const getScoreClass = (score) => {
    if (score >= 80) return 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400 border-success-200 dark:border-success-800';
    if (score >= 60) return 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400 border-warning-200 dark:border-warning-800';
    return 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400 border-danger-200 dark:border-danger-800';
};
</script>

<template>
    <Head title="History" />

    <AppLayout>
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Job Match History</h1>
                    <p class="text-neutral-500 dark:text-neutral-400 mt-2">Track and review compatibility scores for all previous job description match scans.</p>
                </div>
                <Link :href="route('upload')" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload Resume
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="!jobMatches?.length" class="card p-12 text-center">
                <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-800 rounded-xl mx-auto flex items-center justify-center mb-4 text-3xl">
                    🔍
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">No match runs yet</h3>
                <p class="text-neutral-500 dark:text-neutral-400 mb-6 max-w-xs mx-auto">
                    Compare any of your uploaded resumes against specific target job listings to see matching scores.
                </p>
                <Link :href="route('dashboard')" class="btn-primary">
                    Go to Dashboard
                </Link>
            </div>

            <!-- Table -->
            <div v-else class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Resume File</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Target Job Title</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Match Score</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Scan Timestamp</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="m in jobMatches" :key="m.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">📄</span>
                                        <span class="font-semibold text-neutral-900 dark:text-white truncate max-w-[180px]" :title="m.resume?.filename || m.resume?.title">
                                            {{ m.resume?.filename || m.resume?.title || 'Resume' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-700 dark:text-neutral-300">
                                    {{ m.job_title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-semibold uppercase tracking-wider', getScoreClass(m.match_score)]">
                                        <span class="w-2 h-2 rounded-full bg-current" />
                                        {{ m.match_score }}% Match
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-500 dark:text-neutral-400">
                                    {{ new Date(m.created_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <Link
                                        :href="route('resumes.target', m.resume_id)"
                                        class="btn-ghost text-xs"
                                    >
                                        Inspect
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
