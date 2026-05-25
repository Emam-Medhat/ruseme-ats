<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useAnalysisLoader } from '@/composables/useAnalysisLoader';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const loader = useAnalysisLoader();

const form = useForm({
    resume: null,
});

const fileInput = ref(null);
const dragging = ref(false);

const handleFileUpload = (e) => {
    const file = e.target.files[0] || e.dataTransfer.files[0];
    console.log("[CVGenius] File selected:", file);
    if (file) {
        const allowed = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        if (allowed.includes(file.type) || /\.(pdf|docx)$/i.test(file.name)) {
            form.resume = file;
        } else {
            alert('Please upload a PDF or DOCX file.');
        }
    }
};

const submitResume = () => {
    console.log("[CVGenius] Submitting resume for AI analysis...", form.resume);
    loader.start();
    form.post(route('resumes.upload'), {
        preserveScroll: true,
        onSuccess: () => loader.finish(),
        onError: () => loader.stop(),
        onFinish: () => loader.stop(),
    });
};

const resumes = computed(() => $page?.props?.resumes || []);
const totalScans = computed(() => resumes.value.length);
const avgScore = computed(() => {
    if (!resumes.value.length) return 0;
    const sum = resumes.value.reduce((acc, r) => acc + (r.score || 0), 0);
    return Math.round(sum / resumes.value.length);
});
const bestScore = computed(() => {
    if (!resumes.value.length) return 0;
    return Math.max(...resumes.value.map(r => r.score || 0));
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Dashboard</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Welcome back! Here's what's happening with your resumes.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('upload')" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload Resume
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ totalScans }}</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Scans</p>
                        </div>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center text-secondary-600 dark:text-secondary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ avgScore || '—' }}</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Average Score</p>
                        </div>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-success-100 dark:bg-success-900/30 flex items-center justify-center text-success-600 dark:text-success-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ bestScore || '—' }}</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Best Score</p>
                        </div>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center text-warning-600 dark:text-warning-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $page.props.auth.user.credits ?? 1 }}</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Credits Left</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Upload Section -->
                <div class="lg:col-span-2">
                    <div class="card overflow-hidden">
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                            <h3 class="font-semibold text-neutral-900 dark:text-white">Upload New Resume</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Get an instant ATS scan and AI-powered suggestions</p>
                        </div>
                        <div class="p-6">
                            <form @submit.prevent="submitResume">
                                <div
                                    class="border-2 border-dashed rounded-xl p-10 text-center transition-all duration-200 relative group cursor-pointer"
                                    :class="dragging
                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-[1.01]'
                                        : form.resume
                                            ? 'border-success-500/40 bg-success-50 dark:bg-success-900/20'
                                            : 'border-neutral-300 dark:border-neutral-700 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                                    @dragover.prevent="dragging = true"
                                    @dragleave.prevent="dragging = false"
                                    @drop.prevent="e => { dragging = false; handleFileUpload(e) }"
                                    @click="fileInput?.click()"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                        class="hidden"
                                        @change="handleFileUpload"
                                    >

                                    <div v-if="!form.resume" class="flex flex-col items-center pointer-events-none space-y-4">
                                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-200">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-neutral-900 dark:text-white">Click to upload or drag and drop</p>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">PDF or DOCX files up to 5MB</p>
                                        </div>
                                    </div>

                                    <div v-else class="flex flex-col items-center pointer-events-none space-y-4">
                                        <div class="w-16 h-16 bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 rounded-xl flex items-center justify-center shadow-sm">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-neutral-900 dark:text-white truncate max-w-xs">{{ form.resume.name }}</p>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ (form.resume.size / 1024 / 1024).toFixed(2) }} MB — Ready for analysis</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.errors.resume" class="mt-4 p-4 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 text-danger-600 dark:text-danger-400 text-sm rounded-xl text-center font-medium">
                                    {{ form.errors.resume }}
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="!form.resume || form.processing"
                                        class="btn-primary min-w-[180px]"
                                    >
                                        <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        <span v-if="form.processing">AI Analyzing…</span>
                                        <span v-else>Score My Resume</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Recent Scans Sidebar -->
                <div class="lg:col-span-1">
                    <div class="card overflow-hidden h-full flex flex-col">
                        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-neutral-900 dark:text-white">Recent Scans</h3>
                                <Link v-if="resumes.length" :href="route('history')" class="text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                    View All
                                </Link>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-4">
                            <div v-if="!resumes.length" class="text-center py-12 px-4">
                                <div class="w-14 h-14 bg-neutral-100 dark:bg-neutral-800 rounded-xl mx-auto flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">No scans yet</p>
                                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Upload your first resume to start</p>
                            </div>

                            <ul v-else class="space-y-3">
                                <li
                                    v-for="resume in resumes.slice(0, 5)"
                                    :key="resume.id"
                                    class="group rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-primary-300 dark:hover:border-primary-700 bg-neutral-50/30 dark:bg-neutral-800/30 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all duration-200 overflow-hidden"
                                >
                                    <Link
                                        :href="route('resumes.report', resume.id)"
                                        class="flex items-center gap-3 p-3.5"
                                    >
                                        <div
                                            class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 border"
                                            :class="[
                                                resume.score >= 80 ? 'bg-success-100 border-success-200 text-success-600 dark:bg-success-900/30 dark:border-success-800 dark:text-success-400' :
                                                resume.score >= 60 ? 'bg-warning-100 border-warning-200 text-warning-600 dark:bg-warning-900/30 dark:border-warning-800 dark:text-warning-400' :
                                                'bg-danger-100 border-danger-200 text-danger-600 dark:bg-danger-900/30 dark:border-danger-800 dark:text-danger-400'
                                            ]"
                                        >
                                            {{ resume.score }}
                                        </div>
                                        <div class="grow min-w-0">
                                            <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ resume.title }}</p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium mt-0.5">{{ new Date(resume.created_at).toLocaleDateString() }}</p>
                                        </div>
                                        <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </Link>
                                    <div class="px-3.5 pb-3.5 flex gap-2">
                                        <Link
                                            :href="route('resumes.report', resume.id)"
                                            class="flex-1 text-center py-2 text-xs font-semibold uppercase tracking-wider bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-lg transition-colors border border-neutral-200 dark:border-neutral-700"
                                        >
                                            View Report
                                        </Link>
                                        <Link
                                            :href="route('resumes.target', resume.id)"
                                            class="flex-1 text-center py-2 text-xs font-semibold uppercase tracking-wider bg-primary-100 dark:bg-primary-900/30 hover:bg-primary-200 dark:hover:bg-primary-900/50 text-primary-600 dark:text-primary-400 rounded-lg transition-colors border border-primary-200 dark:border-primary-800"
                                        >
                                            Match JD
                                        </Link>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
