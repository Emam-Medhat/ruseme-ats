<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAnalysisLoader } from '@/composables/useAnalysisLoader';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({ resume: null });
const loader = useAnalysisLoader();

const dragging   = ref(false);
const inputRef   = ref(null);
const fileError  = ref('');

// ── File validation ──────────────────────────────────────────────────────────
const ALLOWED_TYPES = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
const MAX_SIZE_MB = 5;

const validateAndSet = (file) => {
    fileError.value = '';
    if (!file) return;
    const typeOk = ALLOWED_TYPES.includes(file.type) || /\.(pdf|docx)$/i.test(file.name);
    if (!typeOk) { fileError.value = 'Only PDF or DOCX files are accepted.'; return; }
    if (file.size > MAX_SIZE_MB * 1024 * 1024) { fileError.value = `File must be under ${MAX_SIZE_MB} MB.`; return; }
    form.resume = file;
};

const onDrop = (e) => { dragging.value = false; validateAndSet(e.dataTransfer.files?.[0]); };
const onPick = (e) => validateAndSet(e.target.files?.[0]);
const removeFile = () => { form.resume = null; fileError.value = ''; };

const fileSizeMB = computed(() =>
    form.resume ? (form.resume.size / (1024 * 1024)).toFixed(2) : '0'
);
const fileExt = computed(() =>
    form.resume?.name?.split('.').pop()?.toUpperCase() ?? ''
);

// ── Submit ───────────────────────────────────────────────────────────────────
const submit = () => {
    if (!form.resume || form.processing) return;
    loader.start();
    form.post(route('resumes.upload'), {
        forceFormData: true,
        onSuccess: () => loader.finish(),
        onError:   () => loader.stop(),
        onFinish:  () => loader.stop(),
    });
};

// ── Feature cards ────────────────────────────────────────────────────────────
const features = [
    { icon: '⚡', title: 'Instant ATS Score',   desc: 'Get your ATS compatibility score in under 60 seconds.' },
    { icon: '🎯', title: 'Line-by-Line Fixes',  desc: 'Exact bullet improvements with side-by-side rewrites.' },
    { icon: '✦',  title: 'AI Magic Rewrite',    desc: '3 executive-level alternatives for every weak bullet.' },
    { icon: '📊', title: '6 Score Dimensions',  desc: 'Impact, Brevity, Style, ATS, Recruiter & Keywords.' },
];
</script>

<template>
    <Head title="Upload Resume — CV Genius AI" />

    <AppLayout>
        <div class="max-w-5xl mx-auto">
            <!-- Hero Section -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full text-sm font-medium mb-4">
                    <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span>
                    AI-Powered · Instant Results · Free to Start
                </div>
                <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">
                    Upload Your Resume
                    <span class="block text-primary-600 dark:text-primary-400">Get Your ATS Score in 60s</span>
                </h1>
                <p class="text-lg text-neutral-500 dark:text-neutral-400 max-w-2xl mx-auto">
                    Our AI scans every line of your CV, flags weak bullets, and gives you executive-level rewrites — just like a $500/hr career coach.
                </p>
            </div>

            <!-- Upload Card -->
            <div class="card overflow-hidden mb-8">
                <div class="px-8 py-6 bg-gradient-to-r from-neutral-50 to-neutral-100 dark:from-neutral-900/50 dark:to-neutral-800/50 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg shadow-primary-500/30">
                            📄
                        </div>
                        <div class="flex-1">
                            <h2 class="font-semibold text-neutral-900 dark:text-white">Upload Your Resume</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">PDF or DOCX · Max 5 MB · Scanned instantly by AI</p>
                        </div>
                        <div class="flex items-center gap-2 bg-success-100 dark:bg-success-900/30 border border-success-200 dark:border-success-800 px-3 py-1.5 rounded-lg">
                            <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span>
                            <span class="text-xs font-semibold uppercase tracking-wider text-success-700 dark:text-success-400">AI Online</span>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Drop Zone -->
                    <div
                        v-if="!form.resume"
                        class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer group"
                        :class="dragging
                            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-[1.01]'
                            : 'border-neutral-300 dark:border-neutral-700 bg-gradient-to-br from-neutral-50/50 to-neutral-100/10 dark:from-neutral-900/10 dark:to-neutral-800/10 hover:border-primary-500 hover:bg-neutral-50/30 dark:hover:bg-neutral-800/20'"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="onDrop"
                        @click="inputRef?.click()"
                    >
                        <input
                            ref="inputRef"
                            type="file"
                            accept=".pdf,.docx"
                            class="hidden"
                            @change="onPick"
                        />

                        <div class="py-14 px-6 flex flex-col items-center text-center">
                            <div class="relative mb-5">
                                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:scale-105 transition-transform duration-200">
                                    <svg class="w-8 h-8 text-white" :class="dragging ? 'animate-bounce' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.338-2.32 5.75 5.75 0 011.605 11.084" />
                                    </svg>
                                </div>
                                <div v-if="dragging" class="absolute inset-0 rounded-xl border-2 border-primary-500/40 animate-ping pointer-events-none" />
                            </div>

                            <p class="text-lg font-semibold text-neutral-900 dark:text-white mb-1">
                                {{ dragging ? 'Drop it here!' : 'Drag & drop your resume' }}
                            </p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-5">
                                or click to browse from your computer
                            </p>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg text-sm font-semibold text-primary-600 dark:text-primary-400 shadow-sm">
                                    📄 PDF
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg text-sm font-semibold text-secondary-600 dark:text-secondary-400 shadow-sm">
                                    📝 DOCX
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg text-sm font-semibold text-neutral-500 dark:text-neutral-500 shadow-sm">
                                    📦 Max 5 MB
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- File Selected State -->
                    <div
                        v-else
                        class="rounded-xl border border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-900/20 p-6 flex items-center gap-4"
                    >
                        <div class="w-16 h-16 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow flex items-center justify-center shrink-0">
                            <span class="text-lg font-bold" :class="fileExt === 'PDF' ? 'text-danger-500' : 'text-primary-500'">
                                {{ fileExt }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ form.resume.name }}</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 font-medium">{{ fileSizeMB }} MB · Ready for AI analysis</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse" />
                                <span class="text-xs font-semibold uppercase tracking-wider text-success-600 dark:text-success-400">File validated ✓</span>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="removeFile"
                            class="shrink-0 w-10 h-10 rounded-full bg-white dark:bg-neutral-900 border border-danger-200 dark:border-danger-900/40 text-danger-500 hover:text-danger-600 hover:border-danger-300 dark:hover:border-danger-800 flex items-center justify-center transition-all shadow-sm"
                            title="Remove file"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Error Messages -->
                    <p v-if="fileError" class="flex items-center gap-2 text-sm text-danger-600 dark:text-danger-400 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 px-4 py-3 rounded-lg font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        {{ fileError }}
                    </p>
                    <p v-if="form.errors.resume" class="flex items-center gap-2 text-sm text-danger-600 dark:text-danger-400 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 px-4 py-3 rounded-lg font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        {{ form.errors.resume }}
                    </p>
                    <p v-if="form.errors.credits" class="flex items-center gap-2 text-sm text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 px-4 py-3 rounded-lg font-medium">
                        🪙 {{ form.errors.credits }}
                    </p>

                    <!-- Submit CTA -->
                    <button
                        type="button"
                        :disabled="!form.resume || form.processing"
                        @click="submit"
                        class="w-full py-4 rounded-xl text-sm font-semibold uppercase tracking-wider text-white transition-all duration-200 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="form.resume && !form.processing
                            ? 'bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-500 hover:to-secondary-500 shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 hover:-translate-y-0.5 active:translate-y-0'
                            : 'bg-neutral-200 dark:bg-neutral-800 text-neutral-400 dark:text-neutral-600 border border-neutral-300 dark:border-neutral-700'"
                    >
                        <span v-if="form.resume && !form.processing" class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-750 pointer-events-none" />

                        <span class="relative flex items-center justify-center gap-3">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else-if="form.resume" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                            <svg v-else class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3" />
                            </svg>

                            <span>
                                {{ form.processing
                                    ? 'Running AI Analysis…'
                                    : form.resume
                                        ? '✦ Analyze My Resume Now'
                                        : 'Upload a file to continue'
                                }}
                            </span>
                        </span>
                    </button>

                    <!-- Privacy Note -->
                    <p class="text-sm text-center text-neutral-500 dark:text-neutral-400 font-medium">
                        🔒 Your resume is processed securely and never shared. Analysis completes in under 60 seconds.
                    </p>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div
                    v-for="feat in features"
                    :key="feat.title"
                    class="card p-6 hover:shadow-card-hover transition-all duration-200 hover:-translate-y-0.5"
                >
                    <div class="text-2xl mb-3">{{ feat.icon }}</div>
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-2">{{ feat.title }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ feat.desc }}</p>
                </div>
            </div>

            <!-- Social Proof -->
            <div class="card p-6">
                <div class="flex flex-wrap items-center justify-center gap-8">
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">98%</span>
                        <span class="text-xs text-neutral-500 uppercase tracking-wider font-semibold">Accuracy Rate</span>
                    </div>
                    <div class="w-px h-10 bg-neutral-200 dark:bg-neutral-800" />
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">&lt; 60s</span>
                        <span class="text-xs text-neutral-500 uppercase tracking-wider font-semibold">Analysis Time</span>
                    </div>
                    <div class="w-px h-10 bg-neutral-200 dark:bg-neutral-800" />
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">10K+</span>
                        <span class="text-xs text-neutral-500 uppercase tracking-wider font-semibold">Resumes Scanned</span>
                    </div>
                    <div class="w-px h-10 bg-neutral-200 dark:bg-neutral-800" />
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">Free</span>
                        <span class="text-xs text-neutral-500 uppercase tracking-wider font-semibold">To Get Started</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
